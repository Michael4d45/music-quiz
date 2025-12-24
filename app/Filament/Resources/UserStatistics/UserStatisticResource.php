<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics;

use App\Filament\Resources\UserStatistics\Pages\CreateUserStatistic;
use App\Filament\Resources\UserStatistics\Pages\EditUserStatistic;
use App\Filament\Resources\UserStatistics\Pages\ListUserStatistics;
use App\Filament\Resources\UserStatistics\Schemas\UserStatisticForm;
use App\Filament\Resources\UserStatistics\Tables\UserStatisticsTable;
use App\Models\UserStatistic;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserStatisticResource extends Resource
{
    protected static string|null $model = UserStatistic::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    public static function form(Schema $schema): Schema
    {
        return UserStatisticForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserStatisticsTable::configure($table);
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
            'index' => ListUserStatistics::route('/'),
            'create' => CreateUserStatistic::route('/create'),
            'edit' => EditUserStatistic::route('/{record}/edit'),
        ];
    }
}
