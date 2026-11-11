<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request): Response
    {
        $query = Branch::withCount(['users', 'events']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $branches = $query->orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Branches/Index', [
            'branches' => $branches,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'is_headquarters' => ['boolean'],
        ]);

        $validated['is_headquarters'] = (bool) ($validated['is_headquarters'] ?? false);

        $branch = Branch::create($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($branch)
            ->withProperties(['code' => $branch->code, 'name' => $branch->name])
            ->log("Menambahkan kantor cabang baru: [{$branch->code}] {$branch->name}");

        return redirect()->route('admin.branches.index')->with('success', "Cabang [{$branch->code}] {$branch->name} berhasil ditambahkan.");
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches')->ignore($branch->id)],
            'is_headquarters' => ['boolean'],
        ]);

        $validated['is_headquarters'] = (bool) ($validated['is_headquarters'] ?? false);

        $branch->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($branch)
            ->withProperties(['code' => $branch->code, 'name' => $branch->name])
            ->log("Memperbarui kantor cabang: [{$branch->code}] {$branch->name}");

        return redirect()->route('admin.branches.index')->with('success', "Cabang [{$branch->code}] berhasil diperbarui.");
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        if ($branch->users()->exists() || $branch->events()->exists()) {
            return back()->withErrors([
                'error' => "Cabang [{$branch->code}] tidak dapat dihapus karena masih memiliki data pengguna atau event terkait.",
            ]);
        }

        $code = $branch->code;
        $name = $branch->name;

        activity()
            ->causedBy($request->user())
            ->performedOn($branch)
            ->withProperties(['code' => $code, 'name' => $name])
            ->log("Menghapus kantor cabang: [{$code}] {$name}");

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', "Cabang [{$code}] berhasil dihapus.");
    }
}
