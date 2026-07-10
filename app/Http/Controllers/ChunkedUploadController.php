<?php

namespace App\Http\Controllers;

use App\Models\CuttingType;
use App\Models\LaminationType;
use App\Models\PrintJob;
use App\Models\PrintJobLabel;
use App\Models\PrintStation;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkedUploadController extends Controller
{
    private const CHUNK_DIR = 'chunk-uploads';

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'upload_id'    => ['required', 'string', 'max:64', 'regex:/^[a-f0-9\-]+$/'],
            'chunk_index'  => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'chunk'        => ['required', 'file', 'max:600'],
        ]);

        $uploadId    = $request->input('upload_id');
        $chunkIndex  = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $chunkFile   = $request->file('chunk');

        $dir = self::CHUNK_DIR . '/' . $uploadId;
        $chunkPath = $dir . '/chunk_' . $chunkIndex;

        Storage::disk('local')->put($chunkPath, file_get_contents($chunkFile->path()));

        // Not the last chunk — just acknowledge
        if ($chunkIndex < $totalChunks - 1) {
            return response()->json(['status' => 'chunk_received', 'chunk' => $chunkIndex]);
        }

        // Last chunk — assemble and process
        return $this->finalise($request, $uploadId, $totalChunks, $dir);
    }

    private function finalise(Request $request, string $uploadId, int $totalChunks, string $dir): JsonResponse
    {
        $request->validate([
            'original_name'      => ['required', 'string', 'max:255'],
            'note'               => ['nullable', 'string', 'max:255'],
            'size_id'            => ['required', 'exists:sizes,id'],
            'print_station_id'   => ['required', 'exists:print_stations,id'],
            'sheets'             => ['required', 'integer', 'min:1'],
            'needs_cutting'      => ['nullable', 'boolean'],
            'cutting_type_id'    => ['nullable', 'exists:cutting_types,id'],
            'needs_lamination'   => ['required', 'boolean'],
            'lamination_type_id' => ['nullable', 'exists:lamination_types,id'],
        ]);

        // Merge chunks into a temp file
        $tmpPath = Storage::disk('local')->path(self::CHUNK_DIR . '/' . $uploadId . '/merged');
        if (! is_dir(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }
        $out = fopen($tmpPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = Storage::disk('local')->path(self::CHUNK_DIR . '/' . $uploadId . '/chunk_' . $i);
            if (! file_exists($chunkPath)) {
                fclose($out);
                $this->cleanup($uploadId);
                return response()->json(['error' => "Missing chunk $i"], 422);
            }
            $in = fopen($chunkPath, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);

        $originalName = $request->input('original_name');
        $mimeType     = mime_content_type($tmpPath) ?: 'application/octet-stream';
        $fileSize     = filesize($tmpPath);

        // Upload merged file to S3
        try {
            $s3Path = Storage::disk('s3')->putFileAs(
                'designs',
                new \Illuminate\Http\File($tmpPath),
                Str::uuid() . '_' . $originalName
            );
        } catch (\Throwable $e) {
            Log::error('Chunked upload S3 failed', ['error' => $e->getMessage()]);
            $this->cleanup($uploadId);
            return response()->json(['error' => 'Storage unavailable. Please try again.'], 500);
        }

        $this->cleanup($uploadId);

        $size     = Size::findOrFail($request->input('size_id'));
        $station  = PrintStation::findOrFail($request->input('print_station_id'));
        $rate     = $station->rateForSize($size);
        $sheets   = (int) $request->input('sheets');

        $needsCutting   = $station->requires_cutting && $request->boolean('needs_cutting');
        $cuttingTypeId  = $needsCutting ? $request->input('cutting_type_id') : null;
        $needsLamination = $request->boolean('needs_lamination');
        $laminationTypeId = $needsLamination ? $request->input('lamination_type_id') : null;
        $laminationRate = 0;
        if ($needsLamination && $laminationTypeId) {
            $laminationType = LaminationType::find($laminationTypeId);
            $station->load('stationLaminationTypes');
            $laminationRate = $station->rateForLaminationType($laminationType);
        }

        $job = PrintJob::create([
            'uploaded_by'        => $request->user()->id,
            'print_station_id'   => $station->id,
            'note'               => $request->input('note') ?: '-',
            'file_path'          => $s3Path,
            'file_name'          => $originalName,
            'file_size'          => $fileSize,
            'mime_type'          => $mimeType,
            'size_id'            => $size->id,
            'rate'               => $rate,
            'sheets'             => $sheets,
            'needs_cutting'      => $needsCutting,
            'cutting_type_id'    => $cuttingTypeId,
            'needs_lamination'   => $needsLamination,
            'lamination_type_id' => $laminationTypeId,
            'lamination_rate'    => $laminationRate,
            'lamination_total'   => $laminationRate * $sheets,
            'status'             => 'pending',
        ]);

        // Save label contents
        $labels = $request->input('labels', []);
        foreach ($labels as $item) {
            if (! empty($item['name']) && ! empty($item['pcs'])) {
                PrintJobLabel::create([
                    'print_job_id'  => $job->id,
                    'label_name'    => $item['name'],
                    'pcs_per_sheet' => (int) $item['pcs'],
                ]);
            }
        }

        return response()->json(['status' => 'done', 'job_id' => $job->id]);
    }

    private function cleanup(string $uploadId): void
    {
        Storage::disk('local')->deleteDirectory(self::CHUNK_DIR . '/' . $uploadId);
    }
}
