<?php

namespace App\Contracts;

interface TeamSlugGeneratorContract
{
    public function generate(string $name, ?int $excludeId = null): string;
}
