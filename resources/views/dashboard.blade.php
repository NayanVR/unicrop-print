<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Overview</h2>
    </div>

    <div class="bg-[#eaf7e1] border border-[#3f9b3f]/20 rounded-2xl p-6 mb-6 shadow-sm shadow-[#1b5e2e]/5">
        <h3 class="font-bold text-[#1b5e2e]">Welcome to Unicrop Print!</h3>
        <p class="mt-2 text-sm text-slate-600">
            Once a file is printed at the Print Station, it moves straight to the Cutting Station, where cutting jobs (default 1) can be recorded and marked as "Cut & Done".
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 text-center shadow-sm hover:shadow-md transition-shadow">
            <p class="text-slate-500 text-sm font-semibold">Pending Prints</p>
            <div class="text-2xl font-extrabold text-blue-600 mt-2">{{ $pending_prints }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 text-center shadow-sm hover:shadow-md transition-shadow">
            <p class="text-slate-500 text-sm font-semibold">Pending Cuts</p>
            <div class="text-2xl font-extrabold text-purple-600 mt-2">{{ $pending_cuts }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 text-center shadow-sm hover:shadow-md transition-shadow">
            <p class="text-slate-500 text-sm font-semibold">Completed Jobs</p>
            <div class="text-2xl font-extrabold text-[#1b5e2e] mt-2">{{ $completed }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 text-center shadow-sm hover:shadow-md transition-shadow">
            <p class="text-slate-500 text-sm font-semibold">Total Revenue</p>
            <div class="text-2xl font-extrabold text-[#1b5e2e] mt-2">{{ number_format($revenue, 2) }} Rs</div>
        </div>
    </div>
</x-app-layout>
