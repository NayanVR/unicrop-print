<x-app-layout>
    <x-slot name="header">Print Queue Station</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Print Queue Station</h2>
        <p class="text-slate-500 text-sm mt-1">Process pending print jobs. Once done, they will be sent to the Cutting Station.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Job ID</th>
                        <th class="px-4 py-3 font-semibold">File & Note</th>
                        <th class="px-4 py-3 font-semibold">Upload Time</th>
                        <th class="px-4 py-3 font-semibold">Size & Rate</th>
                        <th class="px-4 py-3 font-semibold">Req. Sheets</th>
                        <th class="px-4 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($jobs as $job)
                        <tr>
                            <td class="px-4 py-3">#{{ $job->id }}</td>
                            <td class="px-4 py-3">
                                <strong>Note: {{ $job->note }}</strong><br>
                                <span class="text-xs text-slate-500">File: {{ $job->file_name }}</span><br>
                                @if ($job->fileUrl())
                                    <a href="{{ $job->fileUrl() }}" target="_blank" class="inline-flex items-center gap-1 mt-1 bg-purple-500 text-white text-xs px-3 py-1 rounded">
                                        <i class="fa-solid fa-print"></i> View/Print
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-slate-100 border border-slate-200 px-2 py-1 rounded">{{ $job->created_at->format('d/m/Y - h:i A') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $job->size->name }}<br>
                                <span class="text-emerald-600 font-bold text-xs">Rate: {{ $job->rate }} Rs</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" form="print-job-{{ $job->id }}" name="sheets" value="{{ $job->sheets }}" min="1" class="w-20 rounded border-slate-300 px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <form id="print-job-{{ $job->id }}" method="POST" action="{{ route('printer.update', $job) }}">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <button type="submit" form="print-job-{{ $job->id }}" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-3 py-2 rounded inline-flex items-center gap-1">
                                    Mark Printed <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No pending prints.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
