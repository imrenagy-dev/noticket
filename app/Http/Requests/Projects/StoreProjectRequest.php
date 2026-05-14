<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function rules(): array
    {
        $team = $this->route('current_team');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'key'         => [
                'required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/',
                Rule::unique('projects', 'key')->where('team_id', $team->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
