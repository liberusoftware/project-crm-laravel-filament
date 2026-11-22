<?php

declare(strict_types=1);

namespace Liberu\CRM\Core\Filament\App\Resources;

use Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource\Pages\CreateDashboardWidget;
use Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource\Pages\EditDashboardWidget;
use Liberu\CRM\Core\Filament\App\Resources\DashboardWidgetResource\Pages\ListDashboardWidgets;
use Liberu\CRM\Core\Filament\Concerns\EnforcesResourcePermissions;
use Liberu\Analytics\Core\Models\DashboardWidget;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DashboardWidgetResource extends Resource
{
    use EnforcesResourcePermissions;

    protected static ?string $model = DashboardWidget::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center';

    protected static string|\UnitEnum|null $navigationGroup = 'Account';

    protected static ?int $navigationSort = 4;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('widget_type')
                    ->required(),
                TextInput::make('position')
                    ->integer()
                    ->required(),
                KeyValue::make('settings'),
            ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('widget_type'),
                TextColumn::make('position'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
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
            'index' => ListDashboardWidgets::route('/'),
            'create' => CreateDashboardWidget::route('/create'),
            'edit' => EditDashboardWidget::route('/{record}/edit'),
        ];
    }
}
