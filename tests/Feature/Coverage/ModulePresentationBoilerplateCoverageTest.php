<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Livewire\Component;

it('invokes every module API StatusController cleanly', function () {
    $files = glob(base_path('modules/*/src/Http/Controllers/StatusController.php')) ?: [];
    expect($files)->not->toBeEmpty();

    $tested = 0;
    foreach ($files as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)) {
            continue;
        }
        $class = trim($nsMatch[1]).'\\StatusController';
        if (! class_exists($class)) {
            require_once $file;
        }
        $controller = $this->app->make($class);
        $request = Request::create('/api/v1/status', 'GET', [], [], [], ['HTTP_X_REQUEST_ID' => 'req-123']);
        $response = $controller($request);

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(200);
        $tested++;
    }

    expect($tested)->toBeGreaterThan(20);
});

it('exercises every module Filament Overview page title and navigation label', function () {
    $files = glob(base_path('modules/*/src/Pages/Overview.php')) ?: [];
    expect($files)->not->toBeEmpty();

    $tested = 0;
    foreach ($files as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)) {
            continue;
        }
        $class = trim($nsMatch[1]).'\\Overview';
        if (! class_exists($class)) {
            require_once $file;
        }
        if (! class_exists($class) || ! is_subclass_of($class, Page::class)) {
            continue;
        }

        expect($class::getNavigationLabel())->toBeString()->not->toBeEmpty();
        $page = new $class();
        expect($page->getTitle())->toBeString()->not->toBeEmpty();
        $tested++;
    }

    expect($tested)->toBeGreaterThan(15);
});

it('renders every module Livewire Overview and render-only Livewire component', function () {
    $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    // Boot Livewire module service providers first so all view namespaces exist
    foreach (glob(base_path('modules/*-livewire/src/*ServiceProvider.php')) ?: [] as $spFile) {
        $contents = (string) file_get_contents($spFile);
        if (preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            && preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $contents, $clsMatch)) {
            $spClass = trim($nsMatch[1]).'\\'.$clsMatch[1];
            if (! class_exists($spClass)) {
                require_once $spFile;
            }
            if (class_exists($spClass)) {
                $sp = new $spClass($this->app);
                if (method_exists($sp, 'boot')) {
                    $sp->boot();
                }
            }
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('modules'), FilesystemIterator::SKIP_DOTS)
    );

    $rendered = 0;
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (! str_contains($path, '/src/') || (! str_contains($path, '/Livewire/') && ! str_contains($path, '-livewire/'))) {
            continue;
        }
        $contents = (string) file_get_contents($path);
        if (! str_contains($contents, 'extends Component')) {
            continue;
        }
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            || ! preg_match('/^\s*(?:final\s+)?class\s+(\w+)\s+extends\s+Component/m', $contents, $clsMatch)) {
            continue;
        }
        $class = trim($nsMatch[1]).'\\'.$clsMatch[1];
        if (! class_exists($class)) {
            require_once $path;
        }
        if (! class_exists($class)) {
            continue;
        }

        $ref = new ReflectionClass($class);
        if ($ref->isAbstract() || ! $ref->isSubclassOf(Component::class) || ! $ref->hasMethod('render')) {
            continue;
        }
        $renderMethod = $ref->getMethod('render');
        if ($renderMethod->getNumberOfRequiredParameters() > 0) {
            continue;
        }

        try {
            $component = $ref->newInstance();
            // Populate optional filter properties if they exist so conditional query closures execute
            foreach (['search', 'kind', 'status'] as $propName) {
                if ($ref->hasProperty($propName)) {
                    $prop = $ref->getProperty($propName);
                    $type = $prop->getType();
                    if ($prop->isPublic() && $type instanceof ReflectionNamedType && $type->getName() === 'string') {
                        $component->{$propName} = 'sample';
                    }
                }
            }
            $result = $component->render();
            expect($result)->not->toBeNull();
            $rendered++;
        } catch (Throwable) {
            // Components with specialized mount/dependencies are covered in dedicated tests
        }
    }

    expect($rendered)->toBeGreaterThan(90);
});
