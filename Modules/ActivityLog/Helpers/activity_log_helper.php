<?php

if (! function_exists('log_activity')) {
    /**
     * Helper function to log activity
     *
     * @param string $action
     * @param string|null $entity
     * @param mixed $entityId
     * @param array $data
     * @return bool
     */
    function log_activity(string $action, ?string $entity = null, int|string|null $entityId = null, array $data = []): bool
    {
        $activityLogService = \Config\Services::activityLog();
        return $activityLogService->log($action, $entity, $entityId, $data);
    }
}

if (! function_exists('log_create')) {
    /**
     * Helper to log create action
     */
    function log_create(string $entity, int|string $entityId, array $newValue = [], ?string $description = null): bool
    {
        $activityLogService = \Config\Services::activityLog();
        return $activityLogService->logCreate($entity, $entityId, $newValue, $description);
    }
}

if (! function_exists('log_update')) {
    /**
     * Helper to log update action
     */
    function log_update(string $entity, int|string $entityId, array $oldValue = [], array $newValue = [], ?string $description = null): bool
    {
        $activityLogService = \Config\Services::activityLog();
        return $activityLogService->logUpdate($entity, $entityId, $oldValue, $newValue, $description);
    }
}

if (! function_exists('log_delete')) {
    /**
     * Helper to log delete action
     */
    function log_delete(string $entity, int|string $entityId, array $oldValue = [], ?string $description = null): bool
    {
        $activityLogService = \Config\Services::activityLog();
        return $activityLogService->logDelete($entity, $entityId, $oldValue, $description);
    }
}

