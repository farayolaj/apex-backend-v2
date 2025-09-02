<?php

namespace App\Libraries\Notifications;

use App\Entities\Notifications;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;

class NotificationManager
{

    private Notifications $notifications;

    public function __construct()
    {
        $this->notifications = EntityLoader::loadClass(null, 'notifications');
    }

    public function sendNotifications(EventInterface $event): void
    {
        $data = json_encode($event->getMetadata());
        if ($data === false) {
            throw new \RuntimeException('Failed to encode notification metadata to JSON: ' . json_last_error_msg());
        }
        $recipients = $event->getRecipients();

        if (empty($recipients)) {
            return;
        }

        // Todo: Run in background
        $this->notifications->createMany(array_map(fn($recipient) => [
            'recipient_table' => $recipient->tableName,
            'recipient_id' => $recipient->id,
            'type' => $event->getName(),
            'data' => $data,
        ], $recipients));
    }

    private function processNotification($notification)
    {
        unset($notification['recipient_table'], $notification['recipient_id']);
        $notification['data'] = json_decode($notification['data'], true);
        return $notification;
    }

    public function getUnreadNotifications(string $recipientTable, int $recipientId, int $limit, int $offset): array
    {
        $notifications = $this->notifications->getUnreadNotifications($recipientTable, $recipientId, $limit, $offset);
        return array_map([$this, 'processNotification'], $notifications);
    }

    public function countUnreadNotifications(string $recipientTable, int $recipientId): int
    {
        return $this->notifications->countUnreadNotifications($recipientTable, $recipientId);
    }

    public function markNotificationsAsRead(string $recipientTable, int $recipientId, array $ids): bool
    {
        return $this->notifications->markAsRead($recipientTable, $recipientId, $ids);
    }
}
