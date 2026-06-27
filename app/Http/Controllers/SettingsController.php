<?php

namespace App\Http\Controllers;

use App\Models\CuttingType;
use App\Models\PrintStation;
use App\Models\PrintStationCuttingType;
use App\Models\PrintStationSize;
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
            'stationRates' => PrintStationSize::all()->groupBy('print_station_id'),
            'cuttingTypes' => CuttingType::orderBy('name')->get(),
            'stationCuttingRates' => PrintStationCuttingType::all()->groupBy('print_station_id'),
        ]);
    }

    public function setDefaultStation(PrintStation $station): RedirectResponse
    {
        PrintStation::query()->update(['is_default' => false]);
        $station->update(['is_default' => true]);

        return redirect()->route('settings.index')->with('status', 'Default print station updated.');
    }

    public function storeStation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:print_stations,name'],
        ]);

        $station = PrintStation::create($validated);

        if (PrintStation::count() === 1) {
            $station->update(['is_default' => true]);
        }

        foreach (Size::all() as $size) {
            PrintStationSize::create([
                'print_station_id' => $station->id,
                'size_id' => $size->id,
                'rate' => $size->rate,
            ]);
        }

        foreach (CuttingType::all() as $type) {
            PrintStationCuttingType::create([
                'print_station_id' => $station->id,
                'cutting_type_id' => $type->id,
                'rate' => 0,
            ]);
        }

        return redirect()->route('settings.index')->with('status', 'Print station added.');
    }

    public function toggleStationCutting(PrintStation $station): RedirectResponse
    {
        $station->update(['requires_cutting' => ! $station->requires_cutting]);

        return redirect()->route('settings.index')->with('status', 'Print station cutting requirement updated.');
    }

    public function destroyStation(PrintStation $station): RedirectResponse
    {
        if (PrintStation::count() <= 1) {
            return redirect()->route('settings.index')->with('error', 'At least one print station is required.');
        }

        $wasDefault = $station->is_default;
        $station->delete();

        if ($wasDefault) {
            PrintStation::first()?->update(['is_default' => true]);
        }

        return redirect()->route('settings.index')->with('status', 'Print station deleted.');
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

        foreach (PrintStation::all() as $station) {
            PrintStationSize::create([
                'print_station_id' => $station->id,
                'size_id' => $size->id,
                'rate' => $size->rate,
            ]);
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

    public function updateStationRates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*.*' => ['required', 'numeric', 'min:0.01'],
        ]);

        foreach ($validated['rates'] as $stationId => $sizeRates) {
            foreach ($sizeRates as $sizeId => $rate) {
                PrintStationSize::updateOrCreate(
                    ['print_station_id' => $stationId, 'size_id' => $sizeId],
                    ['rate' => $rate],
                );
            }
        }

        return redirect()->route('settings.index')->with('status', 'Print station rates updated.');
    }

    public function updateCuttingRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cutting_rate' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::set('cutting_rate', $validated['cutting_rate']);

        return redirect()->route('settings.index')->with('status', 'Cutting rate updated.');
    }

    public function storeCuttingType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:cutting_types,name'],
        ]);

        $type = CuttingType::create($validated);

        if (CuttingType::count() === 1) {
            $type->update(['is_default' => true]);
        }

        foreach (PrintStation::all() as $station) {
            PrintStationCuttingType::create([
                'print_station_id' => $station->id,
                'cutting_type_id' => $type->id,
                'rate' => 0,
            ]);
        }

        return redirect()->route('settings.index')->with('status', 'Cutting type added.');
    }

    public function destroyCuttingType(CuttingType $cuttingType): RedirectResponse
    {
        if (CuttingType::count() <= 1) {
            return redirect()->route('settings.index')->with('error', 'At least one cutting type is required.');
        }

        $wasDefault = $cuttingType->is_default;
        $cuttingType->delete();

        if ($wasDefault) {
            CuttingType::first()?->update(['is_default' => true]);
        }

        return redirect()->route('settings.index')->with('status', 'Cutting type deleted.');
    }

    public function setDefaultCuttingType(CuttingType $cuttingType): RedirectResponse
    {
        CuttingType::query()->update(['is_default' => false]);
        $cuttingType->update(['is_default' => true]);

        return redirect()->route('settings.index')->with('status', 'Default cutting type updated.');
    }

    public function updateStationCuttingRates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cutting_rates' => ['required', 'array'],
            'cutting_rates.*.*' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['cutting_rates'] as $stationId => $typeRates) {
            foreach ($typeRates as $typeId => $rate) {
                PrintStationCuttingType::updateOrCreate(
                    ['print_station_id' => $stationId, 'cutting_type_id' => $typeId],
                    ['rate' => $rate],
                );
            }
        }

        return redirect()->route('settings.index')->with('status', 'Print station cutting rates updated.');
    }
}
