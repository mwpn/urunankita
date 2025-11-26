<?php

namespace Modules\ActivityLog\Services;

use Modules\ActivityLog\Models\ActivityLogModel;

class ActivityLogService
{
    protected ActivityLogModel $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
    }

    /**
     * Log user activity
     *
     * @param string $action Action type (create, update, delete, login, logout, view, etc)
     * @param string $entity Entity/Model name (User, Product, Order, etc)
     * @param mixed $entityId Entity ID
     * @param array $data Additional data (old_value, new_value, description, etc)
     * @return bool
     */
    public function log(string $action, ?string $entity = null, int|string|null $entityId = null, array $data = []): bool
    {
        try {
            $tenantId = session()->get('tenant_id');
            $user = auth_user();

            $logData = [
                'tenant_id' => $tenantId,
                'user_id' => $user['id'] ?? null,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'old_value' => isset($data['old_value']) ? json_encode($data['old_value']) : null,
                'new_value' => isset($data['new_value']) ? json_encode($data['new_value']) : null,
                'description' => $data['description'] ?? null,
                'ip_address' => $this->getIpAddress(),
                'user_agent' => $this->getUserAgent(),
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            return $this->logModel->insert($logData) !== false;
        } catch (\Exception $e) {
            // Fail silently - logging should not break the application
            log_message('error', 'ActivityLog failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log create action
     *
     * @param string $entity
     * @param mixed $entityId
     * @param array $newValue
     * @param string|null $description
     * @return bool
     */
    public function logCreate(string $entity, int|string $entityId, array $newValue = [], ?string $description = null): bool
    {
        return $this->log('create', $entity, $entityId, [
            'new_value' => $newValue,
            'description' => $description ?? "Created {$entity} #{$entityId}",
        ]);
    }

    /**
     * Log update action
     *
     * @param string $entity
     * @param mixed $entityId
     * @param array $oldValue
     * @param array $newValue
     * @param string|null $description
     * @return bool
     */
    public function logUpdate(string $entity, int|string $entityId, array $oldValue = [], array $newValue = [], ?string $description = null): bool
    {
        return $this->log('update', $entity, $entityId, [
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description ?? "Updated {$entity} #{$entityId}",
        ]);
    }

    /**
     * Log delete action
     *
     * @param string $entity
     * @param mixed $entityId
     * @param array $oldValue
     * @param string|null $description
     * @return bool
     */
    public function logDelete(string $entity, int|string $entityId, array $oldValue = [], ?string $description = null): bool
    {
        return $this->log('delete', $entity, $entityId, [
            'old_value' => $oldValue,
            'description' => $description ?? "Deleted {$entity} #{$entityId}",
        ]);
    }

    /**
     * Log login action
     *
     * @param int $userId
     * @param array $metadata
     * @return bool
     */
    public function logLogin(int $userId, array $metadata = []): bool
    {
        return $this->log('login', 'User', $userId, [
            'description' => 'User logged in',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log logout action
     *
     * @param int $userId
     * @return bool
     */
    public function logLogout(int $userId): bool
    {
        return $this->log('logout', 'User', $userId, [
            'description' => 'User logged out',
        ]);
    }

    /**
     * Log view action
     *
     * @param string $entity
     * @param mixed $entityId
     * @param string|null $description
     * @return bool
     */
    public function logView(string $entity, int|string|null $entityId = null, ?string $description = null): bool
    {
        return $this->log('view', $entity, $entityId, [
            'description' => $description ?? "Viewed {$entity}" . ($entityId ? " #{$entityId}" : ''),
        ]);
    }

    /**
     * Get activity logs with filters
     *
     * @param array $filters
     * @return array
     */
    public function getLogs(array $filters = []): array
    {
        return $this->logModel->getLogs($filters);
    }

    /**
     * Get activity logs for specific entity
     *
     * @param string $entity
     * @param mixed $entityId
     * @return array
     */
    public function getEntityLogs(string $entity, $entityId): array
    {
        return $this->logModel->getLogs([
            'entity' => $entity,
            'entity_id' => $entityId,
        ]);
    }

    /**
     * Get IP address
     *
     * @return string
     */
    protected function getIpAddress(): string
    {
        $request = service('request');
        
        if ($request->getServer('HTTP_CLIENT_IP')) {
            return $request->getServer('HTTP_CLIENT_IP');
        } elseif ($request->getServer('HTTP_X_FORWARDED_FOR')) {
            return $request->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            return $request->getIPAddress() ?: '0.0.0.0';
        }
    }

    /**
     * Get user agent
     *
     * @return string
     */
    protected function getUserAgent(): string
    {
        $request = service('request');
        return $request->getUserAgent()->getAgentString() ?: '';
    }
}

