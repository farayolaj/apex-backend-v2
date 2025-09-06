<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Libraries\Notifications\NotificationManager;
use App\Models\WebSessionManager;
use Config\Services;

class NotificationController extends BaseController
{
    private NotificationManager $notificationManager;

    public function __construct()
    {
        $this->notificationManager = Services::notificationManager();
    }

    public function getNotifications()
    {
        $userId = WebSessionManager::currentAPIUser()->id;
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 20;
        $offset = ($page - 1) * $perPage;

        $notifications = $this->notificationManager->getUnreadNotifications('students', $userId, $perPage, $offset);
        $totalUnread = $this->notificationManager->countUnreadNotifications('students', $userId);
        $data = [
            'paging' => [
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total_count' => $totalUnread,
                'total_pages' => (int)ceil($totalUnread / $perPage),
            ],
            'data' => $notifications,
        ];

        return ApiResponse::success(data: $data);
    }

    public function getNotificationCount()
    {
        $userId = WebSessionManager::currentAPIUser()->id;
        $totalUnread = $this->notificationManager->countUnreadNotifications('students', $userId);
        return ApiResponse::success(data: $totalUnread);
    }

    public function markAsRead()
    {
        $userId = WebSessionManager::currentAPIUser()->id;
        $data = $this->request->getJSON(true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return ApiResponse::error(message: 'Invalid input: ids must be an array', code: 400);
        }

        // Filter to keep only integer values
        $ids = array_filter($data['ids'], function ($id) {
            return is_int($id) || (is_string($id) && ctype_digit($id));
        });

        // Convert all values to integers
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return ApiResponse::error(message: 'No valid notification IDs provided', code: 400);
        }

        if ($this->notificationManager->markNotificationsAsRead('students', $userId, $ids)) {
            return ApiResponse::success(message: 'Notifications marked as read');
        } else {
            return ApiResponse::error(message: 'Failed to mark notifications as read', code: 500);
        }
    }
}
