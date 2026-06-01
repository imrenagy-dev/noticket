<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function findNameById(int $id): ?string
    {
        return $this->model->find($id)?->name;
    }
}
