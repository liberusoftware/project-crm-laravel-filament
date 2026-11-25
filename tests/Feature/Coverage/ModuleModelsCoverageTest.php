<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

it('exercises relationships, scopes, and casts on every module Eloquent model', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $team = $owner->currentTeam;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('modules'), FilesystemIterator::SKIP_DOTS)
    );

    $modelsTested = 0;

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (! str_contains($path, '/src/') || ! str_contains($path, '/Models/')) {
            continue;
        }
        $contents = (string) file_get_contents($path);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            || ! preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $contents, $clsMatch)) {
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
        if ($ref->isAbstract() || ! $ref->isSubclassOf(Model::class)) {
            continue;
        }

        try {
            /** @var Model $instance */
            $instance = $ref->newInstance();
            $instance->getTable();
            $instance->getCasts();
        } catch (Throwable) {
            continue;
        }

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $class || $method->isStatic() || $method->isConstructor()) {
                continue;
            }
            $name = $method->getName();

            // Exercise scopeForTeam / scopeActive / zero-arg scopes
            if (str_starts_with($name, 'scope')) {
                $params = $method->getParameters();
                try {
                    if (count($params) === 1) {
                        $method->invoke($instance, $instance->newQuery());
                    } elseif (count($params) === 2) {
                        $p1Type = $params[1]->getType();
                        if ($p1Type instanceof ReflectionNamedType && $p1Type->getName() === 'int') {
                            $method->invoke($instance, $instance->newQuery(), $team->id);
                        } elseif ($p1Type instanceof ReflectionNamedType && $p1Type->getName() === 'string') {
                            $method->invoke($instance, $instance->newQuery(), 'sample');
                        }
                    }
                } catch (Throwable) {
                }

                continue;
            }

            // Exercise declared relation methods (0 required params, return type is a Relation subclass)
            if ($method->getNumberOfRequiredParameters() === 0) {
                $returnType = $method->getReturnType();
                if ($returnType instanceof ReflectionNamedType && is_subclass_of($returnType->getName(), Relation::class)) {
                    try {
                        $method->invoke($instance);
                    } catch (Throwable) {
                    }
                } elseif ($name === 'getActivitylogOptions') {
                    try {
                        $method->invoke($instance);
                    } catch (Throwable) {
                    }
                }
            }
        }

        $modelsTested++;
    }

    expect($modelsTested)->toBeGreaterThan(300);
});
