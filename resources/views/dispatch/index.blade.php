<x-app-layout>
    <x-slot name="header">Dispatch</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Dispatch</h2>
            <p class="text-slate-500 text-sm mt-1">Select jobs ready to dispatch and mark them as completed.</p>
        </div>
        <form method="GET" action="{{ route('dispatch.index') }}" class="flex items-center gap-2">
            <label class="text-sm font-semibold text-slate-600">Date:</label>
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                class="rounded border-slate-300 px-3 py-2 text-sm">
        </form>
    </div>

    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif

    <div x-data="{
        selected: [],
        toggleAll(jobs) {
            if (this.selected.length === jobs.length) {
                this.selected = [];
            } else {
                this.selected = jobs.map(j => j);
            }
        }
    }">

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
                        <label :class="selected.includes({{ $job->id }}) ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-slate-200'"
                            class="flex items-center gap-4 border rounded-xl px-4 py-3 cursor-pointer transition hover:border-emerald-300">
                            <input type="checkbox" :value="{{ $job->id }}" x-model="selected"
                                class="w-4 h-4 accent-emerald-500 flex-shrink-0">
                            <div class="flex-1 min-w-0 grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1 text-sm">
                                <div>
                                    <span class="text-xs text-slate-400 block">Job ID</span>
                                    <span class="font-bold">#{{ $job->id }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block">Note</span>
                                    <span class="font-medium truncate block">{{ $job->note ?: '—' }}</span>
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
                            <div class="text-right text-xs text-slate-400 flex-shrink-0">
                                {{ $job->updated_at->format('h:i A') }}
                            </div>
                        </label>
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
                                <th class="px-4 py-3 font-semibold">Job ID</th>
                                <th class="px-4 py-3 font-semibold">Note</th>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                <th class="px-4 py-3 font-semibold">Amount</th>
                                <th class="px-4 py-3 font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($otherJobs as $job)
                                <tr>
                                    <td class="px-4 py-3">#{{ $job->id }}</td>
                                    <td class="px-4 py-3">{{ $job->note ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">{{ $job->printStation?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-emerald-600">{{ $job->total_amount }} Rs</td>
                                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $job->updated_at->format('d/m/Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
