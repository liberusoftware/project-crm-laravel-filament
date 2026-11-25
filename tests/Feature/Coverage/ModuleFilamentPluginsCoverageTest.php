<?php

declare(strict_types=1);

use Filament\Contracts\Plugin;
use Filament\Panel;

it('instantiates, registers, and boots every Filament plugin across all modules', function () {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('modules'), FilesystemIterator::SKIP_DOTS)
    );

    $pluginClasses = [];

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (! str_contains($path, '/src/')) {
            continue;
        }

        $contents = (string) file_get_contents($path);
        if (! str_contains($contents, 'implements Plugin') && ! str_contains($contents, 'implements \\Filament\\Contracts\\Plugin')) {
            continue;
        }
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)) {
            continue;
        }
        if (! preg_match_all('/^\s*(?:final\s+|readonly\s+)*class\s+(\w+)[^{]*implements[^{]*\bPlugin\b/m', $contents, $clsMatches)) {
            continue;
        }

        foreach ($clsMatches[1] as $shortName) {
            $class = trim($nsMatch[1]).'\\'.$shortName;
            if (! class_exists($class)) {
                require_once $path;
            }
            if (class_exists($class)) {
                $pluginClasses[$class] = true;
            }
        }
    }

    expect($pluginClasses)->not->toBeEmpty();

    $tested = 0;
    foreach (array_keys($pluginClasses) as $class) {
        $ref = new ReflectionClass($class);
        if ($ref->isAbstract() || ! $ref->implementsInterface(Plugin::class)) {
            continue;
        }

        /** @var Plugin $plugin */
        $plugin = method_exists($class, 'make') ? $class::make() : $this->app->make($class);
        $id = $plugin->getId();
        expect($id)->toBeString()->not->toBeEmpty();

        $panel = Panel::make()->id('test-plugin-'.$tested);
        $plugin->register($panel);
        $plugin->boot($panel);
        $tested++;
    }

    expect($tested)->toBeGreaterThan(80);
});
