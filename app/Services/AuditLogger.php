<?php

namespace App\Services;

use App\Model\AuditLog;
use Illuminate\Support\Carbon;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'confirmPassword',
        'confirm_password',
        'current_password',
        'token',
        'otp',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
    ];

    /**
     * Persist an audit log entry. Failures are swallowed so auditing never breaks the main request.
     *
     * @param  array  $data
     * @return void
     */
    public static function log(array $data): void
    {
        try {
            if (isset($data['properties']) && is_array($data['properties'])) {
                $data['properties'] = self::redact($data['properties']);
            }

            AuditLog::create([
                'user_id' => $data['user_id'] ?? null,
                'user_name' => $data['user_name'] ?? null,
                'event' => $data['event'] ?? 'api.request',
                'method' => $data['method'] ?? null,
                'path' => isset($data['path']) ? mb_substr((string) $data['path'], 0, 255) : null,
                'action' => $data['action'] ?? null,
                'subject_type' => $data['subject_type'] ?? null,
                'subject_id' => isset($data['subject_id']) && $data['subject_id'] !== null && $data['subject_id'] !== ''
                    ? (string) $data['subject_id']
                    : null,
                'properties' => $data['properties'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'status_code' => $data['status_code'] ?? null,
                'created_at' => $data['created_at'] ?? Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Recursively redact sensitive keys from a payload.
     *
     * @param  array  $payload
     * @return array
     */
    public static function redact(array $payload): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            $keyLower = is_string($key) ? strtolower($key) : $key;

            if (is_string($keyLower) && in_array($keyLower, array_map('strtolower', self::SENSITIVE_KEYS), true)) {
                $result[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $result[$key] = self::redact($value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
