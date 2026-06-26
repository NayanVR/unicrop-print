<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrinterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = PrintJob::with(['size', 'printStation'])->where('status', 'pending');

        if (! $user->isAdmin()) {
            $query->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
        }

        return view('printer.index', [
            'jobs' => $query->latest()->get(),
            'canPrint' => $user->isAdmin() || $user->can_print,
        ]);
    }

    public function update(Request $request, PrintJob $printJob): RedirectResponse
    {
        if (! $request->user()->isAdmin() && ! $request->user()->can_print) {
            abort(403);
        }

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
