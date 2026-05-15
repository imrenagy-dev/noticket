<?php

namespace App\Support;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /** @return array<int, ValidationRule|array<mixed>|string> */
    public static function password(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /** @return array<int, ValidationRule|array<mixed>|string> */
    public static function currentPassword(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
