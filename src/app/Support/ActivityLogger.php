<?php

declare(strict_types=1);

namespace App\Support;

use App\Repositories\ActivityLogRepository;
use Throwable;

/**
 * Writes an audit trail entry. Logging must never break the main request.
 */
final class ActivityLogger
{
    public static function log(string $action, string $entity, ?int $entityId, string $description): void
    {
        try {
            $repository = new ActivityLogRepository(Database::connection());
            $repository->create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip_address'  => self::ipAddress(),
            ]);
        } catch (Throwable $exception) {
            error_log('Activity log failed: ' . $exception->getMessage());
        }
    }

    private static function ipAddress(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        return is_string($ip) ? substr($ip, 0, 45) : '';
    }
}
