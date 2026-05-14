<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private CreateTeam $createTeam)
    {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $expected = session('captcha_answer');
        if (! $expected || strtolower(trim($input['captcha'] ?? '')) !== $expected) {
            throw ValidationException::withMessages([
                'captcha' => ['The verification code is incorrect.'],
            ]);
        }

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'captcha'  => ['required', 'string'],
        ])->validate();

        session()->forget('captcha_answer');

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);

            return $user;
        });
    }
}
