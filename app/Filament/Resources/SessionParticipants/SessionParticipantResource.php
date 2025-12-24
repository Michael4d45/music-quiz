<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants;

use App\Filament\Resources\SessionParticipants\Pages\CreateSessionParticipant;
use App\Filament\Resources\SessionParticipants\Pages\EditSessionParticipant;
use App\Filament\Resources\SessionParticipants\Pages\ListSessionParticipants;
use App\Filament\Resources\SessionParticipants\Schemas\SessionParticipantForm;
use App\Filament\Resources\SessionParticipants\Tables\SessionParticipantsTable;
use App\Models\SessionParticipant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SessionParticipantResource extends Resource
{
    protected static string|null $model = SessionParticipant::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Session Management';

    public static function form(Schema $schema): Schema
    {
        return SessionParticipantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionParticipantsTable::configure($table);
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
            'index' => ListSessionParticipants::route('/'),
            'create' => CreateSessionParticipant::route('/create'),
            'edit' => EditSessionParticipant::route('/{record}/edit'),
        ];
    }
}
