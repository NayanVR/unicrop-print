<li x-data="{ editing: false }" class="border border-slate-100 bg-slate-50 rounded-lg px-3 py-2">
    <div x-show="!editing" class="flex items-center justify-between">
        <div>
            <span class="font-medium text-sm">{{ $bottle->name }}</span>
            <span class="ml-1.5 text-xs text-slate-400">{{ $bottle->label_width_mm }} × {{ $bottle->label_height_mm }} mm</span>
        </div>
        @if (auth()->user()->hasPermission('label_checker'))
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
        @endif
    </div>
    @if (auth()->user()->hasPermission('label_checker'))
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
            <select name="group_id" class="w-full rounded border-slate-300 px-2 py-1 text-sm text-slate-600">
                <option value="">— No group —</option>
                @foreach ($groups as $g)
                    <option value="{{ $g->id }}" @selected($bottle->group_id == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-1.5">
                <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-1.5 rounded">Save</button>
                <button type="button" @click="editing = false" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs font-semibold py-1.5 rounded">Cancel</button>
            </div>
        </form>
    @endif
</li>
