<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubCategories;

use App\Filament\Resources\SubCategories\Pages\CreateSubCategory;
use App\Filament\Resources\SubCategories\Pages\EditSubCategory;
use App\Filament\Resources\SubCategories\Pages\ListSubCategories;
use App\Filament\Resources\SubCategories\RelationManagers\MusicTracksRelationManager;
use App\Filament\Resources\SubCategories\Schemas\SubCategoryForm;
use App\Filament\Resources\SubCategories\Tables\SubCategoriesTable;
use App\Models\SubCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubCategoryResource extends Resource
{
    protected static string|null $model = SubCategory::class;

    protected static \BackedEnum|string|null $navigationIcon =
        Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Music Content & Organization';

    public static function form(Schema $schema): Schema
    {
        return SubCategoryForm::configure($schema);
    }

    //
    public static function getPages(): array
    {
        return [
            'index' => ListSubCategories::route('/'),
            'create' => CreateSubCategory::route('/create'),
            'edit' => EditSubCategory::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MusicTracksRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return SubCategoriesTable::configure($table);
    }
}
