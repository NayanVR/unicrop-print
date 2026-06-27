<x-app-layout>
    <x-slot name="header">System Settings</x-slot>

    <h2 class="text-2xl font-bold text-slate-900 mb-6">System Settings</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-scissors"></i> Global Cutting Charge</h3>
            <p class="text-sm text-slate-500 mb-4">Set the default cost for 1 cutting job.</p>
            <form method="POST" action="{{ route('settings.cutting-rate.update') }}">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-semibold mb-1">Rate Per Cut (Rs)</label>
                <input type="number" name="cutting_rate" step="0.01" min="0" value="{{ $cuttingRate }}" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm mb-4">
                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg">Update Cutting Rate</button>
            </form>
        </div>

        <div class="bg-white border-t-4 border-sky-500 border border-slate-200 rounded-xl p-6 self-start">
            <h3 class="font-semibold mb-2 flex items-center gap-2"><i class="fa-solid fa-print"></i> Default Print Station</h3>
            <p class="text-sm text-slate-500 mb-4">Pre-selected station on the upload form.</p>
            <ul class="space-y-2">
                @foreach ($stations as $station)
                    <li class="flex items-center justify-between border border-slate-200 bg-slate-50 rounded-lg p-3">
                        <div>
                            <strong>{{ $station->name }}</strong>
                            @if ($station->is_default)
                                <span class="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-2">DEFAULT</span>
                            @endif
                        </div>
                        @unless ($station->is_default)
                            <form method="POST" action="{{ route('settings.stations.default', $station) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1.5 rounded">Default</button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
