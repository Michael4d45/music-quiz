<?php

declare(strict_types=1);

namespace App\Filament\Resources\ScoringRules\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScoringRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('base_points')
                    ->numeric()
                    ->required()
                    ->default(10)
                    ->minValue(1),

                TextInput::make('decay_factor')
                    ->label('Decay Factor')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(1)
                    ->placeholder('0.95'),

                TextInput::make('max_time_ms')
                    ->label('Max Time (ms)')
                    ->numeric()
                    ->minValue(1000)
                    ->placeholder('30000'),

                Checkbox::make('streak_bonus_enabled')
                    ->label('Streak Bonus Enabled')
                    ->default(true),

                TextInput::make('streak_multiplier')
                    ->label('Streak Multiplier')
                    ->numeric()
                    ->step(0.1)
                    ->required()
                    ->default(1.1)
                    ->minValue(1.0),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
