<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuttingController extends Controller
{
    public function index(): View
    {
        return view('cutting.index', [
            'jobs' => PrintJob::with('size')->where('status', 'cutting')->latest()->get(),
            'cuttingRate' => (float) Setting::get('cutting_rate', 0),
        ]);
    }

    public function update(Request $request, PrintJob $printJob): RedirectResponse
    {
        $validated = $request->validate([
            'cutting_jobs' => ['required', 'integer', 'min:0'],
        ]);

        $cuttingRate = (float) Setting::get('cutting_rate', 0);
        $cuttingTotal = $validated['cutting_jobs'] * $cuttingRate;

        $printJob->update([
            'cutting_jobs' => $validated['cutting_jobs'],
            'cutting_rate' => $cuttingRate,
            'cutting_total' => $cuttingTotal,
            'total_amount' => $printJob->print_total + $cuttingTotal,
            'status' => 'completed',
            'cut_at' => now(),
        ]);

        return redirect()->route('cutting.index')->with('status', "Cutting done! Final bill: {$printJob->total_amount} Rs.");
    }
}
