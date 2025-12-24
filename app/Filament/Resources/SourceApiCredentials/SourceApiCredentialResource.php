<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials;

use App\Filament\Resources\SourceApiCredentials\Pages\CreateSourceApiCredential;
use App\Filament\Resources\SourceApiCredentials\Pages\EditSourceApiCredential;
use App\Filament\Resources\SourceApiCredentials\Pages\ListSourceApiCredentials;
use App\Filament\Resources\SourceApiCredentials\Schemas\SourceApiCredentialForm;
use App\Filament\Resources\SourceApiCredentials\Tables\SourceApiCredentialsTable;
use App\Models\SourceApiCredential;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SourceApiCredentialResource extends Resource
{
    protected static string|null $model = SourceApiCredential::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Library';

    public static function form(Schema $schema): Schema
    {
        return SourceApiCredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SourceApiCredentialsTable::configure($table);
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
            'index' => ListSourceApiCredentials::route('/'),
            'create' => CreateSourceApiCredential::route('/create'),
            'edit' => EditSourceApiCredential::route('/{record}/edit'),
        ];
    }
}
