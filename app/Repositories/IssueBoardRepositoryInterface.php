<?php

namespace App\Repositories;

use App\Models\Sprint;

interface IssueBoardRepositoryInterface
{
    public function boardColumns(Sprint $sprint): array;
}
