<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'question_type' => QuestionType::Artist,
            'correct_answer' => fake()->words(2, true),
            'base_points' => 1000,
            'difficulty_level' => fake()->numberBetween(1, 5),
        ];
    }
}
