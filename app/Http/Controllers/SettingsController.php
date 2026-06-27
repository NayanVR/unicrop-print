<?php

namespace App\Http\Controllers;

use App\Models\PrintStation;
use App\Models\Setting;
use App\Models\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'sizes' => Size::orderBy('name')->get(),
            'cuttingRate' => (float) Setting::get('cutting_rate', 0),
            'stations' => PrintStation::orderBy('name')->get(),
        ]);
    }

    public function setDefaultStation(PrintStation $station): RedirectResponse
    {
        PrintStation::query()->update(['is_default' => false]);
        $station->update(['is_default' => true]);

        return redirect()->route('settings.index')->with('status', 'Default print station updated.');
    }

    public function storeSize(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sizes,name'],
            'rate' => ['required', 'numeric', 'min:0.01'],
        ]);

        $size = Size::create($validated);

        if (Size::count() === 1) {
            $size->update(['is_default' => true]);
        }

        return redirect()->route('settings.index')->with('status', 'Size added.');
    }

    public function updateSize(Request $request, Size $size): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sizes,name,'.$size->id],
            'rate' => ['required', 'numeric', 'min:0.01'],
        ]);

        $size->update($validated);

        return redirect()->route('settings.index')->with('status', 'Size updated.');
    }

    public function destroySize(Size $size): RedirectResponse
    {
        if (Size::count() <= 1) {
            return redirect()->route('settings.index')->with('error', 'At least one size is required.');
        }

        $wasDefault = $size->is_default;
        $size->delete();

        if ($wasDefault) {
            Size::first()?->update(['is_default' => true]);
        }

        return redirect()->route('settings.index')->with('status', 'Size deleted.');
    }

    public function setDefaultSize(Size $size): RedirectResponse
    {
        Size::query()->update(['is_default' => false]);
        $size->update(['is_default' => true]);

        return redirect()->route('settings.index')->with('status', 'Default size updated.');
    }

    public function updateCuttingRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cutting_rate' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::set('cutting_rate', $validated['cutting_rate']);

        return redirect()->route('settings.index')->with('status', 'Cutting rate updated.');
    }
}
