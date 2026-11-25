<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Foundation\ModuleManager\ModuleRegistry;
use RuntimeException;

final readonly class ModulePlugins
{
    public function __construct(private ModuleRegistry $modules) {}

    /** @return list<Plugin> */
    public function forPanel(string $panel): array
    {
        $plugins = [];
        $resolved = $this->modules->resolve((array) config('modules.enabled', []), (array) config('modules.disabled', []));

        foreach ($resolved as $module) {
            foreach ($module->filamentPlugins($panel) as $pluginClass) {
                $plugin = method_exists($pluginClass, 'make') ? $pluginClass::make() : app($pluginClass);
                if (! $plugin instanceof Plugin) {
                    throw new RuntimeException("Module [{$module->name()}] returned an invalid Filament plugin.");
                }
                if (isset($plugins[$plugin->getId()])) {
                    throw new RuntimeException("Duplicate Filament plugin id [{$plugin->getId()}].");
                }
                $plugins[$plugin->getId()] = $plugin;
            }
        }

        return array_values($plugins);
    }

    public function discoverForPanel(Panel $panel, string $scope): void
    {
        foreach (glob(base_path('modules/*/composer.json')) ?: [] as $composerFile) {
            $dir = dirname($composerFile);
            $decoded = json_decode((string) file_get_contents($composerFile), true);
            $psr4 = is_array($decoded) ? ($decoded['autoload']['psr-4'] ?? []) : [];
            $baseNs = null;

            foreach ($psr4 as $namespace => $path) {
                if (rtrim((string) $path, '/') === 'src') {
                    $baseNs = rtrim((string) $namespace, '\\');
                    break;
                }
            }

            if ($baseNs === null) {
                continue;
            }

            $resourceDirs = [
                "{$dir}/src/{$scope}/Resources" => "{$baseNs}\\{$scope}\\Resources",
                "{$dir}/src/Filament/{$scope}/Resources" => "{$baseNs}\\Filament\\{$scope}\\Resources",
            ];

            if ($scope === 'Admin') {
                $resourceDirs["{$dir}/src/Filament/Resources"] = "{$baseNs}\\Filament\\Resources";
            }

            foreach ($resourceDirs as $path => $namespace) {
                if (is_dir($path)) {
                    $panel->discoverResources(in: $path, for: $namespace);
                }
            }

            $pageDirs = [
                "{$dir}/src/{$scope}/Pages" => "{$baseNs}\\{$scope}\\Pages",
                "{$dir}/src/Filament/{$scope}/Pages" => "{$baseNs}\\Filament\\{$scope}\\Pages",
            ];

            foreach ($pageDirs as $path => $namespace) {
                if (is_dir($path)) {
                    $panel->discoverPages(in: $path, for: $namespace);
                }
            }
        }
    }
}
