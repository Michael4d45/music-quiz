<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists\Schemas;

use App\Enums\PlaylistStatus;
use App\Enums\QuestionOrder;
use App\Enums\Visibility;
use App\Models\ScoringRule;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlaylistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Owner')
                ->options(User::pluck('name', 'id'))
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

            KeyValue::make('tags')
                ->label('Tags')
                ->columnSpanFull(),

            TextInput::make('estimated_duration_minutes')
                ->label('Estimated Duration (minutes)')
                ->numeric()
                ->minValue(0),

            TextInput::make('target_audience')
                ->label('Target Audience'),

            Select::make('question_order')
                ->options(QuestionOrder::class)
                ->required()
                ->default(QuestionOrder::Fixed),

            TextInput::make('default_time_limit_seconds')
                ->label('Default Time Limit (seconds)')
                ->numeric()
                ->minValue(0),

            Select::make('scoring_rule_id')
                ->label('Scoring Rule')
                ->options(ScoringRule::pluck('name', 'id'))
                ->searchable(),

            TextInput::make('play_count')
                ->label('Play Count')
                ->numeric()
                ->default(0)
                ->minValue(0),
        ]);
    }
}
