<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->orderBy('name')
            ->paginate(15);

        return view('coordinator.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        return view('coordinator.users.show', [
            'user' => $user,
            'canUpdateRole' => Gate::allows('updateRole', $user),
        ]);
    }

    public function editRole(User $user): View
    {
        Gate::authorize('updateRole', $user);

        return view('coordinator.users.edit-role', compact('user'));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        Gate::authorize('updateRole', [$user, $validated['role']]);

        $user->role = $validated['role'];
        $user->save();

        return redirect()
            ->route('coordinator.users.show', $user)
            ->with('status', 'Role pengguna berhasil diperbarui.');
    }
}
