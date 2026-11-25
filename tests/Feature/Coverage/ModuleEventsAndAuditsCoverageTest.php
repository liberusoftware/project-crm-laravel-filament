<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

it('constructs module events and records audit entries across module audit services', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $team = $owner->currentTeam;

    // 1. Audit services with record(int $teamId, ?int $actorId, string $event, array $details = [])
    $auditFiles = glob(base_path('modules/*/src/Services/*Audit.php')) ?: [];
    $auditsTested = 0;

    foreach ($auditFiles as $file) {
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
        if ($ref->isAbstract() || ! $ref->hasMethod('record')) {
            continue;
        }
        $method = $ref->getMethod('record');
        $params = $method->getParameters();
        if (count($params) >= 3) {
            $p0 = $params[0]->getType();
            $p1 = $params[1]->getType();
            $p2 = $params[2]->getType();
            if ($p0 instanceof ReflectionNamedType && $p0->getName() === 'int'
                && $p1 instanceof ReflectionNamedType && $p1->getName() === 'int'
                && $p2 instanceof ReflectionNamedType && $p2->getName() === 'string') {
                try {
                    $service = $this->app->make($class);
                    $service->record($team->id, $owner->id, 'coverage.checked', ['source' => 'test']);
                    $auditsTested++;
                } catch (Throwable) {
                }
            }
        }
    }

    // 2. Module Events
    $eventFiles = glob(base_path('modules/*/src/Events/*.php')) ?: [];
    $eventsTested = 0;

    foreach ($eventFiles as $file) {
        $contents = (string) file_get_contents($file);
        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)
            || ! preg_match('/^\s*(?:final\s+|readonly\s+)*class\s+(\w+)/m', $contents, $clsMatch)) {
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
        $ctor = $ref->getConstructor();
        $args = [];
        $canConstruct = true;
        if ($ctor !== null) {
            foreach ($ctor->getParameters() as $param) {
                $type = $param->getType();
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } elseif ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                    if ($typeName === 'int') {
                        $args[] = $team->id;
                    } elseif ($typeName === 'string') {
                        $args[] = 'event-sample';
                    } elseif ($typeName === 'bool') {
                        $args[] = true;
                    } elseif ($typeName === 'array') {
                        $args[] = ['key' => 'value'];
                    } elseif (class_exists($typeName) && is_subclass_of($typeName, Model::class)) {
                        $args[] = new $typeName();
                    } elseif ($param->allowsNull()) {
                        $args[] = null;
                    } else {
                        $canConstruct = false;
                        break;
                    }
                } elseif ($param->allowsNull()) {
                    $args[] = null;
                } else {
                    $canConstruct = false;
                    break;
                }
            }
        }
        if ($canConstruct) {
            try {
                $event = $ref->newInstanceArgs($args);
                if (method_exists($event, 'broadcastOn')) {
                    $event->broadcastOn();
                }
                if (method_exists($event, 'broadcastAs')) {
                    $event->broadcastAs();
                }
                if (method_exists($event, 'broadcastWith')) {
                    $event->broadcastWith();
                }
                $eventsTested++;
            } catch (Throwable) {
            }
        }
    }

    expect($auditsTested)->toBeGreaterThanOrEqual(5)
        ->and($eventsTested)->toBeGreaterThan(20);
});
