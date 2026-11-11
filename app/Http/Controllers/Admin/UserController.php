<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with(['branch', 'roles'])
            ->latest()
            ->paginate(15)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                    'code' => $user->branch->code,
                ] : null,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at->format('Y-m-d H:i'),
            ]);

        $branches = Branch::orderBy('name')->get(['id', 'name', 'code']);
        $roles = Role::orderBy('name')->pluck('name');

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'branches' => $branches,
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'branch_id' => $validated['branch_id'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['role' => $validated['role'], 'branch_id' => $validated['branch_id'] ?? null])
            ->log("Membuat pengguna baru: {$user->email}");

        return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'branch_id' => $user->branch_id,
            'role' => $user->roles->pluck('name')->first(),
        ];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->branch_id = $validated['branch_id'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'old' => $oldData,
                'new' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'branch_id' => $user->branch_id,
                    'role' => $validated['role'],
                ],
                'reason' => $validated['reason'] ?? 'Pembaruan data user oleh Admin',
            ])
            ->log("Memperbarui data pengguna: {$user->email}");

        return redirect()->back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $userEmail = $user->email;

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Menghapus pengguna: {$userEmail}");

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
