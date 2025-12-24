<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks;

use App\Filament\Resources\TrackSourceLinks\Pages\CreateTrackSourceLink;
use App\Filament\Resources\TrackSourceLinks\Pages\EditTrackSourceLink;
use App\Filament\Resources\TrackSourceLinks\Pages\ListTrackSourceLinks;
use App\Filament\Resources\TrackSourceLinks\Schemas\TrackSourceLinkForm;
use App\Filament\Resources\TrackSourceLinks\Tables\TrackSourceLinksTable;
use App\Models\TrackSourceLink;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrackSourceLinkResource extends Resource
{
    protected static string|null $model = TrackSourceLink::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return TrackSourceLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrackSourceLinksTable::configure($table);
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
            'index' => ListTrackSourceLinks::route('/'),
            'create' => CreateTrackSourceLink::route('/create'),
            'edit' => EditTrackSourceLink::route('/{record}/edit'),
        ];
    }
}
