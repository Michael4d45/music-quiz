<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\GameSessionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\MusicTracksRelationManager;
use App\Filament\Resources\Users\RelationManagers\ParticipantsRelationManager;
use App\Filament\Resources\Users\RelationManagers\PlaylistsRelationManager;
use App\Filament\Resources\Users\RelationManagers\QuizQuestionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\StatisticsRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static string|null $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            GameSessionsRelationManager::class,
            ParticipantsRelationManager::class,
            StatisticsRelationManager::class,
            PlaylistsRelationManager::class,
            QuizQuestionsRelationManager::class,
            MusicTracksRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }
}
