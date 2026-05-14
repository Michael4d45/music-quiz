<?php

declare(strict_types=1);

namespace Tests\Feature\QuizQuestions;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyQuizQuestionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_quiz_question_fields(): void
    {
        $user = User::factory()->create();
        $question = QuizQuestion::factory()->for($user)->create([
            'question_type' => QuestionType::Artist,
            'correct_answer' => 'Old',
            'prompt_text' => 'Prompt',
            'base_points' => 1000,
            'difficulty_level' => 2,
            'visibility' => Visibility::Private,
        ]);

        $this
            ->actingAs($user, 'web')
            ->patchJson("/api/my/quiz-questions/{$question->id}", [
                'correct_answer' => 'New',
                'prompt_text' => 'Updated prompt',
                'base_points' => 500,
                'difficulty_level' => 5,
                'visibility' => Visibility::Public->value,
                'media_start_seconds' => 10,
                'media_end_seconds' => 60,
            ])
            ->assertOk()
            ->assertJsonPath('correct_answer', 'New')
            ->assertJsonPath('prompt_text', 'Updated prompt')
            ->assertJsonPath('base_points', 500);

        $this->assertDatabaseHas('quiz_questions', [
            'id' => $question->id,
            'correct_answer' => 'New',
            'media_start_seconds' => 10,
        ]);
    }
}
