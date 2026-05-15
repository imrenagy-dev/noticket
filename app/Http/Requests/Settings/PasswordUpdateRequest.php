<?php

namespace App\Http\Requests\Settings;

use App\Support\PasswordRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => PasswordRules::currentPassword(),
            'password'         => PasswordRules::password(),
        ];
    }
}
