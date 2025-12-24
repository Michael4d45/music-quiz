<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems;

use App\Filament\Resources\PlaylistItems\Pages\CreatePlaylistItem;
use App\Filament\Resources\PlaylistItems\Pages\EditPlaylistItem;
use App\Filament\Resources\PlaylistItems\Pages\ListPlaylistItems;
use App\Filament\Resources\PlaylistItems\Schemas\PlaylistItemForm;
use App\Filament\Resources\PlaylistItems\Tables\PlaylistItemsTable;
use App\Models\PlaylistItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlaylistItemResource extends Resource
{
    protected static string|null $model = PlaylistItem::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return PlaylistItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlaylistItemsTable::configure($table);
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
            'index' => ListPlaylistItems::route('/'),
            'create' => CreatePlaylistItem::route('/create'),
            'edit' => EditPlaylistItem::route('/{record}/edit'),
        ];
    }
}
