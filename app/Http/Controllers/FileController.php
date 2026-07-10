<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Request $request, PrintJob $printJob): RedirectResponse
    {
        return $this->serve($request, $printJob);
    }

    public function showPublic(Request $request, PrintJob $printJob): RedirectResponse
    {
        return $this->serve($request, $printJob);
    }

    private function serve(Request $request, PrintJob $printJob): RedirectResponse
    {
        abort_unless(Storage::disk('s3')->exists($printJob->file_path), 404);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        $url = Storage::disk('s3')->temporaryUrl(
            $printJob->file_path,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => $disposition . '; filename="' . rawurlencode($printJob->file_name) . '"',
                'ResponseContentType'        => $printJob->mime_type ?: 'application/octet-stream',
            ]
        );

        return redirect()->away($url);
    }
}
