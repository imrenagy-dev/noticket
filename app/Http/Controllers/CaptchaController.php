<?php

namespace App\Http\Controllers;

use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public function __construct(private CaptchaService $captcha) {}

    public function image(Request $request): Response
    {
        $text = $this->captcha->generate();

        $bgR = 243; $bgG = 244; $bgB = 252;
        $width  = 210;
        $height = 64;
        $len    = strlen($text);

        $image   = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, $bgR, $bgG, $bgB);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);

        $this->drawCharacters($image, $text, $len, $width, $height, $bgR, $bgG, $bgB);
        $this->drawInterferenceLines($image, $width, $height);

        imagerectangle($image, 0, 0, $width - 1, $height - 1,
            imagecolorallocate($image, 190, 190, 210));

        ob_start();
        imagepng($image);
        $png = ob_get_clean();

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    /** @param \GdImage $image */
    private function drawCharacters($image, string $text, int $len, int $width, int $height, int $bgR, int $bgG, int $bgB): void
    {
        // Font-5 glyph is 9 × 15 px; 28 × 28 canvas gives room to rotate
        $slotW = (int)(($width - 20) / $len);

        for ($i = 0; $i < $len; $i++) {
            $charImg = imagecreatetruecolor(28, 28);
            $charBg  = imagecolorallocate($charImg, $bgR, $bgG, $bgB);
            imagefilledrectangle($charImg, 0, 0, 27, 27, $charBg);

            imagestring($charImg, 5, 9, 6, $text[$i], imagecolorallocate($charImg,
                random_int(15, 55),
                random_int(15, 45),
                random_int(100, 175),
            ));

            $rotated = imagerotate($charImg, random_int(-22, 22), $charBg);
            $rw = imagesx($rotated);
            $rh = imagesy($rotated);

            $cx = 10 + $i * $slotW + (int)($slotW / 2);
            $cy = (int)($height / 2);

            imagecopy($image, $rotated,
                $cx - (int)($rw / 2),
                $cy - (int)($rh / 2),
                0, 0, $rw, $rh);
        }
    }

    /** @param \GdImage $image */
    private function drawInterferenceLines($image, int $width, int $height): void
    {
        for ($i = 0; $i < 3; $i++) {
            $baseY = random_int(10, $height - 10);
            $amp   = random_int(4, 8);
            $freq  = random_int(12, 25) / 10.0;
            $lc    = imagecolorallocate($image,
                random_int(135, 170),
                random_int(135, 170),
                random_int(140, 180),
            );
            $px = 0;
            $py = $baseY;
            for ($x = 3; $x <= $width; $x += 3) {
                $ny = (int)($baseY + $amp * sin($x * M_PI * $freq / $width));
                imageline($image, $px, $py, $x, $ny, $lc);
                $px = $x;
                $py = $ny;
            }
        }
    }
}
