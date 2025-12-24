<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions;

use App\Filament\Resources\MultipleChoiceOptions\Pages\CreateMultipleChoiceOption;
use App\Filament\Resources\MultipleChoiceOptions\Pages\EditMultipleChoiceOption;
use App\Filament\Resources\MultipleChoiceOptions\Pages\ListMultipleChoiceOptions;
use App\Filament\Resources\MultipleChoiceOptions\Schemas\MultipleChoiceOptionForm;
use App\Filament\Resources\MultipleChoiceOptions\Tables\MultipleChoiceOptionsTable;
use App\Models\MultipleChoiceOption;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MultipleChoiceOptionResource extends Resource
{
    protected static string|null $model = MultipleChoiceOption::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Management';

    public static function form(Schema $schema): Schema
    {
        return MultipleChoiceOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MultipleChoiceOptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMultipleChoiceOptions::route('/'),
            'create' => CreateMultipleChoiceOption::route('/create'),
            'edit' => EditMultipleChoiceOption::route('/{record}/edit'),
        ];
    }
}
