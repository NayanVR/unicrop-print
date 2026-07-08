<x-app-layout>
    <x-slot name="header">Upload Design & File</x-slot>

    <h2 class="text-2xl font-bold text-slate-900 mb-6">Upload Design & File</h2>

    <div class="bg-white border border-slate-200 rounded-xl p-6 max-w-xl">
        <form method="POST" action="{{ route('uploader.store') }}" enctype="multipart/form-data" x-data="{
            rates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->size_id => (float) $r->rate])])->toJson() }},
            cuttingRates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationCuttingRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->cutting_type_id => (float) $r->rate])])->toJson() }},
            laminationRates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationLaminationRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->lamination_type_id => (float) $r->rate])])->toJson() }},
            stationsRequireCutting: {{ $stations->mapWithKeys(fn ($st) => [$st->id => (bool) $st->requires_cutting])->toJson() }},
            stationId: '{{ $stations->firstWhere('is_default', true)?->id ?? $stations->first()?->id }}',
            sizeId: '{{ $sizes->firstWhere('is_default', true)?->id ?? $sizes->first()?->id }}',
            needsCutting: false,
            cuttingTypeId: '{{ $cuttingTypes->firstWhere('is_default', true)?->id ?? $cuttingTypes->first()?->id }}',
            needsLamination: null,
            laminationTypeId: '{{ $laminationTypes->firstWhere('is_default', true)?->id ?? $laminationTypes->first()?->id }}',
        }">
            @csrf

            <div class="mb-5 bg-slate-100 rounded-lg p-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-paperclip"></i> Select Design File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="design_file" accept=".jpg,.jpeg,.png,.pdf" required
                    class="w-full text-sm text-slate-600 border border-dashed border-slate-400 rounded-lg bg-slate-50 p-2 cursor-pointer">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Note (Optional)</label>
                <input type="text" name="note" placeholder="e.g., Urgent Print, Customer Name, etc..."
                    class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Print Station <span class="text-red-500">*</span></label>
                <select name="print_station_id" x-model="stationId" required class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected($station->is_default)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Select Size</label>
                <select name="size_id" x-model="sizeId" class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($sizes as $size)
                        <option value="{{ $size->id }}" @selected($size->is_default)>{{ $size->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Number of Copies / Sheets <span class="text-red-500">*</span></label>
                <input type="number" name="sheets" min="1" value="1" required
                    class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="mb-5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3">
                <i class="fa-solid fa-tags"></i> Size Rate: <span x-text="rates[stationId]?.[sizeId]"></span> Rs / sheet
            </div>

            <div class="mb-5" x-show="stationsRequireCutting[stationId]">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 mb-2">
                    <input type="checkbox" name="needs_cutting" value="1" x-model="needsCutting">
                    Needs Cutting?
                </label>

                <div x-show="needsCutting">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Cutting Type</label>
                    <select name="cutting_type_id" x-model="cuttingTypeId" class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($cuttingTypes as $type)
                            <option value="{{ $type->id }}" @selected($type->is_default)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-3 text-sm font-bold text-purple-700 bg-purple-50 border border-purple-200 rounded-lg px-4 py-3">
                        <i class="fa-solid fa-scissors"></i> Cutting Rate: <span x-text="cuttingRates[stationId]?.[cuttingTypeId]"></span> Rs / cut
                    </div>
                </div>
            </div>

            {{-- Lamination --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-3">
                    <i class="fa-solid fa-layer-group text-indigo-500"></i> Lamination Required? <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-3">
                    <button type="button" @click="needsLamination = false"
                        :class="needsLamination === false ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-300 hover:border-slate-400'"
                        class="flex-1 border-2 rounded-lg py-2.5 text-sm font-semibold transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark"></i> No Lamination
                    </button>
                    <button type="button" @click="needsLamination = true"
                        :class="needsLamination === true ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400'"
                        class="flex-1 border-2 rounded-lg py-2.5 text-sm font-semibold transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-layer-group"></i> Yes, Lamination
                    </button>
                </div>
                <input type="hidden" name="needs_lamination" :value="needsLamination === true ? '1' : '0'">

                <div x-show="needsLamination === true" x-transition class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Lamination Type</label>
                        <select name="lamination_type_id" x-model="laminationTypeId" class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($laminationTypes as $type)
                                <option value="{{ $type->id }}" @selected($type->is_default)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                        <i class="fa-solid fa-layer-group"></i> Lamination Rate: <span x-text="laminationRates[stationId]?.[laminationTypeId] ?? 0"></span> Rs / sheet
                    </div>
                </div>
            </div>

            <button type="submit"
                :disabled="needsLamination === null"
                :class="needsLamination !== null ? 'bg-emerald-500 hover:bg-emerald-600 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'"
                class="w-full text-white font-semibold rounded-lg py-3 flex items-center justify-center gap-2 transition">
                <span x-text="needsLamination === null ? 'Please select lamination option above' : 'Upload & Send to Print'"></span>
                <i class="fa-solid fa-paper-plane" x-show="needsLamination !== null"></i>
            </button>
        </form>
    </div>
</x-app-layout>
