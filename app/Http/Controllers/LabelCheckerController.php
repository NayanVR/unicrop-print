<?php

namespace App\Http\Controllers;

use App\Models\BottleSize;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelCheckerController extends Controller
{
    private const TOLERANCE_MM = 2.0;
    private const DEFAULT_DPI  = 300;

    public function index(): View
    {
        return view('label-checker.index', [
            'bottleSizes' => BottleSize::orderBy('name')->get(),
        ]);
    }

    public function check(Request $request): View
    {
        $request->validate([
            'label_file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        $file = $request->file('label_file');
        $imageInfo = @getimagesize($file->path());

        if (! $imageInfo || $imageInfo[0] === 0) {
            return view('label-checker.index', [
                'bottleSizes' => BottleSize::orderBy('name')->get(),
                'error' => 'Could not read image dimensions. Please upload a valid PNG or JPG.',
            ]);
        }

        $pixelW = $imageInfo[0];
        $pixelH = $imageInfo[1];
        $dpi    = $this->detectDpi($file->path(), $imageInfo['mime'] ?? '');

        $widthMm  = round(($pixelW / $dpi) * 25.4, 1);
        $heightMm = round(($pixelH / $dpi) * 25.4, 1);

        $bottles  = BottleSize::orderBy('name')->get();
        $tol      = self::TOLERANCE_MM;

        $matches = $bottles->filter(function (BottleSize $b) use ($widthMm, $heightMm, $tol) {
            $bw = (float) $b->label_width_mm;
            $bh = (float) $b->label_height_mm;

            // Match in original or rotated orientation
            $portrait  = abs($widthMm - $bw) <= $tol && abs($heightMm - $bh) <= $tol;
            $landscape = abs($widthMm - $bh) <= $tol && abs($heightMm - $bw) <= $tol;

            return $portrait || $landscape;
        });

        return view('label-checker.index', [
            'bottleSizes' => $bottles,
            'result' => [
                'filename' => $file->getClientOriginalName(),
                'pixelW'   => $pixelW,
                'pixelH'   => $pixelH,
                'dpi'      => $dpi,
                'widthMm'  => $widthMm,
                'heightMm' => $heightMm,
                'matches'  => $matches,
            ],
        ]);
    }

    private function detectDpi(string $path, string $mime): float
    {
        if (str_contains($mime, 'png')) {
            return $this->dpiFromPng($path);
        }

        // JPEG: try EXIF
        try {
            $exif = @exif_read_data($path);
            if ($exif && ! empty($exif['XResolution']) && ! empty($exif['ResolutionUnit'])) {
                $unit = (int) $exif['ResolutionUnit']; // 2=inch, 3=cm
                $res  = $this->fractionToFloat($exif['XResolution']);
                if ($res > 0) {
                    return $unit === 3 ? $res * 2.54 : $res;
                }
            }
        } catch (\Throwable) {}

        return self::DEFAULT_DPI;
    }

    private function dpiFromPng(string $path): float
    {
        // PNG pHYs chunk: bytes 33-44 in a standard PNG
        try {
            $fh = fopen($path, 'rb');
            if (! $fh) return self::DEFAULT_DPI;
            fseek($fh, 8); // skip PNG signature
            while (! feof($fh)) {
                $chunk = fread($fh, 8);
                if (strlen($chunk) < 8) break;
                $len  = unpack('N', substr($chunk, 0, 4))[1];
                $type = substr($chunk, 4, 4);
                if ($type === 'pHYs' && $len >= 9) {
                    $data = fread($fh, $len);
                    $xPPU = unpack('N', substr($data, 0, 4))[1];
                    $unit = ord($data[8]);
                    fclose($fh);
                    if ($unit === 1 && $xPPU > 0) {
                        // pixels per metre → convert to DPI
                        return round($xPPU / 39.3701);
                    }
                    return self::DEFAULT_DPI;
                }
                fseek($fh, $len + 4, SEEK_CUR); // skip data + CRC
            }
            fclose($fh);
        } catch (\Throwable) {}

        return self::DEFAULT_DPI;
    }

    private function fractionToFloat(mixed $value): float
    {
        if (is_string($value) && str_contains($value, '/')) {
            [$n, $d] = explode('/', $value);
            return $d > 0 ? (float) $n / (float) $d : 0;
        }
        return (float) $value;
    }
}
