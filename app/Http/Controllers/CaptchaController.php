<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    private const CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function image(Request $request): Response
    {
        $len  = 6;
        $text = '';
        for ($i = 0; $i < $len; $i++) {
            $text .= self::CHARS[random_int(0, strlen(self::CHARS) - 1)];
        }

        $request->session()->put('captcha_answer', strtolower($text));

        // Solid background — must match the rotation fill colour exactly
        $bgR = 243; $bgG = 244; $bgB = 252;

        $width  = 210;
        $height = 64;
        $image  = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, $bgR, $bgG, $bgB);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);

        // --- Rotated characters ---
        // Font-5 glyph is 9 × 15 px; char canvas 28 × 28 gives room to rotate
        $slotW = (int)(($width - 20) / $len);
        for ($i = 0; $i < $len; $i++) {
            $cw = 28; $ch = 28;
            $charImg  = imagecreatetruecolor($cw, $ch);
            $charBgC  = imagecolorallocate($charImg, $bgR, $bgG, $bgB);
            imagefilledrectangle($charImg, 0, 0, $cw - 1, $ch - 1, $charBgC);

            $r = random_int(15, 55);
            $g = random_int(15, 45);
            $b = random_int(100, 175);
            imagestring($charImg, 5, 9, 6, $text[$i],
                imagecolorallocate($charImg, $r, $g, $b));

            $angle   = random_int(-22, 22);
            $rotated = imagerotate($charImg, $angle, $charBgC);

            $rw = imagesx($rotated);
            $rh = imagesy($rotated);

            // Centre of this character's slot
            $cx = 10 + $i * $slotW + (int)($slotW / 2);
            $cy = (int)($height / 2);

            imagecopy($image, $rotated,
                $cx - (int)($rw / 2),
                $cy - (int)($rh / 2),
                0, 0, $rw, $rh);
        }

        // --- Wavy interference lines drawn OVER the characters ---
        // Light enough for humans to see through; breaks edge-detection for bots
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

        // Subtle border
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
}
