<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizModes;

use App\Filament\Resources\QuizModes\Pages\CreateQuizMode;
use App\Filament\Resources\QuizModes\Pages\EditQuizMode;
use App\Filament\Resources\QuizModes\Pages\ListQuizModes;
use App\Filament\Resources\QuizModes\Schemas\QuizModeForm;
use App\Filament\Resources\QuizModes\Tables\QuizModesTable;
use App\Models\QuizMode;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuizModeResource extends Resource
{
    protected static string|null $model = QuizMode::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Management';

    public static function form(Schema $schema): Schema
    {
        return QuizModeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizModesTable::configure($table);
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
            'index' => ListQuizModes::route('/'),
            'create' => CreateQuizMode::route('/create'),
            'edit' => EditQuizMode::route('/{record}/edit'),
        ];
    }
}
