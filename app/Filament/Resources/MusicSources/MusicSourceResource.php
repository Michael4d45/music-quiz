<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources;

use App\Filament\Resources\MusicSources\Pages\CreateMusicSource;
use App\Filament\Resources\MusicSources\Pages\EditMusicSource;
use App\Filament\Resources\MusicSources\Pages\ListMusicSources;
use App\Filament\Resources\MusicSources\Schemas\MusicSourceForm;
use App\Filament\Resources\MusicSources\Tables\MusicSourcesTable;
use App\Models\MusicSource;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MusicSourceResource extends Resource
{
    protected static string|null $model = MusicSource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return MusicSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MusicSourcesTable::configure($table);
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
            'index' => ListMusicSources::route('/'),
            'create' => CreateMusicSource::route('/create'),
            'edit' => EditMusicSource::route('/{record}/edit'),
        ];
    }
}
