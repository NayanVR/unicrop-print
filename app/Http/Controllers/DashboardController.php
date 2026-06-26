<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending_prints' => PrintJob::where('status', 'pending')->count(),
            'pending_cuts' => PrintJob::where('status', 'cutting')->count(),
            'completed' => PrintJob::where('status', 'completed')->count(),
            'revenue' => PrintJob::where('status', 'completed')->sum('total_amount'),
        ];

        return view('dashboard', $stats);
    }
}
