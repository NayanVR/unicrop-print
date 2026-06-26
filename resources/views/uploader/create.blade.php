<x-app-layout>
    <x-slot name="header">Upload Design & File</x-slot>

    <h2 class="text-2xl font-bold text-slate-900 mb-6">Upload Design & File</h2>

    <div class="bg-white border border-slate-200 rounded-xl p-6 max-w-xl">
        <form method="POST" action="{{ route('uploader.store') }}" enctype="multipart/form-data" x-data="{
            sizes: {{ $sizes->mapWithKeys(fn ($s) => [$s->id => (float) $s->rate])->toJson() }},
            sizeId: '{{ $sizes->firstWhere('is_default', true)?->id ?? $sizes->first()?->id }}',
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
                <select name="print_station_id" required class="w-full rounded-lg border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
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
                <i class="fa-solid fa-tags"></i> Size Rate: <span x-text="sizes[sizeId]"></span> Rs / sheet
            </div>

            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg py-3 flex items-center justify-center gap-2">
                Upload & Send to Print <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>
</x-app-layout>
