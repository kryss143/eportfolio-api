<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HandlesMongoFallback;

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'max:72'],
        ]);

        // Skip the update when only the optional password fields were left
        // empty; otherwise an empty string would overwrite the hash.
        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    public function toggleAdmin(User $user)
    {
        // Prevent removing own admin rights
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own admin access.');
        }

        // Prevent removing the last admin
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot remove the last admin user.');
            }
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        $status = $user->is_admin ? 'granted' : 'revoked';

        return back()->with('success', "Admin access {$status} for {$user->name}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $adminCount = User::where('is_admin', true)->count();
        if ($user->is_admin && $adminCount <= 1) {
            return back()->with('error', 'Cannot delete the last admin user.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been deleted.");
    }
}
