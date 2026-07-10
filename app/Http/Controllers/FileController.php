<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function show(Request $request, PrintJob $printJob): StreamedResponse
    {
        return $this->serve($request, $printJob);
    }

    public function showPublic(Request $request, PrintJob $printJob): StreamedResponse
    {
        return $this->serve($request, $printJob);
    }

    private function serve(Request $request, PrintJob $printJob): StreamedResponse
    {
        abort_unless(Storage::disk('s3')->exists($printJob->file_path), 404);

        $disk        = Storage::disk('s3');
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $mime        = $printJob->mime_type ?: 'application/octet-stream';
        $size        = $disk->size($printJob->file_path);
        $filename    = rawurlencode($printJob->file_name);

        return response()->stream(
            function () use ($disk, $printJob) {
                $stream = $disk->readStream($printJob->file_path);
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type'        => $mime,
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
                'Content-Length'      => $size,
                'Cache-Control'       => 'private, no-store',
            ]
        );
    }
}
