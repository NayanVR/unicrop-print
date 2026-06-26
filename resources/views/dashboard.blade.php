<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Dashboard Overview</h2>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h3 class="font-semibold text-slate-900">Welcome to Unicrop Print!</h3>
        <p class="mt-2 text-sm text-slate-700">
            Once a file is printed at the Print Station, it moves straight to the Cutting Station, where cutting jobs (default 1) can be recorded and marked as "Cut & Done".
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 text-sm font-semibold">Pending Prints</p>
            <div class="text-2xl font-bold text-blue-600 mt-2">{{ $pending_prints }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 text-sm font-semibold">Pending Cuts</p>
            <div class="text-2xl font-bold text-purple-600 mt-2">{{ $pending_cuts }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 text-sm font-semibold">Completed Jobs</p>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ $completed }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
            <p class="text-slate-500 text-sm font-semibold">Total Revenue</p>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($revenue, 2) }} Rs</div>
        </div>
    </div>
</x-app-layout>
