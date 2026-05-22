<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findNameById(int $id): ?string
    {
        return User::find($id)?->name;
    }
}
