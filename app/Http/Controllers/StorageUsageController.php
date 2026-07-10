<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\View\View;

class StorageUsageController extends Controller
{
    public function index(): View
    {
        // Active jobs
        $activeBytes = PrintJob::sum('file_size');
        $activeCount = PrintJob::count();

        // Bin (soft-deleted)
        $binBytes = PrintJob::onlyTrashed()->sum('file_size');
        $binCount = PrintJob::onlyTrashed()->count();

        $totalBytes = $activeBytes + $binBytes;

        // Per-uploader breakdown
        $byUploader = PrintJob::withTrashed()
            ->join('users', 'users.id', '=', 'print_jobs.uploaded_by')
            ->selectRaw('users.name, SUM(print_jobs.file_size) as bytes, COUNT(*) as files')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('bytes')
            ->get();

        // Per-day usage (last 30 days)
        $byDay = PrintJob::withTrashed()
            ->selectRaw('DATE(created_at) as day, SUM(file_size) as bytes, COUNT(*) as files')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('storage.index', [
            'activeBytes' => $activeBytes,
            'activeCount' => $activeCount,
            'binBytes'    => $binBytes,
            'binCount'    => $binCount,
            'totalBytes'  => $totalBytes,
            'byUploader'  => $byUploader,
            'byDay'       => $byDay,
        ]);
    }
}
