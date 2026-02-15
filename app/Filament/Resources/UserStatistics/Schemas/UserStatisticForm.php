<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class UserStatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')
                ->label('User')
                ->url(static fn($record) => (
                    $record && $record->user_id
                        ? UserResource::getUrl('edit', [
                            'record' => $record->user_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('user_id')
                ->label('User')
                ->relationship('user', 'name')
                ->required()
                ->disabled()
                ->preload(),

            TextInput::make('total_games_played')
                ->label('Total Games Played')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(0),

            TextInput::make('total_wins')
                ->label('Total Wins')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(0),

            TextInput::make('total_points')
                ->label('Total Points')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(0),

            TextInput::make('average_score')
                ->label('Average Score')
                ->numeric()
                ->step(0.01)
                ->minValue(0),

            TextInput::make('best_streak')
                ->label('Best Streak')
                ->numeric()
                ->required()
                ->minValue(0)
                ->default(0),

            TextEntry::make('favoriteCategory.name')
                ->label('Favorite Category')
                ->url(static fn($record) => (
                    $record && $record->favorite_category_id
                        ? CategoryResource::getUrl('edit', [
                            'record' => $record->favorite_category_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

            Select::make('favorite_category_id')
                ->label('Favorite Category')
                ->relationship('favoriteCategory', 'name')
                ->searchable()
                ->preload(),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}
