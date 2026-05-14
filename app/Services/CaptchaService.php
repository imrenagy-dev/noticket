<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;

class CaptchaService
{
    private const SESSION_KEY = 'captcha_answer';
    private const CHARS       = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(private Session $session) {}

    public function generate(int $length = 6): string
    {
        $text = '';
        for ($i = 0; $i < $length; $i++) {
            $text .= self::CHARS[random_int(0, strlen(self::CHARS) - 1)];
        }

        $this->session->put(self::SESSION_KEY, strtolower($text));

        return $text;
    }

    public function verify(string $input): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);

        return $expected !== null && strtolower(trim($input)) === $expected;
    }

    public function consume(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }
}
