<x-app-layout>
    <x-slot name="header">Manage Users</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manage Users</h2>
        <p class="mt-1 text-sm text-slate-500">Create users and control their access level.</p>
    </div>

    @if ($pendingResets->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
            <h3 class="font-bold text-amber-700 mb-3 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Pending Password Reset Requests</h3>
            <ul class="space-y-2">
                @foreach ($pendingResets as $reset)
                    <li class="flex items-center justify-between bg-white border border-amber-200 rounded-xl p-3">
                        <span class="text-sm"><strong>{{ $reset->user->name }}</strong> <span class="text-slate-500">{{ $reset->user->email }}</span></span>
                        <button type="button" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded"
                            onclick="document.getElementById('set-password-{{ $reset->user->id }}').showModal()">
                            Set New Password
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm shadow-[#1b5e2e]/5 lg:col-span-1 self-start">
            <h3 class="font-bold text-[#1b5e2e] mb-5 flex items-center gap-2"><i class="fa-solid fa-user-plus"></i> Create User</h3>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4" x-data="{ printStation: false, isAdmin: false }">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1 text-slate-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1 text-slate-700">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30">
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <label class="flex items-center gap-2 mb-3 font-semibold text-sm text-slate-700">
                        <input type="checkbox" name="is_admin" value="1" x-model="isAdmin" class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                        Admin (full access)
                    </label>

                    <div x-show="!isAdmin" class="space-y-2">
                        <label class="block text-sm font-semibold mb-1 text-slate-700">Access Permissions</label>
                        @foreach ($permissions as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                    @if ($key === 'print_station') x-model="printStation" @endif
                                    class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                {{ $label }}
                            </label>
                        @endforeach

                        <div x-show="printStation" class="pl-4 border-l-2 border-[#eaf7e1] mt-2 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="can_print" value="1" checked class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                Allow printing files (uncheck to block)
                            </label>
                            <label class="block text-xs font-semibold text-slate-500">Assigned Print Stations</label>
                            @foreach ($stations as $station)
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="station_ids[]" value="{{ $station->id }}" class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                    {{ $station->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#1b5e2e] hover:bg-[#164d26] text-white font-semibold px-4 py-2.5 rounded-lg transition">
                    Create User
                </button>
            </form>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm shadow-[#1b5e2e]/5 lg:col-span-2 self-start">
            <h3 class="font-bold text-[#1b5e2e] mb-5 flex items-center gap-2"><i class="fa-solid fa-users"></i> All Users</h3>

            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 items-center bg-slate-50 p-3 rounded-lg mb-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..." class="rounded-lg border-slate-300 px-3 py-2 text-sm flex-1 min-w-[180px]">
                <select name="sort" onchange="this.form.submit()" class="rounded-lg border-slate-300 px-3 py-2 text-sm">
                    <option value="name" @selected($sort === 'name')>Sort: Name</option>
                    <option value="created_at" @selected($sort === 'created_at')>Sort: Date Added</option>
                </select>
                <select name="direction" onchange="this.form.submit()" class="rounded-lg border-slate-300 px-3 py-2 text-sm">
                    <option value="asc" @selected($direction === 'asc')>Ascending</option>
                    <option value="desc" @selected($direction === 'desc')>Descending</option>
                </select>
                <button type="submit" class="bg-[#1b5e2e] text-white text-sm px-4 py-2 rounded-lg">Apply</button>
                @if ($search !== '')
                    <a href="{{ route('users.index') }}" class="text-xs text-slate-500 underline">Reset</a>
                @endif
            </form>

            <div class="space-y-3">
                @forelse ($users as $user)
                    <div class="border border-slate-200 bg-slate-50 rounded-xl p-4" x-data="{ printStation: {{ in_array('print_station', $user->permissions ?? []) ? 'true' : 'false' }}, isAdmin: {{ $user->is_admin ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <strong class="text-slate-800">{{ $user->name }}</strong>
                                <span class="text-slate-400 text-xs ml-2">#{{ $user->id }}</span>
                                <span class="text-slate-500 text-sm ml-2">{{ $user->email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="bg-slate-600 hover:bg-slate-700 text-white text-xs px-3 py-1.5 rounded"
                                    onclick="document.getElementById('set-password-{{ $user->id }}').showModal()">
                                    <i class="fa-solid fa-key"></i> Set Password
                                </button>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded disabled:bg-slate-200 disabled:text-slate-400">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <dialog id="set-password-{{ $user->id }}" class="rounded-lg p-6 w-80 backdrop:bg-black/40">
                            <form method="POST" action="{{ route('users.password.update', $user) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-semibold mb-1">New Password for {{ $user->name }}</label>
                                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm">
                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" onclick="document.getElementById('set-password-{{ $user->id }}').close()" class="bg-slate-200 text-slate-700 text-xs px-3 py-2 rounded">Cancel</button>
                                    <button type="submit" class="bg-[#1b5e2e] text-white text-xs px-3 py-2 rounded">Update Password</button>
                                </div>
                            </form>
                        </dialog>

                        <form method="POST" action="{{ route('users.access.update', $user) }}" {{ $user->id === auth()->id() ? 'class=opacity-50 pointer-events-none' : '' }}>
                            @csrf
                            @method('PATCH')
                            <label class="flex items-center gap-2 mb-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="is_admin" value="1" x-model="isAdmin" {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                Admin (full access)
                            </label>

                            <div x-show="!isAdmin" class="flex flex-wrap gap-3 mb-2">
                                @foreach ($permissions as $key => $label)
                                    <label class="flex items-center gap-1.5 text-xs text-slate-600 bg-white border border-slate-200 rounded-lg px-2 py-1">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ in_array($key, $user->permissions ?? []) ? 'checked' : '' }}
                                            @if ($key === 'print_station') x-model="printStation" @endif
                                            class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>

                            <div x-show="printStation" class="flex flex-wrap items-center gap-3 mb-3 pl-2 border-l-2 border-[#eaf7e1]">
                                <label class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <input type="checkbox" name="can_print" value="1" {{ $user->can_print ? 'checked' : '' }} class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                    Allow printing
                                </label>
                                @foreach ($stations as $station)
                                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                                        <input type="checkbox" name="station_ids[]" value="{{ $station->id }}" {{ $user->printStations->contains($station) ? 'checked' : '' }} class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                        {{ $station->name }}
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }} class="bg-[#3f9b3f] hover:bg-[#1b5e2e] text-white text-xs font-semibold px-3 py-1.5 rounded">
                                Save Access
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-center text-slate-500 text-sm py-6">No users found for selected filter.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
