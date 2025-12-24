<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('icon_url')
                    ->label('Icon URL')
                    ->url()
                    ->maxLength(500),

                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
