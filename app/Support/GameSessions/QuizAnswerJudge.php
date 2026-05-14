<?php

declare(strict_types=1);

namespace App\Support\GameSessions;

use App\Enums\QuestionType;
use App\Models\MultipleChoiceOption;
use App\Models\QuizQuestion;

final class QuizAnswerJudge
{
    /**
     * @return array{is_correct: bool, matched_variant_id: null|string, selected_option_id: null|string}
     */
    public static function judge(
        QuizQuestion $question,
        null|string $submittedText,
        null|string $selectedOptionId,
    ): array {
        if ($question->question_type === QuestionType::MultipleChoice) {
            if ($selectedOptionId === null || $selectedOptionId === '') {
                return [
                    'is_correct' => false,
                    'matched_variant_id' => null,
                    'selected_option_id' => null,
                ];
            }

            /** @var MultipleChoiceOption|null $option */
            $option = $question
                ->multipleChoiceOptions()
                ->whereKey($selectedOptionId)
                ->first();

            if (!$option instanceof MultipleChoiceOption) {
                return [
                    'is_correct' => false,
                    'matched_variant_id' => null,
                    'selected_option_id' => $selectedOptionId,
                ];
            }

            return [
                'is_correct' => $option->is_correct,
                'matched_variant_id' => null,
                'selected_option_id' => $option->id,
            ];
        }

        $normalized = self::normalize($submittedText);
        if ($normalized === '') {
            return [
                'is_correct' => false,
                'matched_variant_id' => null,
                'selected_option_id' => null,
            ];
        }

        if (self::normalize($question->correct_answer) === $normalized) {
            return [
                'is_correct' => true,
                'matched_variant_id' => null,
                'selected_option_id' => null,
            ];
        }

        foreach ($question->answerVariants as $variant) {
            if ($variant->accepted_text === null) {
                continue;
            }

            if (self::normalize($variant->accepted_text) === $normalized) {
                return [
                    'is_correct' => true,
                    'matched_variant_id' => $variant->id,
                    'selected_option_id' => null,
                ];
            }
        }

        return [
            'is_correct' => false,
            'matched_variant_id' => null,
            'selected_option_id' => null,
        ];
    }

    private static function normalize(null|string $value): string
    {
        if ($value === null) {
            return '';
        }

        return mb_strtolower(trim($value));
    }
}
