<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('audit.view'), 403);

        $logs = AuditLog::with('user:id,name')
            ->when($request->filled('entidad'), fn ($q) => $q->where('auditable_type', 'like', '%'.$request->string('entidad').'%'))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('admin/audit/Index', [
            'logs' => $logs->through(fn (AuditLog $log) => [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'Sistema',
                'action' => $log->action,
                'entity' => class_basename($log->auditable_type),
                'entityId' => $log->auditable_id,
                'ip' => $log->ip_address,
                'createdAt' => $log->created_at->translatedFormat('d M Y, H:i'),
            ]),
            'filters' => $request->only('entidad'),
        ]);
    }
}
