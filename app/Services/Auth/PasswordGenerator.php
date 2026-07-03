<?php

namespace App\Services\Auth;

class PasswordGenerator
{
    public function generate(int $length = 6): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $password = '';

        for ($index = 0; $index < $length; $index++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
