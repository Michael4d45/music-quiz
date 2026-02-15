<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Schemas;

use App\Filament\Resources\Playlists\PlaylistResource;
use App\Filament\Resources\QuizQuestions\QuizQuestionResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PlaylistItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('playlist.name')
                ->label('Playlist')
                ->url(static fn($record) => (
                    $record && $record->playlist_id
                        ? PlaylistResource::getUrl('edit', [
                            'record' => $record->playlist_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('playlist_id')
                ->label('Playlist')
                ->relationship('playlist', 'name')
                ->required()
                ->searchable()
                ->preload(),

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

            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(0)
                ->helperText(
                    'Order in which this question appears in the playlist',
                ),

            DateTimePicker::make('added_at')
                ->label('Added At')
                ->required()
                ->default(now())
                ->helperText('When this question was added to the playlist'),

            TextInput::make('id')->copyable()->disabled(),
        ]);
    }
}
