<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions;

use App\Filament\Resources\QuizQuestions\Pages\CreateQuizQuestion;
use App\Filament\Resources\QuizQuestions\Pages\EditQuizQuestion;
use App\Filament\Resources\QuizQuestions\Pages\ListQuizQuestions;
use App\Filament\Resources\QuizQuestions\Schemas\QuizQuestionForm;
use App\Filament\Resources\QuizQuestions\Tables\QuizQuestionsTable;
use App\Models\QuizQuestion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuizQuestionResource extends Resource
{
    protected static string|null $model = QuizQuestion::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Management';

    public static function form(Schema $schema): Schema
    {
        return QuizQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizQuestionsTable::configure($table);
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
            'index' => ListQuizQuestions::route('/'),
            'create' => CreateQuizQuestion::route('/create'),
            'edit' => EditQuizQuestion::route('/{record}/edit'),
        ];
    }
}
