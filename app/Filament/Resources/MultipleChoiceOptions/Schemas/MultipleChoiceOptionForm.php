<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MultipleChoiceOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('question_id')
                    ->label('Question')
                    ->relationship('question', 'question_text')
                    ->required()
                    ->searchable()
                    ->preload(),

                Textarea::make('option_text')
                    ->label('Option Text')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_correct')
                    ->label('Is Correct Answer')
                    ->helperText('Check if this option is the correct answer for the question'),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Order in which this option appears (lower numbers appear first)'),
                TextInput::make('id')->required()->disabled(),

            ]);
    }
}
