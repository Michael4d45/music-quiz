<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrackAvailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('track_source_link_id')
                    ->label('Track Source Link')
                    ->relationship('trackSourceLink', 'external_id')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('country_code')
                    ->label('Country Code')
                    ->maxLength(3)
                    ->placeholder('e.g. US, GB, DE'),

                Toggle::make('is_available')
                    ->label('Is Available')
                    ->default(true),

                DateTimePicker::make('last_checked_at')
                    ->label('Last Checked At')
                    ->default(now()),
                TextInput::make('id')->required()->disabled(),

            ]);
    }
}
