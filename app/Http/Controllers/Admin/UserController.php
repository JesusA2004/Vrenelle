<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $users = User::with('roles:id,name')
            ->when($request->filled('buscar'), fn ($q) => $q->where('name', 'like', '%'.$request->string('buscar').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'isActive' => $user->is_active,
                'createdAt' => $user->created_at->translatedFormat('d M Y'),
            ]),
            'filters' => $request->only('buscar'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('users.manage'), 403);

        return Inertia::render('admin/users/Form', ['roles' => $this->availableRoles($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $this->validateUser($request);
        $roles = $this->guardedRoles($request, $data['roles'] ?? []);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, User $user): Response
    {
        abort_unless($request->user()->can('users.manage'), 403);

        return Inertia::render('admin/users/Form', [
            'user' => [...$user->toArray(), 'roles' => $user->roles->pluck('name')],
            'roles' => $this->availableRoles($request),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('users.manage'), 403);

        $data = $this->validateUser($request, $user->id);
        $roles = $this->guardedRoles($request, $data['roles'] ?? []);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            ...(filled($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('users.manage'), 403);
        abort_if($user->id === $request->user()->id, 422, 'No puedes eliminar tu propia cuenta.');

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    protected function validateUser(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$ignoreId ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Ya existe un usuario con este correo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
    }

    /**
     * A user may only grant the super_admin role if they hold it themselves —
     * this is enforced here, server-side, not just hidden in the UI.
     */
    protected function guardedRoles(Request $request, array $roles): array
    {
        if (in_array('super_admin', $roles, true) && ! $request->user()->hasRole('super_admin')) {
            return array_values(array_diff($roles, ['super_admin']));
        }

        return $roles;
    }

    protected function availableRoles(Request $request): array
    {
        $roles = Role::query()->pluck('name');

        if (! $request->user()->hasRole('super_admin')) {
            $roles = $roles->reject(fn ($role) => $role === 'super_admin');
        }

        return $roles->values()->all();
    }
}
