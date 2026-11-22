<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources;

use Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages\CreateActivation;
use Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages\EditActivation;
use Liberu\CRM\Core\Filament\Resources\ActivationResource\Pages\ListActivations;
use Liberu\CRM\Core\Filament\Concerns\EnforcesResourcePermissions;
use Liberu\CRM\Core\Models\Activation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivationResource extends Resource
{
    use EnforcesResourcePermissions;

    protected static ?string $model = Activation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Team';

    protected static ?int $navigationSort = 6;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->preload(),
                TextInput::make('token'),
                TextInput::make('ip_address'),
            ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('token')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListActivations::route('/'),
            'create' => CreateActivation::route('/create'),
            'edit' => EditActivation::route('/{record}/edit'),
        ];
    }
}
