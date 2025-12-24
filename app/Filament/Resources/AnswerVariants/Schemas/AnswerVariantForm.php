<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AnswerVariantForm
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

                Textarea::make('accepted_text')
                    ->label('Accepted Text')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Alternative correct answer text that will be accepted for this question'),
                TextInput::make('id')->required()->disabled(),

            ]);
    }
}
