<?php

namespace App\Support;

interface CaptchaImageGeneratorInterface
{
    public function generate(string $text): string;
}
