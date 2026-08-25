<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;

/**
 * Records every admin action that affects revenue: commission changes,
 * feature/plan toggles, subscription system on/off, seller commission
 * overrides. Append-only — nothing here is ever updated or deleted.
 */
class AuditLogService
{
    public function record(?User $admin, string $action, mixed $old, mixed $new): AdminAuditLog
    {
        return AdminAuditLog::create([
            'admin_user_id' => $admin?->id,
            'action' => $action,
            'old_value' => $this->wrap($old),
            'new_value' => $this->wrap($new),
            'created_at' => now(),
        ]);
    }

    private function wrap(mixed $value): array
    {
        return is_array($value) ? $value : ['value' => $value];
    }
}
