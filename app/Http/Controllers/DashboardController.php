<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\PrintStation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stations = $user->isAdmin()
            ? PrintStation::orderBy('name')->get()
            : $user->printStations()->orderBy('name')->get();

        $stationStats = $stations->map(fn (PrintStation $station) => [
            'station' => $station,
            'pending_prints' => PrintJob::where('print_station_id', $station->id)->where('status', 'pending')->count(),
            'pending_cuts' => PrintJob::where('print_station_id', $station->id)->where('status', 'cutting')->count(),
            'completed' => PrintJob::where('print_station_id', $station->id)->where('status', 'completed')->count(),
            'revenue' => PrintJob::where('print_station_id', $station->id)->where('status', 'completed')->sum('total_amount'),
        ]);

        $jobQuery = $user->isAdmin()
            ? PrintJob::query()
            : PrintJob::whereIn('print_station_id', $stations->pluck('id'));

        return view('dashboard', [
            'pending_prints' => (clone $jobQuery)->where('status', 'pending')->count(),
            'pending_cuts' => (clone $jobQuery)->where('status', 'cutting')->count(),
            'completed' => (clone $jobQuery)->where('status', 'completed')->count(),
            'revenue' => (clone $jobQuery)->where('status', 'completed')->sum('total_amount'),
            'stationStats' => $stationStats,
        ]);
    }
}
