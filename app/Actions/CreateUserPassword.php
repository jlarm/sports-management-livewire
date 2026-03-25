<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class CreateUserPassword
{
    /**
     * @param  array<string, string>  $credentials
     */
    public function handle(array $credentials, string $password): string
    {
        return Password::reset($credentials, function ($user) use ($password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }
}
