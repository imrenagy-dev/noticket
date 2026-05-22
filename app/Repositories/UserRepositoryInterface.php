<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function findNameById(int $id): ?string;
}
