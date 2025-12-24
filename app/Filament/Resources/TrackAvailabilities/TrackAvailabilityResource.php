<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities;

use App\Filament\Resources\TrackAvailabilities\Pages\CreateTrackAvailability;
use App\Filament\Resources\TrackAvailabilities\Pages\EditTrackAvailability;
use App\Filament\Resources\TrackAvailabilities\Pages\ListTrackAvailabilities;
use App\Filament\Resources\TrackAvailabilities\Schemas\TrackAvailabilityForm;
use App\Filament\Resources\TrackAvailabilities\Tables\TrackAvailabilitiesTable;
use App\Models\TrackAvailability;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrackAvailabilityResource extends Resource
{
    protected static string|null $model = TrackAvailability::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return TrackAvailabilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrackAvailabilitiesTable::configure($table);
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
            'index' => ListTrackAvailabilities::route('/'),
            'create' => CreateTrackAvailability::route('/create'),
            'edit' => EditTrackAvailability::route('/{record}/edit'),
        ];
    }
}
