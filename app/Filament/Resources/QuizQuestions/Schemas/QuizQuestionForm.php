<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizQuestions\Schemas;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use App\Filament\Resources\MusicTracks\MusicTrackResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class QuizQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('track.title')
                ->label('Music Track')
                ->url(static fn($record) => (
                    $record && $record->track_id
                        ? MusicTrackResource::getUrl('edit', [
                            'record' => $record->track_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('track_id')
                ->relationship('track', 'title')
                ->label('Music Track')
                ->searchable()
                ->required(),

            TextEntry::make('user.name')
                ->label('User')
                ->url(static fn($record) => (
                    $record && $record->user_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->user_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('user_id')
                ->relationship('user', 'name')
                ->label('User')
                ->searchable(),

            Select::make('question_type')
                ->options(QuestionType::class)
                ->required()
                ->default(QuestionType::Title),

            Textarea::make('prompt_text')
                ->label('Prompt Text')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('rich_prompt_text')
                ->label('Rich Prompt Text')
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('explanation')
                ->label('Explanation')
                ->rows(2)
                ->columnSpanFull(),

            KeyValue::make('hints')->label('Hints')->columnSpanFull(),

            TextInput::make('correct_answer')->required()->maxLength(255),

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

            Select::make('visibility')
                ->options(Visibility::class)
                ->required()
                ->default(Visibility::Private),

            DateTimePicker::make('last_tested_at')->label('Last Tested At'),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}
