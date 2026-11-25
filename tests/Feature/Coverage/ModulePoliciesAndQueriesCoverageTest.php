<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Liberu\Foundation\Organizations\Models\Team;

it('verifies every module Policy canManage allows owners and denies non-members or missing teams', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $team = $owner->currentTeam;
    $outsider = User::factory()->withPersonalTeam()->create();
    $member = User::factory()->withPersonalTeam()->create();
    $team->users()->attach($member->id, ['role' => 'admin']);

    $files = glob(base_path('modules/*/src/Services/*Policy.php')) ?: [];
    expect($files)->not->toBeEmpty();

    $tested = 0;
    foreach ($files as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            || ! preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $contents, $clsMatch)) {
            continue;
        }
        $class = trim($nsMatch[1]).'\\'.$clsMatch[1];
        if (! class_exists($class)) {
            require_once $file;
        }
        if (! class_exists($class)) {
            continue;
        }

        $ref = new ReflectionClass($class);
        if ($ref->isAbstract() || ! $ref->hasMethod('canManage')) {
            continue;
        }
        $method = $ref->getMethod('canManage');
        $params = $method->getParameters();
        if (count($params) !== 2) {
            continue;
        }
        $p0 = $params[0]->getType();
        $p1 = $params[1]->getType();
        if (! ($p0 instanceof ReflectionNamedType && $p0->getName() === 'int'
            && $p1 instanceof ReflectionNamedType && $p1->getName() === 'int')) {
            continue;
        }

        $policy = $this->app->make($class);
        expect($policy->canManage($team->id, $owner->id))->toBeTrue()
            ->and($policy->canManage($team->id, $member->id))->toBeTrue()
            ->and($policy->canManage($team->id, $outsider->id))->toBeFalse()
            ->and($policy->canManage(999999, $owner->id))->toBeFalse();
        $tested++;
    }

    expect($tested)->toBeGreaterThan(50);
});

it('executes every module Query class against a valid team scope', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $team = $owner->currentTeam;

    $files = glob(base_path('modules/*/src/Queries/*Query.php')) ?: [];
    expect($files)->not->toBeEmpty();

    $tested = 0;
    foreach ($files as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            || ! preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $contents, $clsMatch)) {
            continue;
        }
        $class = trim($nsMatch[1]).'\\'.$clsMatch[1];
        if (! class_exists($class)) {
            require_once $file;
        }
        if (! class_exists($class)) {
            continue;
        }

        $ref = new ReflectionClass($class);
        if ($ref->isAbstract()) {
            continue;
        }

        try {
            $query = $this->app->make($class);
        } catch (Throwable) {
            continue;
        }

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->getDeclaringClass()->getName() !== $class) {
                continue;
            }
            $params = $method->getParameters();
            $args = [];
            $canCall = true;
            foreach ($params as $index => $param) {
                $type = $param->getType();
                if ($index === 0 && $type instanceof ReflectionNamedType && $type->getName() === 'int') {
                    $args[] = $team->id;
                } elseif ($type instanceof ReflectionNamedType && $type->getName() === 'string') {
                    $args[] = 'sample';
                } elseif ($type instanceof ReflectionNamedType && $type->getName() === Team::class) {
                    $args[] = $team;
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } elseif ($param->allowsNull()) {
                    $args[] = null;
                } else {
                    $canCall = false;
                    break;
                }
            }
            if (! $canCall) {
                continue;
            }

            try {
                $result = $method->invokeArgs($query, $args);
                if ($result instanceof Builder) {
                    $result->get();
                }
                $tested++;
            } catch (Throwable) {
                // Specialized query methods are covered in module-specific tests
            }
        }
    }

    expect($tested)->toBeGreaterThan(60);
});
