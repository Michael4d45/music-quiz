<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers;

use App\Filament\Resources\PlayerAnswers\Pages\CreatePlayerAnswer;
use App\Filament\Resources\PlayerAnswers\Pages\EditPlayerAnswer;
use App\Filament\Resources\PlayerAnswers\Pages\ListPlayerAnswers;
use App\Filament\Resources\PlayerAnswers\Schemas\PlayerAnswerForm;
use App\Filament\Resources\PlayerAnswers\Tables\PlayerAnswersTable;
use App\Models\PlayerAnswer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlayerAnswerResource extends Resource
{
    protected static string|null $model = PlayerAnswer::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Session Management';

    public static function form(Schema $schema): Schema
    {
        return PlayerAnswerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayerAnswersTable::configure($table);
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
            'index' => ListPlayerAnswers::route('/'),
            'create' => CreatePlayerAnswer::route('/create'),
            'edit' => EditPlayerAnswer::route('/{record}/edit'),
        ];
    }
}
