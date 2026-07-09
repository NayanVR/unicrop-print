<?php

namespace App\Http\Controllers;

use App\Models\BottleSize;
use App\Models\BottleSizeGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelCheckerController extends Controller
{
    private const TOLERANCE_MM = 2.0;
    private const DEFAULT_DPI  = 300;

    private function viewData(): array
    {
        return [
            'groups'      => BottleSizeGroup::orderBy('name')->with('bottleSizes')->get(),
            'bottleSizes' => BottleSize::orderBy('name')->get(),
        ];
    }

    public function index(): View
    {
        return view('label-checker.index', $this->viewData());
    }

    public function check(Request $request): View
    {
        $request->validate([
            'label_files'   => ['required', 'array', 'min:1', 'max:50'],
            'label_files.*' => ['file', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        $bottles = BottleSize::orderBy('name')->get();
        $results = [];

        foreach ($request->file('label_files') as $file) {
            $imageInfo = @getimagesize($file->path());

            if (! $imageInfo || $imageInfo[0] === 0) {
                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'error'    => 'Could not read image dimensions.',
                ];
                continue;
            }

            $pixelW   = $imageInfo[0];
            $pixelH   = $imageInfo[1];
            $dpi      = $this->detectDpi($file->path(), $imageInfo['mime'] ?? '');
            $widthMm  = round(($pixelW / $dpi) * 25.4, 1);
            $heightMm = round(($pixelH / $dpi) * 25.4, 1);
            $tol      = self::TOLERANCE_MM;

            $matches = $bottles->filter(function (BottleSize $b) use ($widthMm, $heightMm, $tol) {
                $bw = (float) $b->label_width_mm;
                $bh = (float) $b->label_height_mm;
                return (abs($widthMm - $bw) <= $tol && abs($heightMm - $bh) <= $tol)
                    || (abs($widthMm - $bh) <= $tol && abs($heightMm - $bw) <= $tol);
            });

            $results[] = [
                'filename' => $file->getClientOriginalName(),
                'pixelW'   => $pixelW,
                'pixelH'   => $pixelH,
                'dpi'      => $dpi,
                'widthMm'  => $widthMm,
                'heightMm' => $heightMm,
                'matches'  => $matches,
            ];
        }

        return view('label-checker.index', array_merge($this->viewData(), [
            'results' => $results,
        ]));
    }

    private function detectDpi(string $path, string $mime): float
    {
        if (str_contains($mime, 'png')) {
            return $this->dpiFromPng($path);
        }

        try {
            $exif = @exif_read_data($path);
            if ($exif && ! empty($exif['XResolution']) && ! empty($exif['ResolutionUnit'])) {
                $unit = (int) $exif['ResolutionUnit'];
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
        try {
            $fh = fopen($path, 'rb');
            if (! $fh) return self::DEFAULT_DPI;
            fseek($fh, 8);
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
                        return round($xPPU / 39.3701);
                    }
                    return self::DEFAULT_DPI;
                }
                fseek($fh, $len + 4, SEEK_CUR);
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
