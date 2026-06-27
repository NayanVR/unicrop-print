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
        <select name="status" onchange="this.form.submit()" class="rounded border-slate-300 px-3 py-2 text-sm">
            <option value="all" @selected($status === 'all')>All Statuses</option>
            @foreach (\App\Enums\JobStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case->value)>{{ ucfirst($case->value) }}</option>
            @endforeach
        </select>
        <select name="station_id" onchange="this.form.submit()" class="rounded border-slate-300 px-3 py-2 text-sm">
            <option value="all" @selected($stationId === 'all')>All Stations</option>
            @foreach ($stations as $station)
                <option value="{{ $station->id }}" @selected($stationId === (string) $station->id)>{{ $station->name }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search note or file name..." class="rounded border-slate-300 px-3 py-2 text-sm flex-1 min-w-[200px]">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <button type="submit" class="bg-slate-800 text-white text-sm px-4 py-2 rounded">Apply</button>
        @if ($status !== 'all' || $search !== '' || $month !== 'all' || $year !== (string) now()->year || $stationId !== 'all')
            <a href="{{ route('records.index') }}" class="text-xs text-slate-500 underline">Reset filters</a>
        @endif
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

    @php
        $sortLink = function (string $column, string $label) use ($sort, $direction) {
            $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
            $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection]);
            $icon = $sort === $column
                ? ($direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down')
                : 'fa-sort text-slate-300';

            return '<a href="'.$url.'" class="inline-flex items-center gap-1 hover:text-slate-800">'.$label.' <i class="fa-solid '.$icon.' text-[10px]"></i></a>';
        };
    @endphp

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('id', 'Job ID') !!}</th>
                        <th class="px-4 py-3 font-semibold">Station</th>
                        <th class="px-4 py-3 font-semibold">File Options</th>
                        <th class="px-4 py-3 font-semibold">Print Details</th>
                        <th class="px-4 py-3 font-semibold">Cut Details</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('total_amount', 'Total Amount') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('created_at', 'Uploaded') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('printed_at', 'Printed') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('cut_at', 'Cut') !!}</th>
                        <th class="px-4 py-3 font-semibold">{!! $sortLink('updated_at', 'Last Updated') !!}</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded">{{ $job->printStation?->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($job->fileUrl())
                                    <a href="{{ $job->fileUrl() }}" target="_blank" class="inline-flex items-center gap-1 mb-1 bg-purple-500 text-white text-xs px-3 py-1 rounded">
                                        <i class="fa-solid fa-print"></i> View File
                                    </a><br>
                                @endif
                                <strong>Note:</strong> {{ $job->note }}<br>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sky-600 text-xs whitespace-nowrap">
                                {{ $job->sheets }} x {{ $job->rate }} = {{ $job->print_total }} Rs
                            </td>
                            <td class="px-4 py-3 text-purple-600 text-xs whitespace-nowrap">
                                {{ $job->cutting_jobs }} x {{ $job->cutting_rate }} = {{ $job->cutting_total }} Rs
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-600">
                                {{ $job->status->value === 'completed' ? number_format($job->total_amount, 2).' Rs' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                <i class="fa-solid fa-arrow-up text-sky-500"></i> {{ $job->created_at->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                @if ($job->printed_at)
                                    <i class="fa-solid fa-print text-orange-500"></i> {{ $job->printed_at->format('d/m/Y h:i A') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                @if ($job->cut_at)
                                    <i class="fa-solid fa-scissors text-emerald-500"></i> {{ $job->cut_at->format('d/m/Y h:i A') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                {{ $job->updated_at->format('d/m/Y h:i A') }}
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
                        <tr><td colspan="11" class="px-4 py-6 text-center text-slate-500">No records found for selected filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $jobs->links() }}
        </div>
    </div>
</x-app-layout>
