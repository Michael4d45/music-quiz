<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QuizQuestion;
use App\Models\User;

class QuizQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->is_guest;
    }

    public function view(User $user, QuizQuestion $quizQuestion): bool
    {
        return !$user->is_guest
            && $quizQuestion->user_id !== null
            && $quizQuestion->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return !$user->is_guest;
    }

    public function update(User $user, QuizQuestion $quizQuestion): bool
    {
        return !$user->is_guest
            && $quizQuestion->user_id !== null
            && $quizQuestion->user_id === $user->id;
    }

    public function delete(User $user, QuizQuestion $quizQuestion): bool
    {
        return !$user->is_guest
            && $quizQuestion->user_id !== null
            && $quizQuestion->user_id === $user->id;
    }

    public function restore(User $user, QuizQuestion $quizQuestion): bool
    {
        return false;
    }

    public function forceDelete(User $user, QuizQuestion $quizQuestion): bool
    {
        return false;
    }
}
