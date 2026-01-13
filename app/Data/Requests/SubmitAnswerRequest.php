<?php

declare(strict_types=1);

namespace App\Data\Requests;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SubmitAnswerRequest extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(255)]
        public string $answer,

        #[Nullable]
        #[StringType]
        public null|string $selected_option_id = null,
    ) {}
}
