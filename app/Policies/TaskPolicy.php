<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $task->creator_id === $user->id
            || $task->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true; // ili neka tvoja logika
    }

    public function update(User $user, Task $task): bool
    {
        return $task->creator_id === $user->id
            || $task->users()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->creator_id;
    }
}
