<x-app-layout>
    <x-slot name="header">Label Size Checker</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Label Size Checker</h2>
        <p class="text-slate-500 text-sm mt-1">Upload a label image to find which bottle sizes it matches.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: Upload + Bottle management --}}
        <div class="space-y-6">

            {{-- Upload form --}}
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-tag text-teal-500"></i> Upload Label
                </h3>

                @if (isset($error))
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                        <i class="fa-solid fa-circle-xmark"></i> {{ $error }}
                    </div>
                @endif

                <form method="POST" action="{{ route('label-checker.check') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Label Image <span class="text-red-500">*</span>
                            <span class="font-normal text-slate-400">(PNG or JPG)</span>
                        </label>
                        <input type="file" name="label_file" accept=".jpg,.jpeg,.png" required
                            class="w-full text-sm text-slate-600 border border-dashed border-slate-400 rounded-lg bg-slate-50 p-3 cursor-pointer">
                        @error('label_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="text-xs text-slate-400 mb-4">
                        <i class="fa-solid fa-circle-info"></i>
                        DPI read from file metadata; defaults to 300 DPI. Tolerance: ±2 mm.
                    </p>
                    <button type="submit"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg py-2.5 flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-magnifying-glass"></i> Check Label Size
                    </button>
                </form>
            </div>

            {{-- Bottle size management --}}
            <div class="bg-white border border-teal-200 border-t-4 border-t-teal-500 rounded-xl p-6">
                <h3 class="font-semibold text-slate-800 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-bottle-water text-teal-500"></i> Bottle Sizes
                    <span class="bg-teal-100 text-teal-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $bottleSizes->count() }}</span>
                </h3>
                <p class="text-xs text-slate-400 mb-4">Add bottle names and their label dimensions.</p>

                <form method="POST" action="{{ route('settings.bottle-sizes.store') }}" class="mb-4 space-y-2">
                    @csrf
                    <input type="text" name="name" placeholder="Bottle name (e.g. 100ml Round)"
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                    <div class="flex gap-2">
                        <input type="number" step="0.1" min="1" name="label_width_mm" placeholder="Width mm"
                            class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                        <input type="number" step="0.1" min="1" name="label_height_mm" placeholder="Height mm"
                            class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <button type="submit"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">
                        <i class="fa-solid fa-plus"></i> Add Bottle Size
                    </button>
                </form>

                @if ($bottleSizes->isNotEmpty())
                    <ul class="space-y-1.5">
                        @foreach ($bottleSizes as $bottle)
                            <li x-data="{ editing: false }" class="border border-slate-100 bg-slate-50 rounded-lg px-3 py-2">
                                {{-- View mode --}}
                                <div x-show="!editing" class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-sm">{{ $bottle->name }}</span>
                                        <span class="ml-1.5 text-xs text-slate-400">{{ $bottle->label_width_mm }} × {{ $bottle->label_height_mm }} mm</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="editing = true"
                                            class="text-sky-400 hover:text-sky-600 hover:bg-sky-50 p-1.5 rounded transition">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <form method="POST" action="{{ route('settings.bottle-sizes.destroy', $bottle) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($bottle->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                {{-- Edit mode --}}
                                <form x-show="editing" method="POST" action="{{ route('settings.bottle-sizes.update', $bottle) }}" class="space-y-1.5">
                                    @csrf @method('PATCH')
                                    <input type="text" name="name" value="{{ $bottle->name }}"
                                        class="w-full rounded border-slate-300 px-2 py-1 text-sm">
                                    <div class="flex gap-1.5">
                                        <input type="number" step="0.1" min="1" name="label_width_mm" value="{{ $bottle->label_width_mm }}"
                                            placeholder="W mm" class="w-full rounded border-slate-300 px-2 py-1 text-sm">
                                        <input type="number" step="0.1" min="1" name="label_height_mm" value="{{ $bottle->label_height_mm }}"
                                            placeholder="H mm" class="w-full rounded border-slate-300 px-2 py-1 text-sm">
                                    </div>
                                    <div class="flex gap-1.5">
                                        <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-1.5 rounded">Save</button>
                                        <button type="button" @click="editing = false" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs font-semibold py-1.5 rounded">Cancel</button>
                                    </div>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-slate-400 text-sm text-center py-4">No bottle sizes added yet.</p>
                @endif
            </div>
        </div>

        {{-- Right columns: Results --}}
        <div class="lg:col-span-2 space-y-4">
            @if (isset($result))
                {{-- Detected dimensions --}}
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-ruler-combined text-slate-500"></i> Detected Dimensions
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div class="bg-slate-50 rounded-lg p-3 text-center col-span-2 sm:col-span-1">
                            <div class="text-xs text-slate-400 mb-1">File</div>
                            <div class="font-semibold text-slate-700 truncate text-xs">{{ $result['filename'] }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <div class="text-xs text-slate-400 mb-1">DPI</div>
                            <div class="font-bold text-slate-800 text-lg">{{ $result['dpi'] }}</div>
                        </div>
                        <div class="bg-teal-50 border border-teal-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-teal-600 mb-1">Width</div>
                            <div class="font-bold text-teal-700 text-xl">{{ $result['widthMm'] }} mm</div>
                            <div class="text-xs text-slate-400">{{ $result['pixelW'] }} px</div>
                        </div>
                        <div class="bg-teal-50 border border-teal-200 rounded-lg p-3 text-center">
                            <div class="text-xs text-teal-600 mb-1">Height</div>
                            <div class="font-bold text-teal-700 text-xl">{{ $result['heightMm'] }} mm</div>
                            <div class="text-xs text-slate-400">{{ $result['pixelH'] }} px</div>
                        </div>
                    </div>
                </div>

                {{-- Matching bottles --}}
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-bottle-water text-teal-500"></i> Matching Bottles
                    </h3>
                    @if ($result['matches']->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($result['matches'] as $bottle)
                                @php
                                    $bw = (float) $bottle->label_width_mm;
                                    $bh = (float) $bottle->label_height_mm;
                                    $rotated = !(abs($result['widthMm'] - $bw) <= 2 && abs($result['heightMm'] - $bh) <= 2);
                                @endphp
                                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-300 rounded-xl px-4 py-3">
                                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-check text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-emerald-800">{{ $bottle->name }}</div>
                                        <div class="text-xs text-emerald-600">
                                            Label: {{ $bw }} × {{ $bh }} mm
                                            @if ($rotated)
                                                <span class="ml-2 bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded text-[10px] font-semibold">ROTATED FIT</span>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-2xl flex-shrink-0"></i>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center py-8 text-center text-slate-400">
                            <i class="fa-solid fa-circle-xmark text-4xl mb-3 text-red-300"></i>
                            <p class="font-semibold text-slate-600">No matching bottle sizes found</p>
                            <p class="text-sm mt-1">
                                Label is {{ $result['widthMm'] }} × {{ $result['heightMm'] }} mm —
                                no bottle configured with this label size (±2 mm).
                            </p>
                        </div>
                    @endif
                </div>

                {{-- All bottles reference --}}
                @if ($bottleSizes->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-xl p-6">
                        <h3 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-list text-slate-400"></i> All Bottle Sizes
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($bottleSizes as $bottle)
                                @php $isMatch = $result['matches']->contains('id', $bottle->id); @endphp
                                <div class="border rounded-lg px-3 py-2.5 text-sm {{ $isMatch ? 'border-emerald-300 bg-emerald-50' : 'border-slate-100 bg-slate-50' }}">
                                    <div class="font-semibold {{ $isMatch ? 'text-emerald-700' : 'text-slate-700' }} flex items-center gap-1">
                                        @if ($isMatch) <i class="fa-solid fa-check text-emerald-500 text-xs"></i> @endif
                                        {{ $bottle->name }}
                                    </div>
                                    <div class="text-xs {{ $isMatch ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $bottle->label_width_mm }} × {{ $bottle->label_height_mm }} mm
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center py-20 text-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-5xl mb-4 text-slate-300"></i>
                    <p class="font-semibold text-slate-500">Upload a label to see results</p>
                    <p class="text-sm mt-1">The matched bottle sizes will appear here.</p>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
