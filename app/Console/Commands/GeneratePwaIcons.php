<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'banhawy:generate-icons
        {--out=public/icons : Output directory for PNG icons}';

    protected $description = 'Render branded PWA PNG icons (192, 512, maskable 512, apple-touch 180) using GD.';

    public function handle(): int
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->error('GD extension is not loaded.');
            return self::FAILURE;
        }

        $out = rtrim($this->option('out'), '/');
        if (! is_dir($out)) {
            mkdir($out, 0775, true);
        }

        $this->renderIcon($out . '/icon-192.png',          192, false);
        $this->renderIcon($out . '/icon-512.png',          512, false);
        $this->renderIcon($out . '/icon-maskable-512.png', 512, true);   // safe area padding
        $this->renderIcon($out . '/apple-touch-icon.png',  180, false);
        $this->renderIcon($out . '/favicon.png',           64,  false);

        // Also overwrite the legacy root-level favicon that's wired into <head>
        $publicRoot = dirname($out);                  // assumes $out lives under public/
        $rootFav = $publicRoot . '/favicon.png';
        $this->renderIcon($rootFav, 64, false);

        // Touch a manifest cache-buster file so we can pin a query string to the manifest URLs
        $version = (int) (microtime(true));
        @file_put_contents($publicRoot . '/icons/.version', $version);

        $this->info('PWA icons regenerated ✓ (' . $out . ' + ' . $rootFav . ')');
        $this->info('Asset version: ' . $version);
        return self::SUCCESS;
    }

    private function renderIcon(string $path, int $size, bool $maskable): void
    {
        // Maskable icons need a "safe zone" — keep content inside the central 80%
        $contentInset = $maskable ? (int) ($size * 0.10) : 0;
        $contentSize  = $size - 2 * $contentInset;

        // Render at 4× then downscale for crisp anti-aliased edges
        $scale = 4;
        $W = $size * $scale;
        $H = $size * $scale;
        $inset = $contentInset * $scale;
        $c = $W - 2 * $inset;   // content box dimensions in scaled coords

        $img = imagecreatetruecolor($W, $H);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Maskable icons must fully fill — use a teal background instead of transparent.
        // Non-maskable icons keep a transparent bg outside the rounded square.
        if ($maskable) {
            $bg = imagecolorallocate($img, 13, 148, 136); // solid teal so corners look fine on any platform
        } else {
            $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
        }
        imagefill($img, 0, 0, $bg);

        // ── Rounded square (content area) ────────────────────────────
        $radius = (int) ($c * 0.24);
        $tealA  = imagecolorallocate($img, 13, 148, 136);  // top  #0D9488
        $tealB  = imagecolorallocate($img, 14, 124, 114);  // bottom #0E7C72 (gradient end)
        $this->filledRoundedRectGradient($img, $inset, $inset, $inset + $c, $inset + $c, $radius, $tealA, $tealB);

        // Subtle top-half highlight overlay
        $this->topHighlight($img, $inset, $inset, $c, $radius);

        // ── Pin silhouette (semi-transparent white) ─────────────────
        $pinW  = (int) ($c * 0.50);
        $pinH  = (int) ($c * 0.50);
        $pinX  = $inset + (int) (($c - $pinW) / 2);
        $pinY  = $inset + (int) ($c * 0.18);
        $whiteSoft = imagecolorallocatealpha($img, 255, 255, 255, 100); // ~22% opacity
        $this->drawPin($img, $pinX, $pinY, $pinW, $pinH, $whiteSoft);

        // ── White pin core circle ───────────────────────────────────
        $coreR  = (int) ($c * 0.105);
        $coreCx = $inset + (int) ($c / 2);
        $coreCy = $inset + (int) ($c * 0.36);
        $white  = imagecolorallocate($img, 255, 255, 255);
        imagefilledellipse($img, $coreCx, $coreCy, $coreR * 2, $coreR * 2, $white);

        // ── Stylised "ب" arc at the bottom ──────────────────────────
        // A flat U-shape with a dot underneath = abstracted Arabic ب
        $arcY = $inset + (int) ($c * 0.62);
        $arcWidth  = (int) ($c * 0.42);
        $arcHeight = (int) ($c * 0.14);
        $arcX = $inset + (int) (($c - $arcWidth) / 2);
        $thick = (int) ($c * 0.055);
        $this->drawArabicBaaShape($img, $arcX, $arcY, $arcWidth, $arcHeight, $thick, $white);

        // Downscale to final size with smooth interpolation
        $final = imagecreatetruecolor($size, $size);
        imagealphablending($final, false);
        imagesavealpha($final, true);
        $transparent = imagecolorallocatealpha($final, 0, 0, 0, 127);
        imagefill($final, 0, 0, $transparent);
        imagealphablending($final, true);

        imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $W, $H);

        imagepng($final, $path, 9);
        imagedestroy($img);
        imagedestroy($final);
    }

    /** Solid rounded rectangle filled with a vertical gradient. */
    private function filledRoundedRectGradient($img, int $x1, int $y1, int $x2, int $y2, int $r, int $topColor, int $bottomColor): void
    {
        $w = $x2 - $x1; $h = $y2 - $y1;
        // Solid base first to get clean rounded corners (alpha-aware)
        $this->filledRoundedRect($img, $x1, $y1, $x2, $y2, $r, $topColor);

        // Overlay a gradient by painting many thin horizontal lines clipped to the rounded shape
        for ($i = 0; $i < $h; $i++) {
            $t  = $i / max(1, $h - 1);
            $rr = (int) ((1 - $t) * (($topColor >> 16) & 0xFF) + $t * (($bottomColor >> 16) & 0xFF));
            $gg = (int) ((1 - $t) * (($topColor >> 8)  & 0xFF) + $t * (($bottomColor >> 8)  & 0xFF));
            $bb = (int) ((1 - $t) * ($topColor & 0xFF)         + $t * ($bottomColor & 0xFF));
            $col = imagecolorallocate($img, $rr, $gg, $bb);

            // Determine row x-extent considering rounded corners
            [$lx, $rx] = $this->rowExtents($y1, $y2, $x1, $x2, $r, $y1 + $i);
            if ($lx === null) continue;
            imageline($img, $lx, $y1 + $i, $rx, $y1 + $i, $col);
        }
    }

    /** Returns [left, right] x coordinates of the rounded rect for a given y; null if outside. */
    private function rowExtents(int $y1, int $y2, int $x1, int $x2, int $r, int $y): array
    {
        $w = $x2 - $x1; $h = $y2 - $y1;
        $cy = $y - $y1;
        if ($cy < 0 || $cy > $h) return [null, null];

        if ($cy < $r) {
            $dy = $r - $cy;
            $dx = (int) round(sqrt(max(0, $r * $r - $dy * $dy)));
            return [$x1 + ($r - $dx), $x2 - ($r - $dx)];
        }
        if ($cy > $h - $r) {
            $dy = $cy - ($h - $r);
            $dx = (int) round(sqrt(max(0, $r * $r - $dy * $dy)));
            return [$x1 + ($r - $dx), $x2 - ($r - $dx)];
        }
        return [$x1, $x2];
    }

    private function filledRoundedRect($img, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($img, $x1 + $r, $y1,      $x2 - $r, $y2,      $color);
        imagefilledrectangle($img, $x1,      $y1 + $r, $x2,      $y2 - $r, $color);
        imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }

    /** Light gradient overlay on the top half — gives a subtle "glassy" feel. */
    private function topHighlight($img, int $x, int $y, int $size, int $r): void
    {
        $half = (int) ($size / 2);
        for ($i = 0; $i < $half; $i++) {
            $t = 1 - ($i / $half);
            $alpha = (int) (127 - 36 * $t); // 91..127  (very subtle)
            $col = imagecolorallocatealpha($img, 255, 255, 255, $alpha);
            [$lx, $rx] = $this->rowExtents($y, $y + $size, $x, $x + $size, $r, $y + $i);
            if ($lx === null) continue;
            imageline($img, $lx, $y + $i, $rx, $y + $i, $col);
        }
    }

    /** Draws a teardrop/pin path filled with $color. */
    private function drawPin($img, int $x, int $y, int $w, int $h, int $color): void
    {
        // Build polygon points approximating a map pin shape
        $cx = $x + $w / 2;
        $topCy = $y + $w / 2;        // circle center for top dome
        $bottomY = $y + $h;
        $radius = $w / 2;

        $points = [];
        $segments = 24;
        // Top dome (left → top → right)
        for ($i = $segments; $i >= 0; $i--) {
            $theta = M_PI + ($i / $segments) * M_PI;  // π → 2π
            $points[] = (int) ($cx + cos($theta) * $radius);
            $points[] = (int) ($topCy + sin($theta) * $radius);
        }
        // Right edge sweeping to bottom point
        $points[] = (int) $cx;
        $points[] = (int) $bottomY;

        imagesetthickness($img, 1);
        imagefilledpolygon($img, $points, $color);
    }

    /** Abstract "ب": a flat U-curve plus a small dot beneath. */
    private function drawArabicBaaShape($img, int $x, int $y, int $w, int $h, int $thick, int $color): void
    {
        imagesetthickness($img, max(2, $thick));
        // Bottom horizontal stroke
        imageline($img, $x, $y + $h, $x + $w, $y + $h, $color);
        // Two upturned ends
        imageline($img, $x,        $y + $h,        $x,        $y + $h - (int) ($h * 0.55), $color);
        imageline($img, $x + $w,   $y + $h,        $x + $w,   $y + $h - (int) ($h * 0.55), $color);
        // Dot (Arabic ب has a single dot UNDER the letter)
        $dotR = max(2, (int) ($thick * 0.9));
        imagefilledellipse($img, $x + (int) ($w * 0.50), $y + $h + (int) ($h * 0.65), $dotR * 2, $dotR * 2, $color);
        imagesetthickness($img, 1);
    }
}
