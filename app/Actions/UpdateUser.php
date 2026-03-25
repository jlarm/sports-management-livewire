<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class UpdateUser
{
    /**
     * @param  array<string, string>  $data
     */
    public function handle(User $user, array $data): void
    {
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);
    }
}
