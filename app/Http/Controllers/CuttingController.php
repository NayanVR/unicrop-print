<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuttingController extends Controller
{
    private const SORTABLE_COLUMNS = ['id', 'sheets', 'printed_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'printed_at');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, self::SORTABLE_COLUMNS, true)) {
            $sort = 'printed_at';
        }

        $query = PrintJob::with('size')->where('status', 'cutting');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        return view('cutting.index', [
            'jobs' => $query->orderBy($sort, $direction)->paginate(15)->withQueryString(),
            'cuttingRate' => (float) Setting::get('cutting_rate', 0),
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
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
