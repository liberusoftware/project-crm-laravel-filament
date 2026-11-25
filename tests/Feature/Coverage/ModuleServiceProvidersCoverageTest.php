<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;

it('registers and boots every module service provider cleanly', function () {
    $providers = [];

    foreach (glob(base_path('modules/*/src/**/*ServiceProvider.php')) ?: [] as $file) {
        $providers[] = $file;
    }
    foreach (glob(base_path('modules/*/src/*ServiceProvider.php')) ?: [] as $file) {
        $providers[] = $file;
    }

    $providers = array_values(array_unique($providers));
    expect($providers)->not->toBeEmpty();

    $booted = 0;

    foreach ($providers as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)) {
            continue;
        }
        if (! preg_match_all('/^\s*(?:final\s+|readonly\s+)*class\s+(\w+)\s+extends\s+[^\n{]*ServiceProvider/m', $contents, $clsMatches)) {
            continue;
        }

        foreach ($clsMatches[1] as $shortName) {
            $class = trim($nsMatch[1]).'\\'.$shortName;
            if (! class_exists($class)) {
                require_once $file;
            }
            if (! class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);
            if ($ref->isAbstract() || ! $ref->isSubclassOf(ServiceProvider::class)) {
                continue;
            }

            $provider = new $class($this->app);
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
            if (method_exists($provider, 'boot')) {
                $this->app->call([$provider, 'boot']);
            }
            $booted++;
        }
    }

    expect($booted)->toBeGreaterThan(400);
});
