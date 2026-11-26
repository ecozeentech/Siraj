<?php

namespace App\Services\Admin;

use App\Models\System\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = [], string $category = 'admin', ?int $actorId = null): AuditLog
    {
        $request = request();
        $actorId ??= Auth::guard('admin')->id() ?? Auth::id();

        return AuditLog::query()->create([
            'category' => $category,
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }

    public static function security(string $action, ?int $actorId = null, ?string $description = null, array $properties = []): AuditLog
    {
        return self::log($action, null, $description, $properties, 'security', $actorId);
    }
}
