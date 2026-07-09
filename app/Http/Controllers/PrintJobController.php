<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintJobController extends Controller
{
    public function updateNote(Request $request, PrintJob $printJob): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $printJob->update(['note' => $validated['note'] ?: '-']);

        return back()->with('status', "Note updated for Job #$printJob->id.");
    }

    public function destroy(Request $request, PrintJob $printJob): RedirectResponse
    {
        $user = $request->user();

        // Only the original uploader (or admin) can delete, and only while pending
        if (! $user->isAdmin() && $printJob->uploaded_by !== $user->id) {
            abort(403);
        }

        if (! $user->isAdmin() && $printJob->status->value !== 'pending') {
            return back()->with('error', 'Only pending jobs can be deleted.');
        }

        $printJob->delete(); // soft delete — goes to bin

        return back()->with('status', "Job #$printJob->id moved to bin.");
    }
}
