<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(Request $request, PrintJob $printJob): Response
    {
        return $this->serve($request, $printJob);
    }

    public function showPublic(Request $request, PrintJob $printJob): Response
    {
        return $this->serve($request, $printJob);
    }

    private function serve(Request $request, PrintJob $printJob): Response
    {
        abort_unless(Storage::disk('s3')->exists($printJob->file_path), 404);

        if ($request->boolean('download')) {
            return Storage::disk('s3')->download($printJob->file_path, $printJob->file_name);
        }

        return Storage::disk('s3')->response($printJob->file_path, $printJob->file_name);
    }
}
