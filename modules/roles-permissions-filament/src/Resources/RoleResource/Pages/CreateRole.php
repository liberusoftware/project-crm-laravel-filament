<?php

declare(strict_types=1);

namespace Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource\Pages;

use App\Models\User;
use Liberu\Foundation\RolesPermissions\Services\RoleConfigurationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\RolesPermissionsFilament\Resources\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User, 403);

        try {
            return app(RoleConfigurationService::class)->save($actor, null, $data);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(fn (array $messages, string $field): array => ['data.'.$field => $messages])->all());
        }
    }
}
