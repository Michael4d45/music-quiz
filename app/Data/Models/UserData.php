<?php

declare(strict_types=1);

namespace App\Data\Models;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $is_admin,
        public string $email,
    ) {}
}
