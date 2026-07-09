<x-app-layout>
    <x-slot name="header">Label Size Checker</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Label Size Checker</h2>
        <p class="text-slate-500 text-sm mt-1">Upload a label image to find which bottle sizes it matches.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upload form --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6 self-start">
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
                        Select Label Image <span class="text-red-500">*</span>
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
                    DPI is read from the file's metadata. If not found, 300 DPI is assumed (standard print resolution).
                    Tolerance: ±2 mm.
                </p>
                <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg py-2.5 flex items-center justify-center gap-2 transition">
                    <i class="fa-solid fa-magnifying-glass"></i> Check Label Size
                </button>
            </form>
        </div>

        {{-- Results --}}
        @if (isset($result))
            <div class="space-y-4">

                {{-- Detected dimensions --}}
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-ruler-combined text-slate-500"></i> Detected Dimensions
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <div class="text-xs text-slate-400 mb-1">File</div>
                            <div class="font-semibold text-slate-700 truncate">{{ $result['filename'] }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <div class="text-xs text-slate-400 mb-1">Resolution (DPI)</div>
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
                                            Label size: {{ $bw }} × {{ $bh }} mm
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
                                no bottle is configured with a label this size (±2 mm).
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Bottle sizes reference (shown when no result yet) --}}
            <div class="bg-white border border-slate-200 rounded-xl p-6 self-start">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bottle-water text-slate-400"></i> Configured Bottle Sizes
                    <span class="bg-slate-100 text-slate-500 text-xs font-bold px-2 py-0.5 rounded-full">{{ $bottleSizes->count() }}</span>
                </h3>
                @if ($bottleSizes->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($bottleSizes as $bottle)
                            <div class="flex items-center justify-between border border-slate-100 bg-slate-50 rounded-lg px-3 py-2.5">
                                <span class="font-medium text-sm">{{ $bottle->name }}</span>
                                <span class="text-xs text-slate-500 bg-white border border-slate-200 px-2 py-1 rounded">
                                    {{ $bottle->label_width_mm }} × {{ $bottle->label_height_mm }} mm
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm text-center py-6">
                        No bottle sizes configured yet.
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('settings.index') }}" class="text-teal-600 underline">Add them in Settings.</a>
                        @else
                            Ask your admin to add bottle sizes in Settings.
                        @endif
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- After result: also show all bottles for reference --}}
    @if (isset($result) && $bottleSizes->isNotEmpty())
        <div class="mt-6 bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list text-slate-400"></i> All Bottle Sizes Reference
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach ($bottleSizes as $bottle)
                    @php
                        $bw = (float) $bottle->label_width_mm;
                        $bh = (float) $bottle->label_height_mm;
                        $isMatch = $result['matches']->contains('id', $bottle->id);
                    @endphp
                    <div class="border rounded-lg px-3 py-2.5 text-sm {{ $isMatch ? 'border-emerald-300 bg-emerald-50' : 'border-slate-100 bg-slate-50' }}">
                        <div class="font-semibold {{ $isMatch ? 'text-emerald-700' : 'text-slate-700' }} flex items-center gap-1">
                            @if ($isMatch) <i class="fa-solid fa-check text-emerald-500 text-xs"></i> @endif
                            {{ $bottle->name }}
                        </div>
                        <div class="text-xs {{ $isMatch ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $bw }} × {{ $bh }} mm
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-app-layout>
