<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials\Schemas;

use App\Enums\CredentialType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SourceApiCredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('source_id')
                    ->label('Source')
                    ->relationship('source', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('credential_type')
                    ->label('Credential Type')
                    ->options(CredentialType::class)
                    ->required()
                    ->enum(CredentialType::class),

                TextInput::make('encrypted_value')
                    ->label('Encrypted Value')
                    ->required()
                    ->password()
                    ->helperText('This will be encrypted before storage'),

                DateTimePicker::make('expires_at')
                    ->label('Expires At'),
                TextInput::make('id')->required()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),

            ]);
    }
}
