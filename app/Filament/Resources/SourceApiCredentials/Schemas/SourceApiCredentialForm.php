<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials\Schemas;

use App\Enums\CredentialType;
use App\Filament\Resources\MusicSources\MusicSourceResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class SourceApiCredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('source.name')
                ->label('Source')
                ->url(static fn($record) => (
                    $record && $record->source_id
                        ? MusicSourceResource::getUrl('edit', [
                            'record' => $record->source_id,
                        ]) : null
                ))
                ->placeholder('N/A'),

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

            DateTimePicker::make('expires_at')->label('Expires At'),

            Flex::make([
                TextInput::make('id')->copyable()->disabled(),
                DateTimePicker::make('created_at')->disabled(),
                DateTimePicker::make('updated_at')->disabled(),
            ])->columnSpanFull()->hiddenOn('create'),
        ]);
    }
}
