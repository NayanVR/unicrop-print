<x-app-layout>
    <x-slot name="header">System Settings</x-slot>

    <h2 class="text-2xl font-bold text-slate-900 mb-6">System Settings</h2>

    @php $isFullAdmin = auth()->user()->isAdmin(); @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if ($isFullAdmin)
        <div class="bg-white border-t-4 border-emerald-500 border border-slate-200 rounded-xl p-6">
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
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-4 py-2 rounded-lg h-[42px]">Add Size</button>
            </form>

            <ul class="space-y-2">
                @foreach ($sizes as $size)
                    <li class="flex items-center justify-between border border-slate-200 bg-slate-50 rounded-lg p-3">
                        <div>
                            <strong>{{ $size->name }}</strong>
                            <span class="text-sky-600 font-bold ml-2">[ {{ $size->rate }} Rs/sht ]</span>
                            @if ($size->is_default)
                                <span class="bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
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
                                        <button type="button" onclick="document.getElementById('edit-size-{{ $size->id }}').close()" class="bg-slate-200 text-slate-700 text-xs px-3 py-2 rounded">Cancel</button>
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

        <div class="bg-white border-t-4 border-purple-500 border border-slate-200 rounded-xl p-6 self-start">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-scissors"></i> Cutting Types</h3>
            <p class="text-sm text-slate-500 mb-4">Manage cutting types. Rates are set per station below.</p>

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
                    <li class="flex items-center justify-between border border-slate-200 bg-slate-50 rounded-lg p-3">
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

        <div class="bg-white border-t-4 border-sky-500 border border-slate-200 rounded-xl p-6 self-start lg:col-span-1">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-print"></i> Print Stations</h3>
            <p class="text-sm text-slate-500 mb-4">Manage stations and the default selected on the upload form.</p>

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
                    <li class="flex items-center justify-between border border-slate-200 bg-slate-50 rounded-lg p-3">
                        <div>
                            <strong>{{ $station->name }}</strong>
                            @if ($station->is_default)
                                <span class="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
                            @endif
                            @if (! $station->requires_cutting)
                                <span class="bg-slate-400 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">NO CUTTING</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('settings.stations.cutting', $station) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-slate-500 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded">
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

        <div class="bg-white border-t-4 border-amber-500 border border-slate-200 rounded-xl p-6 lg:col-span-2">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-table-cells"></i> Rate Per Station & Size</h3>
            <p class="text-sm text-slate-500 mb-4">Each print station can charge a different rate for the same sheet size.</p>

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

        <div class="bg-white border-t-4 border-purple-500 border border-slate-200 rounded-xl p-6 lg:col-span-2">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-table-cells"></i> Cutting Rate Per Station & Type</h3>
            <p class="text-sm text-slate-500 mb-4">Each print station can charge a different rate for each cutting type.</p>

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
    </div>
</x-app-layout>
