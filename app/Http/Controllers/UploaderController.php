<?php

namespace App\Http\Controllers;

use App\Models\CuttingType;
use App\Models\PrintJobLabel;
use App\Models\LaminationType;
use App\Models\PrintJob;
use App\Models\PrintStation;
use App\Models\PrintStationCuttingType;
use App\Models\PrintStationLaminationType;
use App\Models\PrintStationSize;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class UploaderController extends Controller
{
    public function create(Request $request): View
    {
        return view('uploader.create', [
            'sizes' => Size::orderBy('name')->get(),
            'stations' => PrintStation::orderBy('name')->get(),
            'stationRates' => PrintStationSize::all()->groupBy('print_station_id'),
            'cuttingTypes' => CuttingType::orderBy('name')->get(),
            'stationCuttingRates' => PrintStationCuttingType::all()->groupBy('print_station_id'),
            'laminationTypes' => LaminationType::orderBy('name')->get(),
            'stationLaminationRates' => PrintStationLaminationType::all()->groupBy('print_station_id'),
            'myJobs' => PrintJob::with(['printStation', 'size', 'jobLabels'])
                ->where('uploaded_by', $request->user()->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
            'allJobs' => PrintJob::with(['printStation', 'size', 'uploader', 'jobLabels'])
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
            'dailySummary' => $this->dailySummary(),
            'labelSuggestions' => PrintJobLabel::query()
                ->selectRaw('DISTINCT label_name')
                ->orderBy('label_name')
                ->pluck('label_name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'design_file' => ['required', 'file', 'max:204800'],
            'note' => ['nullable', 'string', 'max:255'],
            'labels' => ['nullable', 'array', 'max:20'],
            'labels.*.name' => ['required_with:labels', 'string', 'max:100'],
            'labels.*.pcs' => ['required_with:labels', 'integer', 'min:1'],
            'size_id' => ['required', 'exists:sizes,id'],
            'print_station_id' => ['required', 'exists:print_stations,id'],
            'sheets' => ['required', 'integer', 'min:1'],
            'needs_cutting' => ['nullable', 'boolean'],
            'cutting_type_id' => ['nullable', 'required_if:needs_cutting,1', 'exists:cutting_types,id'],
            'needs_lamination' => ['required', 'boolean'],
            'lamination_type_id' => ['nullable', 'required_if:needs_lamination,1', 'exists:lamination_types,id'],
        ]);

        $size = Size::findOrFail($validated['size_id']);
        $station = PrintStation::findOrFail($validated['print_station_id']);
        $rate = $station->rateForSize($size);
        $needsCutting = $station->requires_cutting && $request->boolean('needs_cutting');
        $cuttingTypeId = $needsCutting ? $validated['cutting_type_id'] : null;
        $needsLamination = $request->boolean('needs_lamination');
        $laminationTypeId = $needsLamination ? $validated['lamination_type_id'] : null;
        $laminationRate = $needsLamination && $laminationTypeId
            ? $station->load('stationLaminationTypes')->rateForLaminationType(LaminationType::find($laminationTypeId))
            : 0;
        $laminationTotal = $laminationRate * $validated['sheets'];
        $file = $request->file('design_file');
        $fileSize = $file->getSize();
        $mimeType = $file->getClientMimeType();

        try {
            $path = $file->store('designs', 's3');
        } catch (Throwable $e) {
            Log::error('Design file upload to S3 failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('status', 'Upload failed: storage is unavailable. Please try again or contact an admin.');
        }

        $job = PrintJob::create([
            'uploaded_by' => $request->user()->id,
            'print_station_id' => $validated['print_station_id'],
            'note' => $validated['note'] ?: '-',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'size_id' => $size->id,
            'rate' => $rate,
            'sheets' => $validated['sheets'],
            'needs_cutting' => $needsCutting,
            'cutting_type_id' => $cuttingTypeId,
            'needs_lamination' => $needsLamination,
            'lamination_type_id' => $laminationTypeId,
            'lamination_rate' => $laminationRate,
            'lamination_total' => $laminationTotal,
            'status' => 'pending',
        ]);

        if (! empty($validated['labels'])) {
            foreach ($validated['labels'] as $item) {
                if (! empty($item['name']) && ! empty($item['pcs'])) {
                    PrintJobLabel::create([
                        'print_job_id' => $job->id,
                        'label_name'   => $item['name'],
                        'pcs_per_sheet' => $item['pcs'],
                    ]);
                }
            }
        }

        return redirect()->route('uploader.create')->with('status', 'File uploaded! Sent to Print Station.');
    }

    private function dailySummary(): \Illuminate\Support\Collection
    {
        return PrintJobLabel::query()
            ->join('print_jobs', 'print_jobs.id', '=', 'print_job_labels.print_job_id')
            ->whereDate('print_jobs.created_at', today())
            ->selectRaw('print_job_labels.label_name, SUM(print_job_labels.pcs_per_sheet * print_jobs.sheets) as total_pcs')
            ->groupBy('print_job_labels.label_name')
            ->orderByDesc('total_pcs')
            ->get();
    }
}
