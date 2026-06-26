<x-app-layout>
    <x-slot name="header">Cutting Station</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Cutting Station</h2>
        <p class="text-slate-500 text-sm mt-1">Process jobs that are printed and waiting for cutting.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Job ID</th>
                        <th class="px-4 py-3 font-semibold">File & Details</th>
                        <th class="px-4 py-3 font-semibold">Sheets Printed</th>
                        <th class="px-4 py-3 font-semibold">Cutting Rate</th>
                        <th class="px-4 py-3 font-semibold">Cutting Jobs</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
                            <td class="px-4 py-3">
                                <strong>Note: {{ $job->note }}</strong><br>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $job->sheets }} Sheets<br>
                                <span class="text-xs text-slate-500">Printed At: {{ $job->printed_at?->format('h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-emerald-600 font-bold text-xs">{{ $cuttingRate }} Rs / Job</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" form="cut-job-{{ $job->id }}" name="cutting_jobs" value="1" min="0" class="w-20 rounded border-slate-300 px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <form id="cut-job-{{ $job->id }}" method="POST" action="{{ route('cutting.update', $job) }}">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <button type="submit" form="cut-job-{{ $job->id }}" class="bg-purple-500 hover:bg-purple-600 text-white text-xs font-semibold px-3 py-2 rounded">
                                    Mark Cut & Done
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No pending cutting jobs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
