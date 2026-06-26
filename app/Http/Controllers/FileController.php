<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(PrintJob $printJob): Response
    {
        abort_unless(Storage::disk('s3')->exists($printJob->file_path), 404);

        return Storage::disk('s3')->response($printJob->file_path, $printJob->file_name);
    }
}
