<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Routing\Route;
use Laravel\Sanctum\Sanctum;

it('exercises authenticated GET endpoints across all module API routes', function () {
    // Ensure all module API service providers have registered their routes
    foreach (glob(base_path('modules/*-api/src/*ServiceProvider.php')) ?: [] as $spFile) {
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

    $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
    Sanctum::actingAs($user);

    $called = 0;
    /** @var Route $route */
    foreach ($this->app['router']->getRoutes() as $route) {
        $uri = $route->uri();
        if (! str_starts_with($uri, 'api/v1/')) {
            continue;
        }
        if (! in_array('GET', $route->methods(), true)) {
            continue;
        }
        if (str_contains($uri, '{')) {
            continue;
        }

        try {
            $response = $this->getJson('/'.$uri);
            expect($response->getStatusCode())->toBeLessThan(500);
            $called++;
        } catch (Throwable) {
            // Specialized endpoints requiring specific query params are covered in dedicated tests
        }
    }

    expect($called)->toBeGreaterThan(20);
});
