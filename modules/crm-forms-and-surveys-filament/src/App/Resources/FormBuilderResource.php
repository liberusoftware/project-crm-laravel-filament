<?php

declare(strict_types=1);

namespace Liberu\CRM\FormsAndSurveysFilament\App\Resources;

use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages\CreateFormBuilder;
use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages\EditFormBuilder;
use Liberu\CRM\FormsAndSurveysFilament\App\Resources\FormBuilderResource\Pages\ListFormBuilders;
use Liberu\CRM\Core\Filament\Concerns\EnforcesResourcePermissions;
use Liberu\CRM\FormsAndSurveys\Models\FormBuilder;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormBuilderResource extends Resource
{
    use EnforcesResourcePermissions;

    protected static ?string $model = FormBuilder::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 6;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(65535),
                Repeater::make('fields')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'number' => 'Number',
                                'email' => 'Email',
                                'select' => 'Select',
                                'checkbox' => 'Checkbox',
                            ])
                            ->required(),
                        TextInput::make('label')
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        Toggle::make('required'),
                    ])
                    ->columns(2)
                    ->required(),
            ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('description'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListFormBuilders::route('/'),
            'create' => CreateFormBuilder::route('/create'),
            'edit' => EditFormBuilder::route('/{record}/edit'),
        ];
    }
}
