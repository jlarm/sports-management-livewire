<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final class CreateUser
{
    /**
     * @param  array<string, string>  $data
     */
    public function handle(array $data, string $password): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        event(new Registered($user));

        return $user;
    }
}
