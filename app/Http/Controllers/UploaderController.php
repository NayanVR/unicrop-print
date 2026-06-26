<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploaderController extends Controller
{
    public function create(): View
    {
        return view('uploader.create', [
            'sizes' => Size::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'design_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:20480'],
            'note' => ['nullable', 'string', 'max:255'],
            'size_id' => ['required', 'exists:sizes,id'],
            'sheets' => ['required', 'integer', 'min:1'],
        ]);

        $size = Size::findOrFail($validated['size_id']);
        $file = $request->file('design_file');
        $path = $file->store('designs', 's3');

        PrintJob::create([
            'uploaded_by' => $request->user()->id,
            'note' => $validated['note'] ?: '-',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'size_id' => $size->id,
            'rate' => $size->rate,
            'sheets' => $validated['sheets'],
            'status' => 'pending',
        ]);

        return redirect()->route('uploader.create')->with('status', 'File uploaded! Sent to Print Station.');
    }
}
