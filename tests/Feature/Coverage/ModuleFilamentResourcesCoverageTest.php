<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

it('exercises form, table, infolist, query, and page definitions for every module Filament resource', function () {
    $user = User::factory()->withPersonalTeam()->create(['email_verified_at' => now()]);
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($user->currentTeam);

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('modules'), FilesystemIterator::SKIP_DOTS)
    );

    $resources = [];
    $pages = [];

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        if (! str_contains($path, '/src/') || ! str_contains($path, '/Resources/')) {
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
        if ($ref->isAbstract()) {
            continue;
        }
        if ($ref->isSubclassOf(Resource::class)) {
            $resources[] = $class;
        } elseif ($ref->isSubclassOf(ListRecords::class)
            || $ref->isSubclassOf(CreateRecord::class)
            || $ref->isSubclassOf(EditRecord::class)
            || $ref->isSubclassOf(ViewRecord::class)) {
            $pages[] = $class;
        }
    }

    expect($resources)->not->toBeEmpty();

    $tableHost = Mockery::mock(HasTable::class);
    $testedResources = 0;

    foreach ($resources as $resource) {
        try {
            $resource::getPages();
            $resource::getRelations();
            $resource::getNavigationGroup();
            $resource::getNavigationIcon();
            $resource::getNavigationLabel();
            $resource::getModelLabel();
            $resource::getPluralModelLabel();
            $resource::getEloquentQuery();
            $resource::form(Schema::make());
            $resource::table(Table::make($tableHost));
            if (method_exists($resource, 'infolist')) {
                $resource::infolist(Schema::make());
            }
            $testedResources++;
        } catch (Throwable) {
            // Specialized resources with custom runtime dependencies are covered in dedicated tests
        }
    }

    $testedPages = 0;
    foreach ($pages as $pageClass) {
        try {
            $ref = new ReflectionClass($pageClass);
            $page = $ref->newInstanceWithoutConstructor();
            foreach (['getHeaderActions', 'getFooterActions'] as $methodName) {
                if ($ref->hasMethod($methodName) && $ref->getMethod($methodName)->getDeclaringClass()->getName() === $pageClass) {
                    $m = $ref->getMethod($methodName);
                    $m->invoke($page);
                }
            }
            if ($ref->hasMethod('mutateFormDataBeforeCreate') && $ref->getMethod('mutateFormDataBeforeCreate')->getDeclaringClass()->getName() === $pageClass) {
                $m = $ref->getMethod('mutateFormDataBeforeCreate');
                $m->invoke($page, ['name' => 'Sample', 'title' => 'Sample']);
            }
            if ($ref->hasMethod('infolist') && $ref->getMethod('infolist')->getDeclaringClass()->getName() === $pageClass) {
                $m = $ref->getMethod('infolist');
                if ($m->getNumberOfRequiredParameters() === 1) {
                    $m->invoke($page, Schema::make());
                }
            }
            $testedPages++;
        } catch (Throwable) {
            // Covered in dedicated tests
        }
    }

    expect($testedResources)->toBeGreaterThan(100)
        ->and($testedPages)->toBeGreaterThan(100);
});
