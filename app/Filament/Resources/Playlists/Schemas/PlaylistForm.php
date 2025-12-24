<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\Schemas;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlaylistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Owner')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Checkbox::make('is_public')
                    ->label('Public')
                    ->default(false),

                TextInput::make('play_count')
                    ->label('Play Count')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
