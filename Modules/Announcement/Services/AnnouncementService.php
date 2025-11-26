<?php

namespace Modules\Announcement\Services;

use Modules\Announcement\Models\AnnouncementModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class AnnouncementService
{
    protected AnnouncementModel $announcementModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create announcement
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $announcementData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'] ?? 'info',
            'priority' => $data['priority'] ?? 'normal',
            'is_published' => $data['is_published'] ?? 0,
            'published_at' => ($data['is_published'] ?? 0) ? date('Y-m-d H:i:s') : null,
            'expires_at' => $data['expires_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ];

        $id = $this->announcementModel->insert($announcementData);

        if ($id) {
            $this->activityLog->logCreate('Announcement', $id, $announcementData, 'Pengumuman platform dibuat');
        }

        return $id ?: false;
    }

    /**
     * Update announcement
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $announcement = $this->announcementModel->find($id);
        if (!$announcement) {
            return false;
        }

        $updateData = [
            'title' => $data['title'] ?? $announcement['title'],
            'content' => $data['content'] ?? $announcement['content'],
            'type' => $data['type'] ?? $announcement['type'],
            'priority' => $data['priority'] ?? $announcement['priority'],
            'expires_at' => $data['expires_at'] ?? $announcement['expires_at'],
        ];

        // Handle publish status
        if (isset($data['is_published'])) {
            $updateData['is_published'] = $data['is_published'];
            if ($data['is_published'] && !$announcement['published_at']) {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        }

        $result = $this->announcementModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Announcement', $id, $announcement, $updateData, 'Pengumuman platform diperbarui');
        }

        return $result;
    }

    /**
     * Delete announcement
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $announcement = $this->announcementModel->find($id);
        if (!$announcement) {
            return false;
        }

        $result = $this->announcementModel->delete($id);

        if ($result) {
            $this->activityLog->logDelete('Announcement', $id, $announcement, 'Pengumuman platform dihapus');
        }

        return $result;
    }

    /**
     * Get announcement by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->announcementModel->find($id);
    }

    /**
     * Get all announcements
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        return $this->announcementModel->getAll($filters);
    }

    /**
     * Get published announcements (for tenants)
     *
     * @param int|null $limit
     * @return array
     */
    public function getPublished(?int $limit = null): array
    {
        return $this->announcementModel->getPublished($limit);
    }
}

