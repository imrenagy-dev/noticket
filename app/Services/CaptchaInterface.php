<?php

namespace App\Services;

interface CaptchaInterface
{
    public function generate(int $length = 6): string;

    public function verify(string $input): bool;

    public function consume(): void;
}
