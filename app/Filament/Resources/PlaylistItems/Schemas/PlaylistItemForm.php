<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlaylistItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('playlist_id')
                    ->label('Playlist')
                    ->relationship('playlist', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'question_text')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Order in which this question appears in the playlist'),

                DateTimePicker::make('added_at')
                    ->label('Added At')
                    ->required()
                    ->default(now())
                    ->helperText('When this question was added to the playlist'),
                TextInput::make('id')->required()->disabled(),

            ]);
    }
}
