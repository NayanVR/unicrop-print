<x-app-layout>
    <x-slot name="header">Manage Users</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manage Users</h2>
            <p class="mt-1 text-sm text-slate-500">Create users and control their access level.</p>
        </div>
        <button type="button" onclick="document.getElementById('create-user-modal').showModal()"
            class="inline-flex items-center gap-2 bg-[#1b5e2e] hover:bg-[#164d26] text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
            <i class="fa-solid fa-user-plus text-sm"></i> New User
        </button>
    </div>

    {{-- Create User Modal --}}
    <dialog id="create-user-modal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md border-0 backdrop:bg-black/50"
        x-data="{ printStation: false, isAdmin: false }">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-user-plus text-[#1b5e2e]"></i> Create New User</h3>
            <button type="button" onclick="document.getElementById('create-user-modal').close()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
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
            <div x-data="{ show: false }">
                <label class="block text-sm font-semibold mb-1 text-slate-700">Password</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" required
                        class="w-full rounded-lg border-slate-300 px-3 py-2 pr-10 text-sm focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30">
                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fa-solid text-sm"></i>
                    </button>
                </div>
            </div>
            <div class="border-t border-slate-100 pt-4">
                <label class="flex items-center gap-2 mb-3 font-semibold text-sm text-slate-700">
                    <input type="checkbox" name="is_admin" value="1" x-model="isAdmin" class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                    Admin (full access)
                </label>
                <div x-show="!isAdmin" class="space-y-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Permissions</label>
                    @foreach ($permissions as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                @if ($key === 'print_station') x-model="printStation" @endif
                                class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                            {{ $label }}
                        </label>
                    @endforeach
                    <div x-show="printStation" class="pl-4 border-l-2 border-emerald-100 mt-2 space-y-2">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="can_print" value="1" checked class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                            Allow printing files
                        </label>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide">Assigned Stations</label>
                        @foreach ($stations as $station)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="station_ids[]" value="{{ $station->id }}" class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                {{ $station->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('create-user-modal').close()"
                    class="px-4 py-2 text-sm rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-[#1b5e2e] hover:bg-[#164d26] text-white font-semibold transition">
                    Create User
                </button>
            </div>
        </form>
    </dialog>

    {{-- Pending Password Resets --}}
    @if ($pendingResets->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
            <h3 class="font-bold text-amber-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> Pending Password Reset Requests
            </h3>
            <ul class="space-y-2">
                @foreach ($pendingResets as $reset)
                    <li class="flex items-center justify-between bg-white border border-amber-200 rounded-xl p-3">
                        <span class="text-sm"><strong>{{ $reset->user->name }}</strong>
                            <span class="text-slate-500 ml-2">{{ $reset->user->email }}</span></span>
                        <button type="button" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition"
                            onclick="document.getElementById('set-password-{{ $reset->user->id }}').showModal()">
                            Set New Password
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Search / Filter bar --}}
    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 items-center bg-white border border-slate-200 rounded-2xl px-4 py-3 mb-6 shadow-sm">
        <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm"></i>
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..."
            class="rounded-lg border-slate-200 px-3 py-2 text-sm flex-1 min-w-[180px] focus:border-[#3f9b3f] focus:ring-[#3f9b3f]/30">
        <select name="sort" onchange="this.form.submit()" class="rounded-lg border-slate-200 px-3 py-2 text-sm text-slate-600">
            <option value="name" @selected($sort === 'name')>Sort: Name</option>
            <option value="created_at" @selected($sort === 'created_at')>Sort: Date Added</option>
        </select>
        <select name="direction" onchange="this.form.submit()" class="rounded-lg border-slate-200 px-3 py-2 text-sm text-slate-600">
            <option value="asc" @selected($direction === 'asc')>A → Z</option>
            <option value="desc" @selected($direction === 'desc')>Z → A</option>
        </select>
        <button type="submit" class="bg-slate-800 text-white text-sm px-4 py-2 rounded-lg font-medium">Apply</button>
        @if ($search !== '')
            <a href="{{ route('users.index') }}" class="text-xs text-slate-400 underline">Reset</a>
        @endif
    </form>

    {{-- Users List --}}
    <div class="space-y-3">
        @forelse ($users as $user)
            @php
                $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                $colors = ['bg-violet-500','bg-sky-500','bg-emerald-500','bg-rose-500','bg-amber-500','bg-indigo-500'];
                $color = $colors[$user->id % count($colors)];
            @endphp

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden"
                x-data="{ printStation: {{ in_array('print_station', $user->permissions ?? []) ? 'true' : 'false' }}, isAdmin: {{ $user->is_admin ? 'true' : 'false' }}, open: false }">

                {{-- User header row --}}
                <div class="flex items-center gap-4 px-5 py-4">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full {{ $color }} text-white font-bold text-sm flex items-center justify-center flex-shrink-0 select-none">
                        {{ $initials }}
                    </div>

                    {{-- Name / email --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-slate-900">{{ $user->name }}</span>
                            <span class="text-slate-400 text-xs">#{{ $user->id }}</span>
                            @if ($user->is_admin)
                                <span class="bg-[#1b5e2e] text-white text-[10px] font-bold px-2 py-0.5 rounded-full tracking-wide">ADMIN</span>
                            @else
                                @foreach ($user->permissions ?? [] as $perm)
                                    <span class="bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-0.5 rounded-full border border-slate-200">
                                        {{ $permissions[$perm] ?? $perm }}
                                    </span>
                                @endforeach
                            @endif
                            @if ($user->id === auth()->id())
                                <span class="bg-sky-50 text-sky-600 text-[10px] font-semibold px-2 py-0.5 rounded-full border border-sky-200">YOU</span>
                            @endif
                        </div>
                        <p class="text-slate-400 text-xs mt-0.5 truncate">{{ $user->email }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition">
                            <i class="fa-solid fa-sliders text-slate-400"></i>
                            <span x-text="open ? 'Close' : 'Edit Access'"></span>
                        </button>
                        <button type="button" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition"
                            onclick="document.getElementById('set-password-{{ $user->id }}').showModal()">
                            <i class="fa-solid fa-key text-slate-400"></i> Password
                        </button>
                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-100 hover:bg-red-50 text-red-400 hover:text-red-600 transition disabled:opacity-30 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Expandable permissions editor --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    class="border-t border-slate-100 bg-slate-50 px-5 py-4"
                    {{ $user->id === auth()->id() ? 'style=opacity:.5;pointer-events:none' : '' }}>
                    <form method="POST" action="{{ route('users.access.update', $user) }}">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-wrap gap-6 items-start">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Role</label>
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input type="checkbox" name="is_admin" value="1" x-model="isAdmin"
                                        class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                    Admin (full access)
                                </label>
                            </div>

                            <div x-show="!isAdmin">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Permissions</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($permissions as $key => $label)
                                        <label class="flex items-center gap-1.5 text-xs text-slate-700 bg-white border border-slate-200 rounded-lg px-3 py-1.5 cursor-pointer hover:border-[#3f9b3f]/40 transition">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                {{ in_array($key, $user->permissions ?? []) ? 'checked' : '' }}
                                                @if ($key === 'print_station') x-model="printStation" @endif
                                                class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div x-show="printStation && !isAdmin">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Print Stations</label>
                                <div class="flex flex-wrap gap-2">
                                    <label class="flex items-center gap-1.5 text-xs text-slate-700 bg-white border border-slate-200 rounded-lg px-3 py-1.5">
                                        <input type="checkbox" name="can_print" value="1" {{ $user->can_print ? 'checked' : '' }}
                                            class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                        Allow printing
                                    </label>
                                    @foreach ($stations as $station)
                                        <label class="flex items-center gap-1.5 text-xs text-slate-700 bg-white border border-slate-200 rounded-lg px-3 py-1.5">
                                            <input type="checkbox" name="station_ids[]" value="{{ $station->id }}"
                                                {{ $user->printStations->contains($station) ? 'checked' : '' }}
                                                class="rounded border-slate-300 text-[#3f9b3f] focus:ring-[#3f9b3f]">
                                            {{ $station->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="mt-4 inline-flex items-center gap-1.5 bg-[#1b5e2e] hover:bg-[#164d26] text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                            <i class="fa-solid fa-check"></i> Save Changes
                        </button>
                    </form>
                </div>

                {{-- Set Password Modal --}}
                <dialog id="set-password-{{ $user->id }}" class="rounded-2xl shadow-2xl p-0 w-96 border-0 backdrop:bg-black/50"
                    x-data="{
                        pwd: '',
                        copied: false,
                        generate() {
                            const chars = 'abcdefghjkmnpqrstuvwxyz23456789';
                            let p = '';
                            for (let i = 0; i < 10; i++) p += chars[Math.floor(Math.random() * chars.length)];
                            this.pwd = p;
                            this.copied = false;
                        },
                        copy() {
                            navigator.clipboard.writeText(this.pwd);
                            this.copied = true;
                            setTimeout(() => this.copied = false, 2000);
                        }
                    }">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-key text-amber-500"></i> Set Password — {{ $user->name }}
                        </h3>
                        <button type="button" onclick="document.getElementById('set-password-{{ $user->id }}').close()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('users.password.update', $user) }}" class="p-5 space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Auto-generate section --}}
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 space-y-2">
                            <p class="text-xs font-semibold text-amber-700">Auto Generate Password</p>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="generate()"
                                    class="text-xs bg-amber-500 hover:bg-amber-600 text-white font-semibold px-3 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-dice mr-1"></i> Generate
                                </button>
                                <template x-if="pwd">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="font-mono text-sm font-bold text-slate-800 bg-white border border-amber-200 px-3 py-1 rounded-lg flex-1 text-center tracking-widest" x-text="pwd"></span>
                                        <button type="button" @click="copy()"
                                            class="text-xs px-2 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-100 transition whitespace-nowrap">
                                            <i class="fa-solid" :class="copied ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <template x-if="pwd">
                                <p class="text-[10px] text-amber-600">Password generate thay gayo. Niche "Set This Password" click karo.</p>
                            </template>
                        </div>

                        {{-- Hidden input auto-filled when generated, or manual --}}
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-1">Or type manually:</p>
                            <div class="relative">
                                <input type="text" name="password" x-model="pwd" required minlength="8"
                                    placeholder="Password type karo (min 8 chars)"
                                    class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm font-mono focus:border-[#3f9b3f] focus:ring-[#3f9b3f]/30">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="document.getElementById('set-password-{{ $user->id }}').close()"
                                class="px-3 py-2 text-xs rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold">
                                <i class="fa-solid fa-check mr-1"></i> Set This Password
                            </button>
                        </div>
                    </form>
                </dialog>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400">
                <i class="fa-solid fa-users text-4xl mb-3 block"></i>
                No users found.
            </div>
        @endforelse
    </div>
</x-app-layout>
