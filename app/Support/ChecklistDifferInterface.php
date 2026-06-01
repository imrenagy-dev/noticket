<?php

namespace App\Support;

interface ChecklistDifferInterface
{
    public function entry(?string $oldRaw, mixed $newValue): ?array;
}
