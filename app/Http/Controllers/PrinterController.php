<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrinterController extends Controller
{
    public function index(): View
    {
        return view('printer.index', [
            'jobs' => PrintJob::with('size')->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function update(Request $request, PrintJob $printJob): RedirectResponse
    {
        $validated = $request->validate([
            'sheets' => ['required', 'integer', 'min:1'],
        ]);

        $printTotal = $validated['sheets'] * $printJob->rate;

        $printJob->update([
            'sheets' => $validated['sheets'],
            'print_total' => $printTotal,
            'total_amount' => $printTotal,
            'status' => 'cutting',
            'printed_at' => now(),
        ]);

        return redirect()->route('printer.index')->with('status', 'Print marked done! Job sent to Cutting Station.');
    }
}
