<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizModes\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuizModeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Checkbox::make('allows_host_override')
                    ->label('Allows Host Override')
                    ->default(false),

                Checkbox::make('requires_manual_scoring')
                    ->label('Requires Manual Scoring')
                    ->default(false),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
