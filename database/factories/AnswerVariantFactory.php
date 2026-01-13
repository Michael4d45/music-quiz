<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnswerVariant>
 */
class AnswerVariantFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'question_id' => QuizQuestion::factory(),
            'accepted_text' => fake()->words(2, true),
        ];
    }
}
