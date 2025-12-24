<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Pages;

use App\Filament\Resources\SessionEvents\SessionEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSessionEvent extends CreateRecord
{
    protected static string $resource = SessionEventResource::class;
}
