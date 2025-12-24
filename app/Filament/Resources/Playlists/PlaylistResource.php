<?php

declare(strict_types=1);

namespace App\Filament\Resources\Playlists;

use App\Filament\Resources\Playlists\Pages\CreatePlaylist;
use App\Filament\Resources\Playlists\Pages\EditPlaylist;
use App\Filament\Resources\Playlists\Pages\ListPlaylists;
use App\Filament\Resources\Playlists\Schemas\PlaylistForm;
use App\Filament\Resources\Playlists\Tables\PlaylistsTable;
use App\Models\Playlist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static string|null $model = Playlist::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return PlaylistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlaylistsTable::configure($table);
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
            'index' => ListPlaylists::route('/'),
            'create' => CreatePlaylist::route('/create'),
            'edit' => EditPlaylist::route('/{record}/edit'),
        ];
    }
}
