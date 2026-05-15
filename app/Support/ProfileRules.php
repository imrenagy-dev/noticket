<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class ProfileRules
{
    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    public static function profile(?int $userId = null): array
    {
        return [
            'name'  => static::name(),
            'email' => static::email($userId),
        ];
    }

    /** @return array<int, ValidationRule|array<mixed>|string> */
    public static function name(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /** @return array<int, ValidationRule|array<mixed>|string> */
    public static function email(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
