<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\Schemas;

use App\Enums\PlaylistStatus;
use App\Enums\QuestionOrder;
use App\Enums\Visibility;
use App\Filament\Resources\ScoringRules\ScoringRuleResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class PlaylistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')
                ->label('Owner')
                ->url(static fn($record) => (
                    $record && $record->user_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->user_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable(),

            TextInput::make('name')->required()->maxLength(255),

            Textarea::make('description')->rows(3)->columnSpanFull(),

            Select::make('status')
                ->options(PlaylistStatus::class)
                ->required()
                ->default(PlaylistStatus::Draft),

            Select::make('visibility')
                ->options(Visibility::class)
                ->required()
                ->default(Visibility::Private),

            KeyValue::make('tags')->label('Tags')->columnSpanFull(),

            TextInput::make('estimated_duration_minutes')
                ->label('Estimated Duration (minutes)')
                ->numeric()
                ->minValue(0),

            TextInput::make('target_audience')->label('Target Audience'),

            Select::make('question_order')
                ->options(QuestionOrder::class)
                ->required()
                ->default(QuestionOrder::Fixed),

            TextInput::make('default_time_limit_seconds')
                ->label('Default Time Limit (seconds)')
                ->numeric()
                ->minValue(0),

            TextEntry::make('scoringRule.name')
                ->label('Scoring Rule')
                ->url(static fn($record) => (
                    $record && $record->scoring_rule_id
                        ? ScoringRuleResource::getUrl('edit', [
                            'record' => $record->scoring_rule_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('scoring_rule_id')
                ->relationship('scoringRule', 'name')
                ->searchable(),

            TextInput::make('play_count')
                ->label('Play Count')
                ->numeric()
                ->default(0)
                ->minValue(0),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}
