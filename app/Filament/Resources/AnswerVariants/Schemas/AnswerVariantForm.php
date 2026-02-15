<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Schemas;

use App\Filament\Resources\QuizQuestions\QuizQuestionResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AnswerVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('question.prompt_text')
                ->label('Question')
                ->url(static fn($record) => (
                    $record && $record->question_id
                        ? QuizQuestionResource::getUrl('edit', [
                            'record' => $record->question_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('question_id')
                ->label('Question')
                ->relationship('question', 'prompt_text')
                ->required()
                ->searchable()
                ->preload(),

            Textarea::make('accepted_text')
                ->label('Accepted Text')
                ->rows(3)
                ->columnSpanFull()
                ->helperText(
                    'Alternative correct answer text that will be accepted for this question',
                ),

            TextInput::make('id')->copyable()->disabled(),
        ]);
    }
}
