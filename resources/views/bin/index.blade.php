<x-app-layout>
    <x-slot name="header">Bin</x-slot>

    <div class="mb-6 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-trash-can text-red-400"></i> Bin
                @if ($jobs->isNotEmpty())
                    <span class="bg-red-100 text-red-600 text-sm font-bold px-2.5 py-0.5 rounded-full">{{ $jobs->count() }}</span>
                @endif
            </h2>
            <p style="font-size:13px;color:#717171;margin-top:4px;">Deleted jobs — restore them or let them auto-purge.</p>
        </div>

        {{-- Auto-delete setting --}}
        <div class="flex items-center gap-3 flex-wrap">
            <form method="POST" action="{{ route('bin.days') }}" class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2">
                @csrf @method('PATCH')
                <i class="fa-solid fa-clock text-slate-400 text-sm"></i>
                <label class="text-sm text-slate-600 font-medium">Auto-delete after</label>
                <input type="number" name="bin_days" value="{{ $binDays }}" min="1" max="365"
                    class="w-16 rounded-lg border-slate-300 px-2 py-1 text-sm text-center font-bold">
                <span class="text-sm text-slate-600">days</span>
                <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Save</button>
            </form>

            @if ($jobs->isNotEmpty())
                <form method="POST" action="{{ route('bin.purge') }}"
                    onsubmit="return confirm('Purge all jobs older than {{ $binDays }} days? This cannot be undone.')">
                    @csrf
                    <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-fire"></i> Purge expired ({{ $jobs->where('deleted_at', '<=', now()->subDays($binDays))->count() }} jobs)
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    @if ($jobs->isEmpty())
        <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center py-20 text-center text-slate-400">
            <i class="fa-solid fa-trash-can text-5xl mb-4 text-slate-300"></i>
            <p class="font-semibold text-slate-500">Bin is empty</p>
            <p class="text-sm mt-1">Deleted jobs will appear here.</p>
        </div>
    @else
        @php
            $statusConfig = [
                'pending'   => ['label' => 'Pending',   'class' => 'bg-amber-100 text-amber-700'],
                'cutting'   => ['label' => 'Cutting',   'class' => 'bg-purple-100 text-purple-700'],
                'dispatch'  => ['label' => 'Dispatch',  'class' => 'bg-sky-100 text-sky-700'],
                'completed' => ['label' => 'Completed', 'class' => 'bg-emerald-100 text-emerald-700'],
            ];
        @endphp

        <div class="space-y-3">
            @foreach ($jobs as $job)
                @php
                    $st = $statusConfig[$job->status->value] ?? ['label' => $job->status->value, 'class' => 'bg-slate-100 text-slate-500'];
                    $expired = $job->deleted_at->lte(now()->subDays($binDays));
                    $daysLeft = max(0, $binDays - (int) $job->deleted_at->diffInDays(now()));
                @endphp
                <div class="bg-white border rounded-xl px-4 py-3 flex items-center gap-4 {{ $expired ? 'border-red-200 bg-red-50/30' : 'border-slate-200' }}">
                    {{-- Thumbnail --}}
                    @if ($job->fileUrl())
                        @if (str_contains($job->mime_type ?? '', 'pdf'))
                            <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-red-50 border border-red-200 flex items-center justify-center text-red-400">
                                <i class="fa-solid fa-file-pdf text-xl"></i>
                            </div>
                        @elseif (str_contains($job->mime_type ?? '', 'image'))
                            <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}"
                                class="w-12 h-12 flex-shrink-0 rounded-lg object-cover border border-slate-200">
                        @else
                            <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                <i class="fa-solid fa-file text-xl"></i>
                            </div>
                        @endif
                    @else
                        <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300">
                            <i class="fa-solid fa-image text-xl"></i>
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-0.5">
                            <span class="font-bold text-slate-700 text-sm">#{{ $job->id }}</span>
                            <span class="text-xs {{ $st['class'] }} px-2 py-0.5 rounded-full font-semibold">{{ $st['label'] }}</span>
                            <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">{{ $job->printStation?->name ?? '—' }}</span>
                            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded">{{ $job->size?->name ?? '—' }}</span>
                        </div>
                        <div class="text-xs text-slate-500 truncate">{{ $job->file_name }} &middot; {{ $job->uploader?->name ?? '—' }}</div>
                        <div class="flex items-center gap-3 mt-0.5">
                            <span class="text-xs text-slate-400">Deleted {{ $job->deleted_at->diffForHumans() }}</span>
                            @if ($expired)
                                <span class="text-xs font-bold text-red-500 flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Expired — will be purged
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Auto-delete in <span class="font-semibold text-slate-600">{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</span></span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Restore --}}
                        <form method="POST" action="{{ route('bin.restore', $job) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-rotate-left"></i> Restore
                            </button>
                        </form>
                        {{-- Permanent delete --}}
                        <form method="POST" action="{{ route('bin.destroy', $job) }}"
                            onsubmit="return confirm('Permanently delete Job #{{ $job->id }}? The file will be deleted too. This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
