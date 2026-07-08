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

    private const GST_RATE = 0.18;

    public function index(Request $request): View
    {
        [$query, $filters] = $this->buildQuery($request);

        $completedQuery = (clone $query)->where('status', 'completed');

        $totalRevenue = $completedQuery->sum('total_amount');
        $gst = round($totalRevenue * self::GST_RATE, 2);

        // Daily summary (completed jobs grouped by dispatched_at date or updated_at)
        $dailySummary = (clone $query)
            ->where('status', 'completed')
            ->selectRaw('DATE(dispatched_at) as day, COUNT(*) as job_count, SUM(total_amount) as subtotal')
            ->groupBy('day')
            ->orderByDesc('day')
            ->get();

        // Monthly summary
        $monthlySummary = (clone $query)
            ->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(dispatched_at, '%Y-%m') as month_key, DATE_FORMAT(dispatched_at, '%M %Y') as month_label, COUNT(*) as job_count, SUM(total_amount) as subtotal")
            ->groupBy('month_key', 'month_label')
            ->orderByDesc('month_key')
            ->get();

        $jobs = $query->orderBy($filters['sort'], $filters['direction'])
            ->paginate(20)
            ->withQueryString();

        $user = $request->user();

        return view('records.index', [
            'jobs' => $jobs,
            'totalJobs' => $completedQuery->count(),
            'totalRevenue' => $totalRevenue,
            'gst' => $gst,
            'gstRate' => self::GST_RATE * 100,
            'grandTotal' => round($totalRevenue + $gst, 2),
            'dailySummary' => $dailySummary,
            'monthlySummary' => $monthlySummary,
            'stations' => $user->isAdmin()
                ? PrintStation::orderBy('name')->get()
                : $user->printStations()->orderBy('name')->get(),
            ...$filters,
        ]);
    }

    public function pdf(Request $request): View
    {
        [$query, $filters] = $this->buildQuery($request);

        $completedQuery = (clone $query)->where('status', 'completed');
        $totalRevenue = $completedQuery->sum('total_amount');
        $gst = round($totalRevenue * self::GST_RATE, 2);

        $dailySummary = (clone $query)
            ->where('status', 'completed')
            ->selectRaw('DATE(dispatched_at) as day, COUNT(*) as job_count, SUM(total_amount) as subtotal')
            ->groupBy('day')
            ->orderByDesc('day')
            ->get();

        $jobs = (clone $query)->where('status', 'completed')
            ->with(['size', 'printStation'])
            ->orderBy('dispatched_at', 'desc')
            ->get();

        return view('records.pdf', [
            'jobs' => $jobs,
            'dailySummary' => $dailySummary,
            'totalRevenue' => $totalRevenue,
            'gst' => $gst,
            'gstRate' => self::GST_RATE * 100,
            'grandTotal' => round($totalRevenue + $gst, 2),
            ...$filters,
        ]);
    }

    /** @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array<string, mixed>} */
    private function buildQuery(Request $request): array
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
            $query->whereMonth('created_at', $month);
        }

        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
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

        return [$query, compact('month', 'year', 'status', 'search', 'stationId', 'sort', 'direction')];
    }
}
