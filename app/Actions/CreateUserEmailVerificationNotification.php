<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

final class CreateUserEmailVerificationNotification
{
    public function handle(User $user): void
    {
        $user->notify(new VerifyEmail);
    }
}
