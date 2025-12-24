<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameSessions;

use App\Filament\Resources\GameSessions\Pages\CreateGameSession;
use App\Filament\Resources\GameSessions\Pages\EditGameSession;
use App\Filament\Resources\GameSessions\Pages\ListGameSessions;
use App\Filament\Resources\GameSessions\RelationManagers\ParticipantsRelationManager;
use App\Filament\Resources\GameSessions\RelationManagers\RoundsRelationManager;
use App\Filament\Resources\GameSessions\Schemas\GameSessionForm;
use App\Filament\Resources\GameSessions\Tables\GameSessionsTable;
use App\Models\GameSession;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GameSessionResource extends Resource
{
    protected static string|null $model = GameSession::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Game Management';

    public static function form(Schema $schema): Schema
    {
        return GameSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GameSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ParticipantsRelationManager::class,
            RoundsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameSessions::route('/'),
            'create' => CreateGameSession::route('/create'),
            'edit' => EditGameSession::route('/{record}/edit'),
        ];
    }
}
