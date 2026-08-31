<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('roles.manage'), 403);

        return Inertia::render('admin/roles/Index', [
            'roles' => Role::withCount('users')->get()->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'usersCount' => $role->users_count,
                'permissions' => $role->permissions->pluck('name'),
            ]),
            'permissions' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('roles.manage'), 403);
        abort_unless($request->user()->hasRole('super_admin'), 403, 'Solo un Super Admin puede crear roles.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('roles.manage'), 403);
        abort_unless($request->user()->hasRole('super_admin'), 403, 'Solo un Super Admin puede modificar permisos.');
        abort_if($role->name === 'super_admin', 422, 'El rol Super Admin no puede modificarse.');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Permisos del rol actualizados.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);
        abort_if(in_array($role->name, ['super_admin', 'administrador'], true), 422, 'Este rol no puede eliminarse.');

        $role->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
    }
}
