<x-app-layout>
    <x-slot name="header">System Settings</x-slot>

    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:48px;letter-spacing:0.06em;color:#111;line-height:1;margin-bottom:24px;">System Settings</h2>

    @php $isFullAdmin = auth()->user()->isAdmin(); @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if ($isFullAdmin)
        <div class="bg-white border-t-4 border-t-orange-500 border border-gray-200 rounded-xl p-6">
            <h3 class="font-semibold mb-5 flex items-center gap-2"><i class="fa-solid fa-expand"></i> Manage Print Sizes & Rates</h3>

            <form method="POST" action="{{ route('settings.sizes.store') }}" class="flex gap-3 items-end mb-6">
                @csrf
                <div class="flex-[2]">
                    <label class="block text-sm font-semibold mb-1">Sheet Size Name</label>
                    <input type="text" name="name" placeholder="e.g., 12x18 Jumbo" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-semibold mb-1">Rate (Rs)</label>
                    <input type="number" name="rate" step="0.01" min="1" placeholder="e.g., 25" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" style="background:#F05A28;color:#fff;border:none;padding:0 16px;height:42px;border-radius:8px;font-weight:600;font-size:13.5px;cursor:pointer;">Add Size</button>
            </form>

            <ul class="space-y-2">
                @foreach ($sizes as $size)
                    <li style="display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E5E5E5;background:#FAFAF8;border-radius:9px;padding:10px 12px;">
                        <div>
                            <strong>{{ $size->name }}</strong>
                            <span class="text-sky-600 font-bold ml-2">[ {{ $size->rate }} Rs/sht ]</span>
                            @if ($size->is_default)
                                <span style="background:#F05A28;color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:4px;margin-left:6px;letter-spacing:0.04em;">DEFAULT</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @unless ($size->is_default)
                                <form method="POST" action="{{ route('settings.sizes.default', $size) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1.5 rounded">Default</button>
                                </form>
                            @endunless
                            <button type="button" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded"
                                onclick="document.getElementById('edit-size-{{ $size->id }}').showModal()">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <dialog id="edit-size-{{ $size->id }}" class="rounded-lg p-6 w-80 backdrop:bg-black/40">
                                <form method="POST" action="{{ route('settings.sizes.update', $size) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-sm font-semibold mb-1">Sheet Size Name</label>
                                        <input type="text" name="name" value="{{ $size->name }}" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-1">Rate (Rs)</label>
                                        <input type="number" name="rate" step="0.01" min="1" value="{{ $size->rate }}" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" onclick="document.getElementById('edit-size-{{ $size->id }}').close()" style="background:#F0F0EE;color:#555;border:none;padding:6px 12px;border-radius:6px;font-size:12.5px;cursor:pointer;">Cancel</button>
                                        <button type="submit" class="bg-amber-500 text-white text-xs px-3 py-2 rounded">Update</button>
                                    </div>
                                </form>
                            </dialog>
                            <form method="POST" action="{{ route('settings.sizes.destroy', $size) }}" onsubmit="return confirm('Delete this size?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #111;border-radius:14px;padding:22px;align-self:start;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-scissors"></i> Cutting Types</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Manage cutting types. Rates are set per station below.</p>

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('settings.cutting-types.store') }}" class="flex gap-3 items-end mb-4">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-semibold mb-1">Cutting Type Name</label>
                        <input type="text" name="name" placeholder="e.g., Full Cut" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg h-[42px]">Add Type</button>
                </form>
            @endif

            <ul class="space-y-2">
                @foreach ($cuttingTypes as $type)
                    <li style="display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E5E5E5;background:#FAFAF8;border-radius:9px;padding:10px 12px;">
                        <div>
                            <strong>{{ $type->name }}</strong>
                            @if ($type->is_default)
                                <span class="bg-purple-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @unless ($type->is_default)
                                <form method="POST" action="{{ route('settings.cutting-types.default', $type) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1.5 rounded">Default</button>
                                </form>
                            @endunless
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('settings.cutting-types.destroy', $type) }}" onsubmit="return confirm('Delete this cutting type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #F05A28;border-radius:14px;padding:22px;align-self:start;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-print"></i> Print Stations</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Manage stations and the default selected on the upload form.</p>

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('settings.stations.store') }}" class="flex gap-3 items-end mb-4">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-semibold mb-1">Station Name</label>
                        <input type="text" name="name" placeholder="e.g., Pranjal" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold px-4 py-2 rounded-lg h-[42px]">Add Station</button>
                </form>
            @endif

            <ul class="space-y-2">
                @foreach ($stations as $station)
                    <li style="display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E5E5E5;background:#FAFAF8;border-radius:9px;padding:10px 12px;">
                        <div>
                            <strong>{{ $station->name }}</strong>
                            @if ($station->is_default)
                                <span class="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
                            @endif
                            @if (! $station->requires_cutting)
                                <span style="background:#717171;color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:4px;margin-left:6px;">NO CUTTING</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('settings.stations.cutting', $station) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="background:#111;color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    {{ $station->requires_cutting ? 'Disable Cutting' : 'Enable Cutting' }}
                                </button>
                            </form>
                            @unless ($station->is_default)
                                <form method="POST" action="{{ route('settings.stations.default', $station) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1.5 rounded">Default</button>
                                </form>
                            @endunless
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('settings.stations.destroy', $station) }}" onsubmit="return confirm('Delete this print station?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (! $isFullAdmin)
            <div class="bg-sky-50 border border-sky-200 text-sky-700 text-sm rounded-xl px-4 py-3 mb-2 lg:col-span-2 flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i>
                You can only view and edit rates for your assigned station(s).
            </div>
        @endif

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #F05A28;border-radius:14px;padding:22px;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-table-cells"></i> Rate Per Station & Size</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Each print station can charge a different rate for the same sheet size.</p>

            <form method="POST" action="{{ route('settings.station-rates.update') }}">
                @csrf
                @method('PATCH')
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                @foreach ($sizes as $size)
                                    <th class="px-4 py-3 font-semibold">{{ $size->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($stations as $station)
                                <tr>
                                    <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $station->name }}</td>
                                    @foreach ($sizes as $size)
                                        @php
                                            $rate = ($stationRates[$station->id] ?? collect())->firstWhere('size_id', $size->id)?->rate ?? $size->rate;
                                        @endphp
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0.01"
                                                name="rates[{{ $station->id }}][{{ $size->id }}]"
                                                value="{{ $rate }}"
                                                class="w-24 rounded border-slate-300 px-2 py-1 text-sm">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="mt-4 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-lg">Save Rates</button>
            </form>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #111;border-radius:14px;padding:22px;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-table-cells"></i> Cutting Rate Per Station & Type</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Each print station can charge a different rate for each cutting type.</p>

            <form method="POST" action="{{ route('settings.station-cutting-rates.update') }}">
                @csrf
                @method('PATCH')
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                @foreach ($cuttingTypes as $type)
                                    <th class="px-4 py-3 font-semibold">{{ $type->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($stations as $station)
                                <tr>
                                    <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $station->name }}</td>
                                    @foreach ($cuttingTypes as $type)
                                        @php
                                            $rate = ($stationCuttingRates[$station->id] ?? collect())->firstWhere('cutting_type_id', $type->id)?->rate ?? 0;
                                        @endphp
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0"
                                                name="cutting_rates[{{ $station->id }}][{{ $type->id }}]"
                                                value="{{ $rate }}"
                                                class="w-24 rounded border-slate-300 px-2 py-1 text-sm">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="mt-4 bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg">Save Cutting Rates</button>
            </form>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #111;border-radius:14px;padding:22px;align-self:start;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-layer-group"></i> Lamination Types</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Manage lamination types. Rates are set per station below.</p>

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('settings.lamination-types.store') }}" class="flex gap-3 items-end mb-4">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-sm font-semibold mb-1">Lamination Type Name</label>
                        <input type="text" name="name" placeholder="e.g., Glossy" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg h-[42px]">Add Type</button>
                </form>
            @endif

            <ul class="space-y-2">
                @foreach ($laminationTypes as $type)
                    <li style="display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E5E5E5;background:#FAFAF8;border-radius:9px;padding:10px 12px;">
                        <div>
                            <strong>{{ $type->name }}</strong>
                            @if ($type->is_default)
                                <span class="bg-indigo-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @unless ($type->is_default)
                                <form method="POST" action="{{ route('settings.lamination-types.default', $type) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1.5 rounded">Default</button>
                                </form>
                            @endunless
                            @if (auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('settings.lamination-types.destroy', $type) }}" onsubmit="return confirm('Delete this lamination type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #111;border-radius:14px;padding:22px;">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-table-cells"></i> Lamination Rate Per Station & Type</h3>
            <p style="font-size:13px;color:#717171;margin-bottom:14px;">Each print station can charge a different rate for each lamination type (per sheet).</p>

            <form method="POST" action="{{ route('settings.station-lamination-rates.update') }}">
                @csrf
                @method('PATCH')
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                @foreach ($laminationTypes as $type)
                                    <th class="px-4 py-3 font-semibold">{{ $type->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($stations as $station)
                                <tr>
                                    <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $station->name }}</td>
                                    @foreach ($laminationTypes as $type)
                                        @php
                                            $rate = ($stationLaminationRates[$station->id] ?? collect())->firstWhere('lamination_type_id', $type->id)?->rate ?? 0;
                                        @endphp
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" min="0"
                                                name="lamination_rates[{{ $station->id }}][{{ $type->id }}]"
                                                value="{{ $rate }}"
                                                class="w-24 rounded border-slate-300 px-2 py-1 text-sm">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="mt-4 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg">Save Lamination Rates</button>
            </form>
        </div>

    </div>
</x-app-layout>
