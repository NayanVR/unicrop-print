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

    <div class="mt-8">
        <h3 class="text-lg font-bold text-slate-900 mb-4">By Print Station</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($stationStats as $stat)
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                    <p class="font-bold text-slate-800 mb-3"><i class="fa-solid fa-print text-purple-500"></i> {{ $stat['station']->name }}</p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-slate-500">Pending Prints</p>
                            <p class="font-bold text-blue-600">{{ $stat['pending_prints'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Pending Cuts</p>
                            <p class="font-bold text-purple-600">{{ $stat['pending_cuts'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Completed</p>
                            <p class="font-bold text-[#1b5e2e]">{{ $stat['completed'] }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Revenue</p>
                            <p class="font-bold text-[#1b5e2e]">{{ number_format($stat['revenue'], 2) }} Rs</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-sm">No print stations assigned to your account.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
