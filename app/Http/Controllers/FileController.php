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

        if ($request->boolean('download')) {
            return Storage::disk('s3')->download($printJob->file_path, $printJob->file_name);
        }

        return Storage::disk('s3')->response($printJob->file_path, $printJob->file_name);
    }
}
