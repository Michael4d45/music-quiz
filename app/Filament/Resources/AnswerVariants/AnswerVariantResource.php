<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants;

use App\Filament\Resources\AnswerVariants\Pages\CreateAnswerVariant;
use App\Filament\Resources\AnswerVariants\Pages\EditAnswerVariant;
use App\Filament\Resources\AnswerVariants\Pages\ListAnswerVariants;
use App\Filament\Resources\AnswerVariants\Schemas\AnswerVariantForm;
use App\Filament\Resources\AnswerVariants\Tables\AnswerVariantsTable;
use App\Models\AnswerVariant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnswerVariantResource extends Resource
{
    protected static string|null $model = AnswerVariant::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Management';

    public static function form(Schema $schema): Schema
    {
        return AnswerVariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnswerVariantsTable::configure($table);
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
            'index' => ListAnswerVariants::route('/'),
            'create' => CreateAnswerVariant::route('/create'),
            'edit' => EditAnswerVariant::route('/{record}/edit'),
        ];
    }
}
