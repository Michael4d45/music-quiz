<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MusicSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('api_base_url')
                    ->label('Base URL')
                    ->url()
                    ->maxLength(500),

                Checkbox::make('is_active')
                    ->label('Active')
                    ->default(true),
                TextInput::make('id')->required()->disabled(),
                TextInput::make('display_name')->required(),
                TextInput::make('icon_url'),
                Toggle::make('requires_authentication')->required(),
                TextInput::make('priority')->numeric()->required(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
