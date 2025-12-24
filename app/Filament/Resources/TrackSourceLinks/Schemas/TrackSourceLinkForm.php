<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrackSourceLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('track_id')
                    ->label('Track')
                    ->relationship('track', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('source_id')
                    ->label('Source')
                    ->relationship('source', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('external_id')
                    ->label('External ID')
                    ->required()
                    ->maxLength(255),

                TextInput::make('preview_url')
                    ->label('Preview URL')
                    ->url()
                    ->maxLength(500),

                TextInput::make('full_url')
                    ->label('Full URL')
                    ->url()
                    ->maxLength(500),

                TextInput::make('embed_url')
                    ->label('Embed URL')
                    ->url()
                    ->maxLength(500),

                TextInput::make('album_art_url')
                    ->label('Album Art URL')
                    ->url()
                    ->maxLength(500),

                Toggle::make('is_verified')
                    ->label('Is Verified')
                    ->default(false),

                Toggle::make('is_available')
                    ->label('Is Available')
                    ->default(true),

                DateTimePicker::make('last_checked_at')
                    ->label('Last Checked At')
                    ->default(now()),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
