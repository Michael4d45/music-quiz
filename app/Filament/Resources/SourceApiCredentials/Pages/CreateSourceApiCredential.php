<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials\Pages;

use App\Filament\Resources\SourceApiCredentials\SourceApiCredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSourceApiCredential extends CreateRecord
{
    protected static string $resource = SourceApiCredentialResource::class;
}
