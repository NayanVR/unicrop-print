<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\PrintStation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    private const SORTABLE_COLUMNS = [
        'id', 'total_amount', 'created_at', 'printed_at', 'cut_at', 'updated_at',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();

        $month = $request->query('month', 'all');
        $year = $request->query('year', (string) now()->year);
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $stationId = $request->query('station_id', 'all');
        $sort = $request->query('sort', 'updated_at');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'updated_at';
        }

        $query = PrintJob::with(['size', 'printStation']);

        if (! $user->isAdmin()) {
            $query->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
        }

        if ($stationId !== 'all') {
            $query->where('print_station_id', $stationId);
        }

        if ($month !== 'all') {
            $query->whereMonth('cut_at', $month);
        }

        if ($year !== 'all') {
            $query->whereYear('cut_at', $year);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $totals = (clone $query)->where('status', 'completed');

        $jobs = $query->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('records.index', [
            'jobs' => $jobs,
            'totalJobs' => $totals->count(),
            'totalRevenue' => $totals->sum('total_amount'),
            'month' => $month,
            'year' => $year,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'stationId' => $stationId,
            'stations' => $user->isAdmin()
                ? PrintStation::orderBy('name')->get()
                : $user->printStations()->orderBy('name')->get(),
        ]);
    }
}
