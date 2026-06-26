<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->query('month', 'all');
        $year = $request->query('year', (string) now()->year);

        $query = PrintJob::with('size')->orderByDesc('updated_at');

        if ($month !== 'all') {
            $query->whereMonth('cut_at', $month);
        }

        if ($year !== 'all') {
            $query->whereYear('cut_at', $year);
        }

        $jobs = $query->get();
        $completed = $jobs->where('status', 'completed');

        return view('records.index', [
            'jobs' => $jobs,
            'totalJobs' => $completed->count(),
            'totalRevenue' => $completed->sum('total_amount'),
            'month' => $month,
            'year' => $year,
        ]);
    }
}
