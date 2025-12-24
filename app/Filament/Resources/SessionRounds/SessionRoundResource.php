<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds;

use App\Filament\Resources\SessionRounds\Pages\CreateSessionRound;
use App\Filament\Resources\SessionRounds\Pages\EditSessionRound;
use App\Filament\Resources\SessionRounds\Pages\ListSessionRounds;
use App\Filament\Resources\SessionRounds\Schemas\SessionRoundForm;
use App\Filament\Resources\SessionRounds\Tables\SessionRoundsTable;
use App\Models\SessionRound;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SessionRoundResource extends Resource
{
    protected static string|null $model = SessionRound::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Session Management';

    public static function form(Schema $schema): Schema
    {
        return SessionRoundForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionRoundsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSessionRounds::route('/'),
            'create' => CreateSessionRound::route('/create'),
            'edit' => EditSessionRound::route('/{record}/edit'),
        ];
    }
}
