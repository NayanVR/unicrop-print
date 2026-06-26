<x-app-layout>
    <x-slot name="header">Manage Users</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manage Users</h2>
        <p class="mt-1 text-sm text-slate-500">Create users and control their access level.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm shadow-[#1b5e2e]/5 lg:col-span-1 self-start">
            <h3 class="font-bold text-[#1b5e2e] mb-5 flex items-center gap-2"><i class="fa-solid fa-user-plus"></i> Create User</h3>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
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
                <div>
                    <label class="block text-sm font-semibold mb-1 text-slate-700">Access (Role)</label>
                    <select name="role" required
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-[#1b5e2e] hover:bg-[#164d26] text-white font-semibold px-4 py-2.5 rounded-lg transition">
                    Create User
                </button>
            </form>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm shadow-[#1b5e2e]/5 lg:col-span-2 self-start">
            <h3 class="font-bold text-[#1b5e2e] mb-5 flex items-center gap-2"><i class="fa-solid fa-users"></i> All Users</h3>

            <ul class="space-y-2">
                @foreach ($users as $user)
                    <li class="flex items-center justify-between border border-slate-200 bg-slate-50 rounded-xl p-3">
                        <div>
                            <strong class="text-slate-800">{{ $user->name }}</strong>
                            <span class="text-slate-500 text-sm ml-2">{{ $user->email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('users.role.update', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()" {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    class="rounded-lg border-slate-300 text-xs px-2 py-1.5 capitalize focus:border-[#3f9b3f] focus:ring-2 focus:ring-[#3f9b3f]/30 disabled:bg-slate-100 disabled:text-slate-400">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded disabled:bg-slate-200 disabled:text-slate-400">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
