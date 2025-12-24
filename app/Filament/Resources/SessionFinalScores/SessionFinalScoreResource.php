<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores;

use App\Filament\Resources\SessionFinalScores\Pages\CreateSessionFinalScore;
use App\Filament\Resources\SessionFinalScores\Pages\EditSessionFinalScore;
use App\Filament\Resources\SessionFinalScores\Pages\ListSessionFinalScores;
use App\Filament\Resources\SessionFinalScores\Schemas\SessionFinalScoreForm;
use App\Filament\Resources\SessionFinalScores\Tables\SessionFinalScoresTable;
use App\Models\SessionFinalScore;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SessionFinalScoreResource extends Resource
{
    protected static string|null $model = SessionFinalScore::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|\UnitEnum|null $navigationGroup = 'Session Management';

    public static function form(Schema $schema): Schema
    {
        return SessionFinalScoreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionFinalScoresTable::configure($table);
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
            'index' => ListSessionFinalScores::route('/'),
            'create' => CreateSessionFinalScore::route('/create'),
            'edit' => EditSessionFinalScore::route('/{record}/edit'),
        ];
    }
}
