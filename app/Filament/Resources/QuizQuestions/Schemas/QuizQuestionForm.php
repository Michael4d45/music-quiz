<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\Schemas;

use App\Enums\QuestionType;
use App\Models\MusicTrack;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuizQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('track_id')
                    ->label('Music Track')
                    ->options(MusicTrack::pluck('title', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('question_type')
                    ->options(QuestionType::class)
                    ->required()
                    ->default(QuestionType::Title),

                Textarea::make('prompt_text')
                    ->label('Prompt Text')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('correct_answer')
                    ->required()
                    ->maxLength(255),

                TextInput::make('base_points')
                    ->numeric()
                    ->required()
                    ->default(10)
                    ->minValue(1),

                TextInput::make('media_start_seconds')
                    ->label('Media Start (seconds)')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('media_end_seconds')
                    ->label('Media End (seconds)')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('difficulty_level')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(5),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
