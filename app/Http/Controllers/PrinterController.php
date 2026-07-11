<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\PrintStation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrinterController extends Controller
{
    private const SORTABLE_COLUMNS = ['id', 'created_at', 'sheets', 'rate'];

    public function index(Request $request): View
    {
        $user = $request->user();

        $search = trim((string) $request->query('search', ''));
        $stationId = $request->query('station_id', 'all');
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'created_at';
        }

        $query = PrintJob::with(['size', 'printStation', 'jobLabels'])->where('status', 'pending');

        if (! $user->isAdmin()) {
            $query->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
        }

        if ($stationId !== 'all') {
            $query->where('print_station_id', $stationId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        return view('printer.index', [
            'jobs' => $query->orderBy($sort, $direction)->paginate(15)->withQueryString(),
            'canPrint' => $user->isAdmin() || $user->can_print,
            'stations' => PrintStation::orderBy('name')->get(),
            'search' => $search,
            'stationId' => $stationId,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function poll(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $query = PrintJob::where('status', 'pending');

        if (! $user->isAdmin()) {
            $query->whereIn('print_station_id', $user->printStations()->pluck('print_stations.id'));
        }

        return response()->json([
            'count' => $query->count(),
            'latest_id' => $query->max('id') ?? 0,
        ]);
    }

    public function update(Request $request, PrintJob $printJob): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('print_station') || (! $user->isAdmin() && ! $user->can_print)) {
            abort(403);
        }

        $validated = $request->validate([
            'sheets' => ['required', 'integer', 'min:1'],
            'cutting_required' => ['required', 'boolean'],
        ]);

        $printTotal = $validated['sheets'] * $printJob->rate;

        if ($validated['cutting_required']) {
            $printJob->update([
                'sheets' => $validated['sheets'],
                'print_total' => $printTotal,
                'total_amount' => $printTotal,
                'status' => 'cutting',
                'printed_at' => now(),
            ]);

            return redirect()->route('printer.index')->with('status', 'Print marked done! Job sent to Cutting Station.');
        }

        $printJob->update([
            'sheets' => $validated['sheets'],
            'print_total' => $printTotal,
            'total_amount' => $printTotal,
            'status' => 'dispatch',
            'printed_at' => now(),
        ]);

        return redirect()->route('printer.index')->with('status', 'Print marked done! Job sent to Dispatch.');
    }
}
