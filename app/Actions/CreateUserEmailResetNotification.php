<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Password;

final class CreateUserEmailResetNotification
{
    /**
     * @param  array<string, string>  $data
     */
    public function handle(array $data): string
    {
        return Password::sendResetLink(['email' => $data['email']]);
    }
}
