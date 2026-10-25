<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request): View
    {
        $query = User::withCount('shipments')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_CUSTOMER])],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $this->auditService->log(
            'USER_CREATED',
            'User',
            $user->id,
            "User {$user->email} created with role {$user->role} by Admin.",
            null,
            $user->toArray(),
            Auth::id()
        );

        return back()->with('success', "User account for {$user->name} ({$user->role}) created successfully.");
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id() && $request->role !== User::ROLE_ADMIN) {
            return back()->with('error', 'Cannot demote your own administrator account.');
        }

        $request->validate([
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_CUSTOMER])],
        ]);

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        $this->auditService->log(
            'USER_ROLE_CHANGED',
            'User',
            $user->id,
            "User {$user->email} role modified from {$oldRole} to {$user->role}.",
            ['role' => $oldRole],
            ['role' => $user->role],
            Auth::id()
        );

        return back()->with('success', "Role for {$user->name} changed to {$user->role}.");
    }
}
