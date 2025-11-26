<?php

namespace Modules\ActivityLog\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Services;

class ActivityLogController extends BaseController
{
    /**
     * Get activity logs
     * GET /activity-log/list
     */
    public function list()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $activityLogService = Services::activityLog();

        $filters = [
            'tenant_id' => $tenantId,
            'user_id' => $this->request->getGet('user_id'),
            'action' => $this->request->getGet('action'),
            'entity' => $this->request->getGet('entity'),
            'entity_id' => $this->request->getGet('entity_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'limit' => $this->request->getGet('limit') ?? 100,
            'offset' => $this->request->getGet('offset') ?? 0,
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        $logs = $activityLogService->getLogs($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get activity summary
     * GET /activity-log/summary
     */
    public function summary()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $activityLogModel = new \Modules\ActivityLog\Models\ActivityLogModel();
        $summary = $activityLogModel->getSummary(['tenant_id' => $tenantId]);

        return $this->response->setJSON([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get entity logs
     * GET /activity-log/entity/{entity}/{entityId}
     */
    public function entityLogs($entity, $entityId)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $activityLogService = Services::activityLog();
        $logs = $activityLogService->getEntityLogs($entity, $entityId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $logs,
        ]);
    }
}

