<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Models\PrintStation;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::with('printStations')->orderBy('name')->get(),
            'permissions' => Permission::ALL,
            'stations' => PrintStation::orderBy('name')->get(),
            'pendingResets' => PasswordResetRequest::with('user')->where('status', 'pending')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAccess($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_admin' => $validated['is_admin'],
            'permissions' => $validated['permissions'],
            'can_print' => $validated['can_print'],
        ]);

        $user->printStations()->sync($validated['station_ids']);

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function updateAccess(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')->with('error', 'You cannot change your own access level.');
        }

        $validated = $this->validateAccess($request);

        $user->update([
            'is_admin' => $validated['is_admin'],
            'permissions' => $validated['permissions'],
            'can_print' => $validated['can_print'],
        ]);

        $user->printStations()->sync($validated['station_ids']);

        return redirect()->route('users.index')->with('status', 'User access updated.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => bcrypt($validated['password'])]);

        $user->passwordResetRequests()->where('status', 'pending')->update(['status' => 'resolved']);

        return redirect()->route('users.index')->with('status', "Password updated for {$user->name}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    /**
     * @param  array<string, mixed>  $extraRules
     * @return array<string, mixed>
     */
    private function validateAccess(Request $request, array $extraRules = []): array
    {
        $validated = $request->validate([
            ...$extraRules,
            'is_admin' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:'.implode(',', array_keys(Permission::ALL))],
            'can_print' => ['nullable', 'boolean'],
            'station_ids' => ['nullable', 'array'],
            'station_ids.*' => ['exists:print_stations,id'],
        ]);

        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['permissions'] = $request->input('permissions', []);
        $validated['can_print'] = $request->boolean('can_print', true);
        $validated['station_ids'] = $request->input('station_ids', []);

        return $validated;
    }
}
