<x-app-layout>
    <x-slot name="header">Label Size Checker</x-slot>

    <div class="mb-6">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:0.06em;color:#111;line-height:1;">Label Size Checker</h2>
        <p style="font-size:13px;color:#717171;margin-top:4px;">Upload up to 50 label images at once to find which bottle sizes they match.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: Upload + Bottle management --}}
        <div class="space-y-6">

            {{-- Upload form --}}
            <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px;">
                <h3 style="font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:0.06em;color:#111;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-tag style="color:#F05A28;""></i> Upload Labels
                </h3>

                <form method="POST" action="{{ route('label-checker.check') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3" x-data="{ count: 0 }">
                        <label style="display:block;font-size:13px;font-weight:600;color:#1A1A1A;margin-bottom:6px;">
                            Select Label Images <span class="text-red-500">*</span>
                            <span class="font-normal text-slate-400">(PNG or JPG, up to 50)</span>
                        </label>
                        <input type="file" name="label_files[]" accept=".jpg,.jpeg,.png" multiple required
                            @change="count = $event.target.files.length"
                            style="width:100%;font-size:13px;color:#555;border:2px dashed #E5E5E5;border-radius:9px;background:#FAFAF8;padding:12px;cursor:pointer;">
                        <p x-show="count > 0" style="font-size:12px;color:#F05A28;margin-top:4px;font-weight:600;">
                            <span x-text="count"></span> file<span x-show="count > 1">s</span> selected
                        </p>
                        @error('label_files')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('label_files.*')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <p style="font-size:12px;color:#A0A0A0;margin-bottom:14px;">
                        <i class="fa-solid fa-circle-info"></i>
                        DPI read from file metadata; defaults to 300 DPI. Tolerance: ±2 mm.
                    </p>
                    <button type="submit"
                        style="width:100%;background:#F05A28;color:#fff;border:none;padding:10px;border-radius:9px;font-weight:600;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;">
                        <i class="fa-solid fa-magnifying-glass"></i> Check Label Sizes
                    </button>
                </form>
            </div>

            {{-- Bottle size management (admin only) --}}
            <div style="background:#fff;border:1.5px solid #E5E5E5;border-top:4px solid #F05A28;border-radius:14px;padding:22px;">
                <h3 style="font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:0.06em;color:#111;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-bottle-water style="color:#F05A28;""></i> Bottle Sizes
                    <span style="background:#FFF1EC;color:#F05A28;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">{{ $bottleSizes->count() }}</span>
                </h3>
                <p style="font-size:12px;color:#A0A0A0;margin-bottom:14px;">Add bottle names and their label dimensions. Organise into groups.</p>

                @if (auth()->user()->hasPermission('label_checker'))
                    {{-- Add bottle form --}}
                    <form method="POST" action="{{ route('settings.bottle-sizes.store') }}" class="mb-5 space-y-2">
                        @csrf
                        <input type="text" name="name" placeholder="Bottle name (e.g. 100ml Round)"
                            class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                        <div class="flex gap-2">
                            <input type="number" step="0.1" min="1" name="label_width_mm" placeholder="Width mm"
                                class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                            <input type="number" step="0.1" min="1" name="label_height_mm" placeholder="Height mm"
                                class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                        </div>
                        @if ($groups->isNotEmpty())
                            <select name="group_id" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm text-slate-600">
                                <option value="">— No group —</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit"
                            style="width:100%;background:#F05A28;color:#fff;border:none;padding:9px 16px;border-radius:9px;font-weight:600;font-size:13.5px;cursor:pointer;">
                            <i class="fa-solid fa-plus"></i> Add Bottle Size
                        </button>
                    </form>

                    {{-- Group management --}}
                    <div class="border-t border-slate-100 pt-4 mb-4">
                        <p style="font-size:10.5px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#A0A0A0;margin-bottom:8px;">Groups</p>
                        <form method="POST" action="{{ route('settings.bottle-size-groups.store') }}" class="flex gap-2 mb-3">
                            @csrf
                            <input type="text" name="name" placeholder="New group name"
                                class="flex-1 rounded-lg border-slate-300 px-3 py-1.5 text-sm">
                            <button type="submit" style="background:#111;color:#fff;border:none;padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </form>
                        @if ($groups->isNotEmpty())
                            <ul class="space-y-1">
                                @foreach ($groups as $group)
                                    <li x-data="{ editing: false }" class="flex items-center gap-1.5">
                                        <span x-show="!editing" style="flex:1;font-size:13px;color:#1A1A1A;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $group->name }}</span>
                                        <form x-show="editing" method="POST" action="{{ route('settings.bottle-size-groups.update', $group) }}" class="flex-1 flex gap-1">
                                            @csrf @method('PATCH')
                                            <input type="text" name="name" value="{{ $group->name }}"
                                                class="flex-1 rounded border-slate-300 px-2 py-1 text-sm">
                                            <button type="submit" style="background:#F05A28;color:#fff;border:none;padding:4px 10px;border-radius:6px;font-size:12px;cursor:pointer;">Save</button>
                                            <button type="button" @click="editing = false" style="background:#F0F0EE;color:#555;border:none;padding:4px 10px;border-radius:6px;font-size:12px;cursor:pointer;">✕</button>
                                        </form>
                                        <button x-show="!editing" type="button" @click="editing = true"
                                            class="text-sky-400 hover:text-sky-600 p-1 rounded transition">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <form x-show="!editing" method="POST" action="{{ route('settings.bottle-size-groups.destroy', $group) }}"
                                            onsubmit="return confirm('Delete group {{ addslashes($group->name) }}? Bottles in it will become ungrouped.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 p-1 rounded transition">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-slate-400">No groups yet.</p>
                        @endif
                    </div>
                @endif

                {{-- Bottles listed by group --}}
                @php
                    $ungrouped = $bottleSizes->whereNull('group_id');
                @endphp

                @if ($bottleSizes->isEmpty())
                    <p class="text-slate-400 text-sm text-center py-4">No bottle sizes added yet.</p>
                @else
                    <div x-data="{ active: null }">
                        {{-- Grouped bottles --}}
                        @foreach ($groups as $group)
                            @if ($group->bottleSizes->isNotEmpty())
                                <div class="mb-2">
                                    <button type="button"
                                        @click="active = (active === {{ $group->id }}) ? null : {{ $group->id }}"
                                        style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:4px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#717171;margin-bottom:4px;padding:6px 8px;border-radius:7px;cursor:pointer;border:none;background:none;" onmouseover="this.style.background='#F5F5F3'" onmouseout="this.style.background='none'">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-layer-group" style="color:#A0A0A0;"></i>
                                            {{ $group->name }}
                                            <span style="background:#F0F0EE;color:#555;font-size:10px;font-weight:700;padding:1px 6px;border-radius:999px;">{{ $group->bottleSizes->count() }}</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down" style="color:#A0A0A0;" class="transition-transform duration-200"
                                            :class="active === {{ $group->id }} ? 'rotate-180' : ''"></i>
                                    </button>
                                    <ul x-show="active === {{ $group->id }}" x-collapse class="space-y-1.5">
                                        @foreach ($group->bottleSizes as $bottle)
                                            @include('label-checker._bottle-item', ['bottle' => $bottle, 'groups' => $groups])
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach

                        {{-- Ungrouped bottles --}}
                        @if ($ungrouped->isNotEmpty())
                            <div class="mb-2">
                                @if ($groups->isNotEmpty())
                                    <button type="button"
                                        @click="active = (active === 0) ? null : 0"
                                        style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:4px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#717171;margin-bottom:4px;padding:6px 8px;border-radius:7px;cursor:pointer;border:none;background:none;" onmouseover="this.style.background='#F5F5F3'" onmouseout="this.style.background='none'">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-layer-group" style="color:#A0A0A0;"></i>
                                            Ungrouped
                                            <span style="background:#F0F0EE;color:#555;font-size:10px;font-weight:700;padding:1px 6px;border-radius:999px;">{{ $ungrouped->count() }}</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down" style="color:#A0A0A0;" class="transition-transform duration-200"
                                            :class="active === 0 ? 'rotate-180' : ''"></i>
                                    </button>
                                    <ul x-show="active === 0" x-collapse class="space-y-1.5">
                                @else
                                    <ul class="space-y-1.5">
                                @endif
                                    @foreach ($ungrouped as $bottle)
                                        @include('label-checker._bottle-item', ['bottle' => $bottle, 'groups' => $groups])
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Right columns: Results --}}
        <div class="lg:col-span-2">
            @if (isset($results))
                @php
                    $matched   = collect($results)->filter(fn($r) => !isset($r['error']) && $r['matches']->isNotEmpty());
                    $unmatched = collect($results)->filter(fn($r) => !isset($r['error']) && $r['matches']->isEmpty());
                    $errors    = collect($results)->filter(fn($r) => isset($r['error']));
                @endphp

                {{-- Summary bar --}}
                <div class="flex flex-wrap gap-3 mb-4">
                    <div style="background:#111;color:#fff;border-radius:10px;padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:13.5px;font-weight:600;">
                        <i class="fa-solid fa-images"></i> {{ count($results) }} label{{ count($results) > 1 ? 's' : '' }} checked
                    </div>
                    @if ($matched->count())
                        <div class="bg-emerald-100 text-emerald-700 rounded-xl px-4 py-2.5 flex items-center gap-2 text-sm font-semibold">
                            <i class="fa-solid fa-circle-check"></i> {{ $matched->count() }} matched
                        </div>
                    @endif
                    @if ($unmatched->count())
                        <div class="bg-red-100 text-red-600 rounded-xl px-4 py-2.5 flex items-center gap-2 text-sm font-semibold">
                            <i class="fa-solid fa-circle-xmark"></i> {{ $unmatched->count() }} no match
                        </div>
                    @endif
                    @if ($errors->count())
                        <div class="bg-amber-100 text-amber-700 rounded-xl px-4 py-2.5 flex items-center gap-2 text-sm font-semibold">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->count() }} error{{ $errors->count() > 1 ? 's' : '' }}
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach ($results as $r)
                        @if (isset($r['error']))
                            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                <div>
                                    <span class="font-semibold text-sm text-amber-800">{{ $r['filename'] }}</span>
                                    <span class="text-xs text-amber-600 ml-2">{{ $r['error'] }}</span>
                                </div>
                            </div>
                        @elseif ($r['matches']->isNotEmpty())
                            <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4">
                                <div class="flex items-start gap-3 flex-wrap">
                                    <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fa-solid fa-check text-white text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-emerald-800 text-sm truncate">{{ $r['filename'] }}</div>
                                        <div class="text-xs text-emerald-600 mt-0.5">
                                            {{ $r['widthMm'] }} × {{ $r['heightMm'] }} mm &nbsp;·&nbsp; {{ $r['dpi'] }} DPI &nbsp;·&nbsp; {{ $r['pixelW'] }}×{{ $r['pixelH'] }} px
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @foreach ($r['matches'] as $bottle)
                                                @php
                                                    $bw = (float) $bottle->label_width_mm;
                                                    $bh = (float) $bottle->label_height_mm;
                                                    $rotated = !(abs($r['widthMm'] - $bw) <= 2 && abs($r['heightMm'] - $bh) <= 2);
                                                @endphp
                                                <span class="inline-flex items-center gap-1 bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    <i class="fa-solid fa-bottle-water text-[10px]"></i>
                                                    {{ $bottle->name }}
                                                    @if ($rotated)
                                                        <span class="bg-white/20 text-white text-[9px] px-1 rounded">rotated</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-start gap-3">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-xmark text-red-400 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-700 text-sm truncate">{{ $r['filename'] }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ $r['widthMm'] }} × {{ $r['heightMm'] }} mm &nbsp;·&nbsp; {{ $r['dpi'] }} DPI &nbsp;·&nbsp; {{ $r['pixelW'] }}×{{ $r['pixelH'] }} px
                                    </div>
                                    <div class="text-xs text-red-500 mt-1">No matching bottle size (±2 mm)</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            @else
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center py-20 text-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-5xl mb-4 text-slate-300"></i>
                    <p class="font-semibold text-slate-500">Upload labels to see results</p>
                    <p class="text-sm mt-1">Select one or more PNG/JPG files and click Check.</p>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
