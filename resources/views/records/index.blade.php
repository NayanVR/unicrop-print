<x-app-layout>
    <x-slot name="header">Billing & Records</x-slot>

    <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
        <div>
            <h2 style="font-family:'Bebas Neue',sans-serif;font-size:40px;letter-spacing:0.06em;color:#111;line-height:1;">Billing & Records</h2>
            <p style="font-size:13px;color:#717171;margin-top:4px;">Per-day, monthly summary with GST and full job list.</p>
        </div>
        <a href="{{ route('records.pdf', request()->query()) }}" target="_blank"
            style="display:inline-flex;align-items:center;gap:8px;background:#EF4444;color:#fff;font-size:13.5px;font-weight:600;padding:9px 18px;border-radius:9px;text-decoration:none;">
            <i class="fa-solid fa-file-pdf"></i> Download Statement PDF
        </a>
    </div>

    <form method="GET" action="{{ route('records.index') }}" class="filter-bar">
        <label class="font-bold text-sm">Filter:</label>
        <select name="month" onchange="this.form.submit()" 
            <option value="all" @selected($month === 'all')>All Months</option>
            @foreach (['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $value => $label)
                <option value="{{ $value }}" @selected($month === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" 
            <option value="all" @selected($year === 'all')>All Years</option>
            @foreach (['2024','2025','2026'] as $y)
                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" 
            <option value="all" @selected($status === 'all')>All Statuses</option>
            @foreach (\App\Enums\JobStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case->value)>{{ ucfirst($case->value) }}</option>
            @endforeach
        </select>
        <select name="station_id" onchange="this.form.submit()" 
            <option value="all" @selected($stationId === 'all')>All Stations</option>
            @foreach ($stations as $station)
                <option value="{{ $station->id }}" @selected($stationId === (string) $station->id)>{{ $station->name }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search note or file..." class="rounded border-slate-300 px-3 py-2 text-sm flex-1 min-w-[180px]">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <button type="submit" type="submit">Apply</button>
        @if ($status !== 'all' || $search !== '' || $month !== 'all' || $year !== (string) now()->year || $stationId !== 'all')
            <a href="{{ route('records.index') }}" class="text-xs text-slate-500 underline">Reset</a>
        @endif
    </form>

    {{-- GST Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">Total Jobs</p>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totalJobs) }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wide">Subtotal</p>
            <div class="text-2xl font-bold text-slate-700 mt-1">{{ number_format($totalRevenue, 2) }}</div>
            <p class="text-slate-400 text-xs">Rs (ex-GST)</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-xl p-4 text-center">
            <p class="text-amber-600 text-xs font-semibold uppercase tracking-wide">GST ({{ $gstRate }}%)</p>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($gst, 2) }}</div>
            <p class="text-slate-400 text-xs">Rs</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
            <p class="text-emerald-700 text-xs font-semibold uppercase tracking-wide">Grand Total</p>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($grandTotal, 2) }}</div>
            <p class="text-slate-400 text-xs">Rs (inc. GST)</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'summary' }">
        <div class="flex gap-1 bg-slate-100 p-1 rounded-lg mb-6 w-fit">
            <button @click="tab = 'summary'"
                :class="tab === 'summary' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition">
                <i class="fa-solid fa-chart-bar mr-1"></i> Summary
            </button>
            <button @click="tab = 'daily'"
                :class="tab === 'daily' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition">
                <i class="fa-solid fa-calendar-day mr-1"></i> Daily
            </button>
            <button @click="tab = 'jobs'"
                :class="tab === 'jobs' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                class="px-4 py-2 text-sm font-semibold rounded-md transition">
                <i class="fa-solid fa-list mr-1"></i> Job List
            </button>
        </div>

        {{-- Summary Tab --}}
        <div x-show="tab === 'summary'">
            @if ($monthlySummary->count() > 0)
                <div class="bg-white border border-slate-200 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-sky-500"></i> Monthly Breakdown
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Month</th>
                                    <th class="px-4 py-3 font-semibold text-right">Jobs</th>
                                    <th class="px-4 py-3 font-semibold text-right">Subtotal (Rs)</th>
                                    <th class="px-4 py-3 font-semibold text-right">GST {{ $gstRate }}% (Rs)</th>
                                    <th class="px-4 py-3 font-semibold text-right">Total incl. GST (Rs)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($monthlySummary as $row)
                                    @php $mg = round($row->subtotal * ($gstRate / 100), 2); @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold">{{ $row->month_label }}</td>
                                        <td class="px-4 py-3 text-right">{{ $row->job_count }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format($row->subtotal, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-amber-600">{{ number_format($mg, 2) }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($row->subtotal + $mg, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-300 font-bold">
                                <tr>
                                    <td class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-right">{{ $totalJobs }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format($totalRevenue, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-amber-600">{{ number_format($gst, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-600">{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl p-10 text-center text-slate-400">
                    <i class="fa-solid fa-chart-bar text-3xl mb-3 block"></i>
                    No completed jobs in the selected period.
                </div>
            @endif
        </div>

        {{-- Daily Tab --}}
        <div x-show="tab === 'daily'">
            @if ($dailySummary->count() > 0)
                <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px;">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-purple-500"></i> Daily Breakdown
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold text-right">Jobs</th>
                                    <th class="px-4 py-3 font-semibold text-right">Subtotal (Rs)</th>
                                    <th class="px-4 py-3 font-semibold text-right">GST {{ $gstRate }}% (Rs)</th>
                                    <th class="px-4 py-3 font-semibold text-right">Total incl. GST (Rs)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($dailySummary as $row)
                                    @php
                                        $dg = round($row->subtotal * ($gstRate / 100), 2);
                                        $dayLabel = \Carbon\Carbon::parse($row->day);
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-semibold">
                                            {{ $dayLabel->format('d M Y') }}
                                            @if ($dayLabel->isToday())
                                                <span class="ml-2 text-[10px] bg-emerald-500 text-white px-1.5 py-0.5 rounded">Today</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">{{ $row->job_count }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format($row->subtotal, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-amber-600">{{ number_format($dg, 2) }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($row->subtotal + $dg, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-300 font-bold">
                                <tr>
                                    <td class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-right">{{ $totalJobs }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format($totalRevenue, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-amber-600">{{ number_format($gst, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-600">{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl p-10 text-center text-slate-400">
                    <i class="fa-solid fa-calendar-day text-3xl mb-3 block"></i>
                    No completed jobs in the selected period.
                </div>
            @endif
        </div>

        {{-- Job List Tab --}}
        <div x-show="tab === 'jobs'" x-data="{ previewUrl: '', previewMime: '', previewName: '', previewOpen: false }">

            <dialog x-bind:open="previewOpen" @click.self="previewOpen = false"
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

            <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px;">
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Job ID</th>
                                <th class="px-4 py-3 font-semibold">Preview</th>
                                <th class="px-4 py-3 font-semibold">Station</th>
                                <th class="px-4 py-3 font-semibold">Note / File</th>
                                <th class="px-4 py-3 font-semibold">Print</th>
                                <th class="px-4 py-3 font-semibold">Cut</th>
                                <th class="px-4 py-3 font-semibold">Subtotal</th>
                                <th class="px-4 py-3 font-semibold">GST {{ $gstRate }}%</th>
                                <th class="px-4 py-3 font-semibold">Total</th>
                                <th class="px-4 py-3 font-semibold">Dispatched</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($jobs as $job)
                                @php $jobGst = round($job->total_amount * ($gstRate / 100), 2); @endphp
                                <tr>
                                    <td class="px-4 py-3">#{{ $job->id }}</td>
                                    <td class="px-4 py-3">
                                        @if ($job->fileUrl())
                                            @if (str_contains($job->mime_type ?? '', 'pdf'))
                                                <button type="button"
                                                    @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                                    class="flex items-center justify-center w-14 h-14 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 transition text-red-500 flex-col gap-1">
                                                    <i class="fa-solid fa-file-pdf text-xl"></i>
                                                    <span class="text-[9px] font-semibold">PDF</span>
                                                </button>
                                            @else
                                                <button type="button"
                                                    @click="previewUrl = '{{ $job->fileUrl() }}'; previewMime = '{{ $job->mime_type }}'; previewName = '{{ addslashes($job->file_name) }}'; previewOpen = true"
                                                    class="block w-14 h-14 rounded-lg border border-slate-200 overflow-hidden hover:ring-2 hover:ring-purple-400 transition">
                                                    <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}" class="w-full h-full object-cover" loading="lazy">
                                                </button>
                                            @endif
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-300">
                                                <i class="fa-solid fa-image text-xl"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">{{ $job->printStation?->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <strong>{{ $job->note ?: '—' }}</strong><br>
                                        <span class="text-xs text-slate-400">{{ $job->file_name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-sky-600 whitespace-nowrap">
                                        {{ $job->sheets }} × {{ $job->rate }}<br>
                                        <span class="font-bold">= {{ $job->print_total }} Rs</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-purple-600 whitespace-nowrap">
                                        @if ($job->cutting_jobs)
                                            {{ $job->cutting_jobs }} × {{ $job->cutting_rate }}<br>
                                            <span class="font-bold">= {{ $job->cutting_total }} Rs</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-700 font-semibold">
                                        {{ number_format($job->total_amount, 2) }} Rs
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-amber-600 font-semibold">
                                        {{ number_format($jobGst, 2) }} Rs
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-emerald-600">
                                        {{ number_format($job->total_amount + $jobGst, 2) }} Rs
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $job->dispatched_at?->format('d/m/Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($job->status->value === 'completed')
                                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">Done</span>
                                        @elseif ($job->status->value === 'dispatch')
                                            <span class="text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">Dispatch</span>
                                        @else
                                            <span class="text-xs font-bold text-orange-500 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded">{{ ucfirst($job->status->value) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $jobs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
