<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents;

use App\Filament\Resources\SessionEvents\Pages\CreateSessionEvent;
use App\Filament\Resources\SessionEvents\Pages\EditSessionEvent;
use App\Filament\Resources\SessionEvents\Pages\ListSessionEvents;
use App\Filament\Resources\SessionEvents\Schemas\SessionEventForm;
use App\Filament\Resources\SessionEvents\Tables\SessionEventsTable;
use App\Models\SessionEvent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SessionEventResource extends Resource
{
    protected static string|null $model = SessionEvent::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Session Management';

    public static function form(Schema $schema): Schema
    {
        return SessionEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionEventsTable::configure($table);
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
            'index' => ListSessionEvents::route('/'),
            'create' => CreateSessionEvent::route('/create'),
            'edit' => EditSessionEvent::route('/{record}/edit'),
        ];
    }
}
