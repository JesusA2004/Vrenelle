<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Records who changed what on the entities that matter most (products,
 * prices, stock, orders, promotions, users, roles, settings). Call this
 * explicitly from admin actions rather than relying on a blanket model
 * observer, so log entries stay meaningful and readable.
 */
class AuditService
{
    public static function log(string $action, Model $model, array $old = [], array $new = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
            'created_at' => now(),
        ]);
    }
}
