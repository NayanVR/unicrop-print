<x-app-layout>
    <x-slot name="header">Billing & Timeline Records</x-slot>

    <h2 class="text-2xl font-bold text-slate-900 mb-6">Billing & Timeline Records</h2>

    <form method="GET" action="{{ route('records.index') }}" class="flex flex-wrap gap-4 items-center bg-slate-100 p-4 rounded-lg mb-6">
        <label class="font-bold text-sm">Filter By:</label>
        <select name="month" onchange="this.form.submit()" class="rounded border-slate-300 px-3 py-2 text-sm">
            <option value="all" @selected($month === 'all')>All Months</option>
            @foreach (['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $value => $label)
                <option value="{{ $value }}" @selected($month === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" class="rounded border-slate-300 px-3 py-2 text-sm">
            <option value="all" @selected($year === 'all')>All Years</option>
            @foreach (['2024','2025','2026'] as $y)
                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
            @endforeach
        </select>
    </form>

    <div class="grid grid-cols-2 gap-5 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 font-bold text-sm">Total Jobs Completed</p>
            <div class="text-2xl font-bold text-blue-600 mt-2">{{ $totalJobs }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 font-bold text-sm">Total Revenue (Rs)</p>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($totalRevenue, 2) }} Rs</div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Job ID</th>
                        <th class="px-4 py-3 font-semibold">File Options</th>
                        <th class="px-4 py-3 font-semibold">Print & Cut Details</th>
                        <th class="px-4 py-3 font-semibold">Total Amount</th>
                        <th class="px-4 py-3 font-semibold">Timestamps</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
                            <td class="px-4 py-3">
                                @if ($job->fileUrl())
                                    <a href="{{ $job->fileUrl() }}" target="_blank" class="inline-flex items-center gap-1 mb-1 bg-purple-500 text-white text-xs px-3 py-1 rounded">
                                        <i class="fa-solid fa-print"></i> View File
                                    </a><br>
                                @endif
                                <strong>Note:</strong> {{ $job->note }}<br>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sky-600 text-xs">Print: {{ $job->sheets }} x {{ $job->rate }} = {{ $job->print_total }} Rs</span><br>
                                <span class="text-purple-600 text-xs">Cut: {{ $job->cutting_jobs }} x {{ $job->cutting_rate }} = {{ $job->cutting_total }} Rs</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600">
                                {{ $job->status->value === 'completed' ? number_format($job->total_amount, 2).' Rs' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 min-w-[160px]">
                                <div><i class="fa-solid fa-arrow-up text-sky-500"></i> Up: {{ $job->created_at->format('d/m/Y h:i A') }}</div>
                                <div class="mt-1"><i class="fa-solid fa-print text-orange-500"></i> Pr: {{ $job->printed_at?->format('d/m/Y h:i A') ?? '-' }}</div>
                                <div class="mt-1"><i class="fa-solid fa-scissors text-emerald-500"></i> Ct: {{ $job->cut_at?->format('d/m/Y h:i A') ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($job->status->value === 'completed')
                                    <span class="text-emerald-600 text-xs font-bold">Done</span>
                                @else
                                    <span class="text-orange-500 text-xs font-bold">Processing</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No records found for selected filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
