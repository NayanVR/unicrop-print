<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BinController extends Controller
{
    public function index(): View
    {
        $days = (int) Setting::get('bin_days', 30);

        $jobs = PrintJob::onlyTrashed()
            ->with(['printStation', 'size', 'uploader'])
            ->orderByDesc('deleted_at')
            ->get();

        return view('bin.index', [
            'jobs'    => $jobs,
            'binDays' => $days,
        ]);
    }

    public function restore(PrintJob $printJob): RedirectResponse
    {
        $printJob->restore();

        return back()->with('status', "Job #$printJob->id restored.");
    }

    public function destroy(PrintJob $printJob): RedirectResponse
    {
        if ($printJob->file_path) {
            Storage::disk('s3')->delete($printJob->file_path);
        }
        $printJob->forceDelete();

        return back()->with('status', "Job #$printJob->id permanently deleted.");
    }

    public function purge(): RedirectResponse
    {
        $days = (int) Setting::get('bin_days', 30);

        $jobs = PrintJob::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays($days))
            ->get();

        foreach ($jobs as $job) {
            if ($job->file_path) {
                Storage::disk('s3')->delete($job->file_path);
            }
            $job->forceDelete();
        }

        return back()->with('status', "Purged {$jobs->count()} expired job(s) from bin.");
    }

    public function setBinDays(Request $request): RedirectResponse
    {
        $request->validate(['bin_days' => ['required', 'integer', 'min:1', 'max:365']]);
        Setting::set('bin_days', $request->input('bin_days'));

        return back()->with('status', 'Bin auto-delete period updated.');
    }
}
