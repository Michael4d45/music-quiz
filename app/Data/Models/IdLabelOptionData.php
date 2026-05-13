<?php

declare(strict_types=1);

namespace App\Data\Models;

use Spatie\LaravelData\Data;

/**
 * Generic id + human label for select lists in the SPA.
 */
class IdLabelOptionData extends Data
{
    public function __construct(
        public string $id,
        public string $label,
    ) {}
}
