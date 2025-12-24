<?php

declare(strict_types=1);

namespace App\Filament\Resources\ScoringRules;

use App\Filament\Resources\ScoringRules\Pages\CreateScoringRule;
use App\Filament\Resources\ScoringRules\Pages\EditScoringRule;
use App\Filament\Resources\ScoringRules\Pages\ListScoringRules;
use App\Filament\Resources\ScoringRules\Schemas\ScoringRuleForm;
use App\Filament\Resources\ScoringRules\Tables\ScoringRulesTable;
use App\Models\ScoringRule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScoringRuleResource extends Resource
{
    protected static string|null $model = ScoringRule::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Management';

    public static function form(Schema $schema): Schema
    {
        return ScoringRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScoringRulesTable::configure($table);
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
            'index' => ListScoringRules::route('/'),
            'create' => CreateScoringRule::route('/create'),
            'edit' => EditScoringRule::route('/{record}/edit'),
        ];
    }
}
