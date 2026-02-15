<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Schemas;

use App\Filament\Resources\AnswerVariants\AnswerVariantResource;
use App\Filament\Resources\MultipleChoiceOptions\MultipleChoiceOptionResource;
use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use App\Filament\Resources\SessionRounds\SessionRoundResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class PlayerAnswerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('round.id')
                ->label('Round')
                ->url(static fn($record) => (
                    $record && $record->round_id
                        ? SessionRoundResource::getUrl('edit', [
                            'record' => $record->round_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('round_id')
                ->label('Round')
                ->relationship('round', 'id')
                ->required()
                ->searchable()
                ->preload(),

            TextEntry::make('participant.guest_name')
                ->label('Participant')
                ->url(static fn($record) => (
                    $record && $record->participant_id
                        ? SessionParticipantResource::getUrl('edit', [
                            'record' => $record->participant_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('participant_id')
                ->label('Participant')
                ->relationship('participant', 'id')
                ->required()
                ->searchable()
                ->preload(),

            Textarea::make('submitted_text')
                ->label('Submitted Text')
                ->rows(3)
                ->columnSpanFull(),

            TextEntry::make('selectedOption.option_text')
                ->label('Selected Option')
                ->url(static fn($record) => (
                    $record && $record->selected_option_id
                        ? MultipleChoiceOptionResource::getUrl('edit', [
                            'record' => $record->selected_option_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('selected_option_id')
                ->label('Selected Option')
                ->relationship('selectedOption', 'option_text')
                ->searchable()
                ->preload(),

            TextEntry::make('matchedVariant.accepted_text')
                ->label('Matched Variant')
                ->url(static fn($record) => (
                    $record && $record->matched_variant_id
                        ? AnswerVariantResource::getUrl('edit', [
                            'record' => $record->matched_variant_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('matched_variant_id')
                ->label('Matched Variant')
                ->relationship('matchedVariant', 'accepted_text')
                ->searchable()
                ->preload(),

            Toggle::make('is_correct')->label('Is Correct')->default(false),

            TextInput::make('response_time_ms')
                ->label('Response Time (ms)')
                ->numeric()
                ->minValue(0)
                ->suffix('ms'),

            TextInput::make('points_awarded')
                ->label('Points Awarded')
                ->numeric()
                ->default(0),

            Toggle::make('host_override')
                ->label('Host Override')
                ->helperText(
                    'Whether this answer was manually corrected by the host',
                ),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}
