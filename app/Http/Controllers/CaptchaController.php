<?php

namespace App\Http\Controllers;

use App\Services\CaptchaServiceInterface;
use App\Support\CaptchaImageGeneratorInterface;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public function __construct(
        private CaptchaServiceInterface       $captcha,
        private CaptchaImageGeneratorInterface $imageGenerator,
    ) {}

    public function image(): Response
    {
        $png = $this->imageGenerator->generate($this->captcha->generate());

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }
}
