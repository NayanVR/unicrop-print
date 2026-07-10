<x-app-layout>
    <x-slot name="header">Dispatch</x-slot>

    <div class="mb-4 flex items-start justify-between flex-wrap gap-3">
        <div>
            <h2 style="font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:0.06em;color:#111;line-height:1;">Dispatch</h2>
            <p style="font-size:13px;color:#717171;margin-top:4px;">Select jobs ready to dispatch and mark them as completed.</p>
        </div>
        <form method="GET" action="{{ route('dispatch.index') }}" class="flex items-center gap-2">
            <label class="text-sm font-semibold text-slate-600">Date:</label>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                class="rounded border-slate-300 px-3 py-2 text-sm">
        </form>
    </div>

    {{-- Pending dispatch summary --}}
    @if ($pendingTotal > 0)
        <div class="mb-6 bg-white border border-sky-200 border-t-4 border-t-sky-500 rounded-xl p-4" x-data="{ open: false }">
            <div class="flex items-center justify-between gap-3 cursor-pointer" @click="open = !open">
                <div class="flex items-center gap-3">
                    <div class="bg-sky-100 text-sky-700 rounded-xl px-4 py-2 flex items-center gap-2 font-bold text-lg">
                        <i class="fa-solid fa-truck-fast"></i>
                        {{ $pendingTotal }} jobs pending dispatch
                    </div>
                </div>
                <button type="button" class="text-xs text-sky-500 font-semibold flex items-center gap-1">
                    Date-wise breakdown
                    <i class="fa-solid fa-chevron-down transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <div x-show="open" x-collapse class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach ($pendingByDate as $row)
                    <a href="{{ route('dispatch.index', ['date' => $row->day]) }}"
                        class="flex items-center justify-between bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded-lg px-3 py-2 transition group">
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-sky-800">
                            {{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}
                        </span>
                        <span class="bg-sky-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $row->total }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2 text-emerald-700 text-sm font-semibold">
            <i class="fa-solid fa-circle-check"></i> All caught up — no pending dispatches!
        </div>
    @endif

    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    <div x-data="{
        selected: [],
        previewUrl: '', previewMime: '', previewName: '', previewOpen: false,
        toggleAll(jobs) {
            if (this.selected.length === jobs.length) {
                this.selected = [];
            } else {
                this.selected = jobs.map(j => j);
            }
        }
    }">

        {{-- File preview modal --}}
        <dialog x-ref="previewDlg"
            x-effect="previewOpen ? $refs.previewDlg.showModal() : ($refs.previewDlg.open && $refs.previewDlg.close())"
            @click.self="previewOpen = false" @cancel.prevent="previewOpen = false"
            class="fixed inset-0 z-50 w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl p-0 border-0 backdrop:bg-black/60 overflow-hidden">
            <div class="flex items-center justify-between bg-slate-800 text-white px-5 py-3">
                <span class="text-sm font-semibold truncate" x-text="previewName"></span>
                <button @click="previewOpen = false" class="text-white/70 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <div class="bg-black flex items-center justify-center" style="height: calc(90vh - 52px)">
                <template x-if="previewMime === 'application/pdf'">
                    <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                </template>
                <template x-if="previewMime !== 'application/pdf'">
                    <img :src="previewUrl" :alt="previewName" class="max-w-full max-h-full object-contain">
                </template>
            </div>
        </dialog>

        {{-- Today's jobs --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full inline-block"></span>
                    {{ \Carbon\Carbon::parse($date)->isToday() ? "Today's Jobs" : \Carbon\Carbon::parse($date)->format('d M Y') }}
                    <span class="text-slate-400 font-normal text-sm">({{ $todayJobs->count() }} jobs)</span>
                </h3>
                @if ($todayJobs->count() > 0)
                    <button type="button"
                        @click="toggleAll({{ $todayJobs->pluck('id') }})"
                        class="text-sm font-semibold px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-600">
                        <span x-text="selected.length === {{ $todayJobs->count() }} ? 'Deselect All' : 'Select All'">Select All</span>
                    </button>
                @endif
            </div>

            @if ($todayJobs->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach ($todayJobs as $job)
                        <div x-data="{ editNote: false }"
                            :class="selected.includes({{ $job->id }}) ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-slate-200'"
                            class="flex items-center gap-3 border rounded-xl px-4 py-3 transition hover:border-emerald-300">
                            <input type="checkbox" :value="{{ $job->id }}" x-model="selected"
                                class="w-4 h-4 accent-emerald-500 flex-shrink-0 cursor-pointer">

                            {{-- Thumbnail --}}
                            @if ($job->fileUrl())
                                @if (str_contains($job->mime_type ?? '', 'pdf'))
                                    <button type="button"
                                        @click.stop="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                        class="flex-shrink-0 flex items-center justify-center w-14 h-14 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 transition text-red-500 flex-col gap-0.5">
                                        <i class="fa-solid fa-file-pdf text-xl"></i>
                                        <span class="text-[9px] font-semibold">PDF</span>
                                    </button>
                                @elseif (str_starts_with($job->mime_type ?? '', 'image/'))
                                    <button type="button"
                                        @click.stop="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                        class="flex-shrink-0 block w-14 h-14 rounded-lg border border-slate-200 overflow-hidden hover:ring-2 hover:ring-emerald-400 transition">
                                        <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}" class="w-full h-full object-cover" loading="lazy">
                                    </button>
                                @else
                                    <div class="flex-shrink-0 w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 flex-col gap-0.5">
                                        <i class="fa-solid fa-file text-xl"></i>
                                        <span class="text-[9px] font-semibold uppercase">{{ pathinfo($job->file_name, PATHINFO_EXTENSION) }}</span>
                                    </div>
                                @endif
                            @else
                                <div class="flex-shrink-0 w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300">
                                    <i class="fa-solid fa-image text-xl"></i>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0 grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1 text-sm">
                                <div>
                                    <span class="text-xs text-slate-400 block">Job ID</span>
                                    <span class="font-bold">#{{ $job->id }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Note</span>
                                    <div x-show="!editNote" class="flex items-center gap-1">
                                        <span class="font-medium truncate">{{ $job->note ?: '—' }}</span>
                                        <button type="button" @click.stop="editNote = true" class="text-sky-400 hover:text-sky-600 text-xs flex-shrink-0"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </div>
                                    <form x-show="editNote" @click.stop method="POST" action="{{ route('jobs.note.update', $job) }}" class="flex gap-1">
                                        @csrf @method('PATCH')
                                        <input type="text" name="note" value="{{ $job->note === '-' ? '' : $job->note }}" placeholder="Note..." class="rounded border-slate-300 px-1.5 py-0.5 text-xs w-24">
                                        <button type="submit" class="bg-sky-500 text-white text-xs px-2 py-0.5 rounded font-semibold">Save</button>
                                        <button type="button" @click.stop="editNote = false" class="text-xs text-slate-400">✕</button>
                                    </form>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Station</span>
                                    <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">{{ $job->printStation?->name ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Amount</span>
                                    <span class="font-bold text-emerald-600">{{ $job->total_amount }} Rs</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="text-xs text-slate-400">{{ $job->updated_at->format('h:i A') }}</span>
                                @if (auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('jobs.destroy', $job) }}"
                                        onsubmit="return confirm('Delete Job #{{ $job->id }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs inline-flex items-center gap-1">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('dispatch.bulk') }}">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="job_ids[]" :value="id">
                    </template>
                    <button type="submit"
                        x-bind:disabled="selected.length === 0"
                        x-bind:class="selected.length > 0 ? 'bg-emerald-500 hover:bg-emerald-600 cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        class="text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-truck"></i>
                        Dispatch <span x-show="selected.length > 0">(<span x-text="selected.length"></span>)</span> Selected
                    </button>
                </form>
            @else
                <p class="text-slate-400 text-sm text-center py-6">No jobs ready for dispatch on this date.</p>
            @endif
        </div>

        {{-- Other pending dispatch jobs --}}
        @if ($otherJobs->count() > 0)
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-2 h-2 bg-amber-400 rounded-full inline-block"></span>
                    Other Pending Dispatch
                    <span class="text-slate-400 font-normal text-sm">({{ $otherJobs->count() }} jobs from previous dates)</span>
                </h3>
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Preview</th>
                                <th class="px-4 py-3 font-semibold">Job ID</th>
                                <th class="px-4 py-3 font-semibold">Note</th>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                <th class="px-4 py-3 font-semibold">Amount</th>
                                <th class="px-4 py-3 font-semibold">Date</th>
                                @if (auth()->user()->isAdmin())
                                    <th class="px-4 py-3 font-semibold"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($otherJobs as $job)
                                <tr x-data="{ editNote: false }">
                                    <td class="px-4 py-3">
                                        @if ($job->fileUrl())
                                            @if (str_contains($job->mime_type ?? '', 'pdf'))
                                                <button type="button"
                                                    @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                                    class="flex items-center justify-center w-14 h-14 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 transition text-red-500 flex-col gap-0.5">
                                                    <i class="fa-solid fa-file-pdf text-xl"></i>
                                                    <span class="text-[9px] font-semibold">PDF</span>
                                                </button>
                                            @elseif (str_starts_with($job->mime_type ?? '', 'image/'))
                                                <button type="button"
                                                    @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                                    class="block w-14 h-14 rounded-lg border border-slate-200 overflow-hidden hover:ring-2 hover:ring-emerald-400 transition">
                                                    <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}" class="w-full h-full object-cover" loading="lazy">
                                                </button>
                                            @else
                                                <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 flex-col gap-0.5">
                                                    <i class="fa-solid fa-file text-xl"></i>
                                                    <span class="text-[9px] font-semibold uppercase">{{ pathinfo($job->file_name, PATHINFO_EXTENSION) }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300">
                                                <i class="fa-solid fa-image text-xl"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold">#{{ $job->id }}</td>
                                    <td class="px-4 py-3">
                                        <div x-show="!editNote" class="flex items-center gap-1">
                                            <span>{{ $job->note ?: '—' }}</span>
                                            <button type="button" @click="editNote = true" class="text-sky-400 hover:text-sky-600 text-xs"><i class="fa-solid fa-pen-to-square"></i></button>
                                        </div>
                                        <form x-show="editNote" method="POST" action="{{ route('jobs.note.update', $job) }}" class="flex gap-1">
                                            @csrf @method('PATCH')
                                            <input type="text" name="note" value="{{ $job->note === '-' ? '' : $job->note }}" placeholder="Note..." class="rounded border-slate-300 px-1.5 py-0.5 text-xs w-28">
                                            <button type="submit" class="bg-sky-500 text-white text-xs px-2 py-0.5 rounded font-semibold">Save</button>
                                            <button type="button" @click="editNote = false" class="text-xs text-slate-400">✕</button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">{{ $job->printStation?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-emerald-600">{{ $job->total_amount }} Rs</td>
                                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $job->updated_at->format('d/m/Y h:i A') }}</td>
                                    @if (auth()->user()->isAdmin())
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('jobs.destroy', $job) }}"
                                                onsubmit="return confirm('Delete Job #{{ $job->id }}? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 hover:bg-red-50 text-xs px-2 py-1 rounded transition inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
