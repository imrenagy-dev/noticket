<?php

namespace App\Support;

interface TeamSlugGeneratorInterface
{
    public function generate(string $name, ?int $excludeId = null): string;
}
