<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->query('date', now()->toDateString());

        $stationFilter = function ($q) use ($user) {
            if (! $user->isAdmin()) {
                $q->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
            }
        };

        $query = PrintJob::with(['size', 'printStation', 'cuttingType'])
            ->where('status', 'dispatch')
            ->whereDate('updated_at', $date)
            ->tap($stationFilter)
            ->orderBy('updated_at', 'desc');

        $allQuery = PrintJob::with(['size', 'printStation', 'cuttingType'])
            ->where('status', 'dispatch')
            ->whereDate('updated_at', '!=', $date)
            ->tap($stationFilter)
            ->orderBy('updated_at', 'desc');

        return view('dispatch.index', [
            'todayJobs' => $query->get(),
            'otherJobs' => $allQuery->get(),
            'date' => $date,
        ]);
    }

    public function bulkDispatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'exists:print_jobs,id'],
        ]);

        $user = $request->user();
        $q = PrintJob::whereIn('id', $validated['job_ids'])->where('status', 'dispatch');
        if (! $user->isAdmin()) {
            $q->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
        }
        $q->update([
                'status' => 'completed',
                'dispatched_at' => now(),
            ]);

        $count = count($validated['job_ids']);

        return redirect()->route('dispatch.index')->with('status', "{$count} job(s) dispatched successfully!");
    }
}
