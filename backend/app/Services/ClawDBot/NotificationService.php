<?php

namespace App\Services\ClawDBot;

use App\Models\User;
use App\Notifications\ClawDBot\BotStatusAlert;
use App\Notifications\ClawDBot\SystemMaintenance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\AnonymousNotifiable;

class NotificationService
{
    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $userIds, string $notificationType, array $data = []): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            try {
                $notification = $this->createNotification($notificationType, $data);
                $user->notify($notification);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to send to user {$user->id}: {$e->getMessage()}";
                Log::error("NotificationService: Failed to send to user {$user->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Send notification to all admins
     */
    public function sendToAdmins(string $notificationType, array $data = []): array
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $adminIds = $adminUsers->pluck('id')->toArray();

        return $this->sendToUsers($adminIds, $notificationType, $data);
    }

    /**
     * Send notification to all agents
     */
    public function sendToAgents(string $notificationType, array $data = []): array
    {
        $agentUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'agent');
        })->get();

        $agentIds = $agentUsers->pluck('id')->toArray();

        return $this->sendToUsers($agentIds, $notificationType, $data);
    }

    /**
     * Send notification to all users
     */
    public function sendToAllUsers(string $notificationType, array $data = []): array
    {
        $allUsers = User::where('status', 'active')->get();
        $userIds = $allUsers->pluck('id')->toArray();

        return $this->sendToUsers($userIds, $notificationType, $data);
    }

    /**
     * Send email notification directly
     */
    public function sendEmail(string $to, string $subject, string $content, array $data = []): bool
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $content, $data) {
                $message->to($to)
                    ->subject($subject)
                    ->html($content);
            });

            Log::info("Email sent successfully", ['to' => $to, 'subject' => $subject]);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send email", [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send bot status alert
     */
    public function sendBotAlert(string $title, string $message, string $severity = 'warning'): array
    {
        return $this->sendToAdmins('bot_status_alert', [
            'title' => $title,
            'message' => $message,
            'severity' => $severity
        ]);
    }

    /**
     * Send system maintenance notification
     */
    public function sendMaintenanceNotification(string $message, \DateTime $scheduledAt): array
    {
        return $this->sendToAllUsers('system_maintenance', [
            'message' => $message,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send bulk notifications with queue
     */
    public function sendBulkNotifications(array $notifications): array
    {
        $results = [
            'queued' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($notifications as $notificationData) {
            try {
                $userIds = $notificationData['user_ids'] ?? [];
                $type = $notificationData['type'] ?? 'general';
                $data = $notificationData['data'] ?? [];

                if (empty($userIds)) {
                    $results['failed']++;
                    $results['errors'][] = "No user IDs provided for notification type: {$type}";
                    continue;
                }

                // Queue the notification
                $this->queueNotification($userIds, $type, $data);
                $results['queued']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to queue notification: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Queue notification for background processing
     */
    private function queueNotification(array $userIds, string $type, array $data): void
    {
        // This would dispatch a job to handle the notification
        // For now, we'll process immediately
        $this->sendToUsers($userIds, $type, $data);
    }

    /**
     * Create notification instance based on type
     */
    private function createNotification(string $type, array $data)
    {
        return match($type) {
            'bot_status_alert' => new BotStatusAlert(
                $data['title'] ?? 'Bot Alert',
                $data['message'] ?? 'Bot notification',
                $data['severity'] ?? 'info'
            ),
            'system_maintenance' => new SystemMaintenance(
                $data['message'] ?? 'System maintenance scheduled',
                new \DateTime($data['scheduled_at'] ?? 'now')
            ),
            default => throw new \InvalidArgumentException("Unknown notification type: {$type}")
        };
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics(): array
    {
        return [
            'total_notifications' => $this->getTotalNotifications(),
            'notifications_today' => $this->getNotificationsToday(),
            'notifications_this_week' => $this->getNotificationsThisWeek(),
            'notifications_this_month' => $this->getNotificationsThisMonth(),
            'success_rate' => $this->getNotificationSuccessRate(),
            'popular_types' => $this->getPopularNotificationTypes()
        ];
    }

    /**
     * Get total notifications count
     */
    private function getTotalNotifications(): int
    {
        // This would query your notifications table
        return 0; // Placeholder
    }

    /**
     * Get notifications count for today
     */
    private function getNotificationsToday(): int
    {
        // This would query today's notifications
        return 0; // Placeholder
    }

    /**
     * Get notifications count for this week
     */
    private function getNotificationsThisWeek(): int
    {
        // This would query this week's notifications
        return 0; // Placeholder
    }

    /**
     * Get notifications count for this month
     */
    private function getNotificationsThisMonth(): int
    {
        // This would query this month's notifications
        return 0; // Placeholder
    }

    /**
     * Get notification success rate
     */
    private function getNotificationSuccessRate(): float
    {
        // This would calculate actual success rate
        return 95.5; // Placeholder
    }

    /**
     * Get popular notification types
     */
    private function getPopularNotificationTypes(): array
    {
        return [
            'property_expiry' => 150,
            'daily_digest' => 120,
            'bot_alerts' => 45,
            'system_maintenance' => 15
        ]; // Placeholder
    }

    /**
     * Send notification with retry logic
     */
    public function sendWithRetry($recipient, string $type, array $data = [], int $maxRetries = 3): bool
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $notification = $this->createNotification($type, $data);
                $recipient->notify($notification);
                return true;
            } catch (\Exception $e) {
                $attempts++;
                
                if ($attempts >= $maxRetries) {
                    Log::error("Notification failed after {$maxRetries} attempts", [
                        'type' => $type,
                        'recipient' => get_class($recipient),
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
                
                // Wait before retry (exponential backoff)
                $waitTime = pow(2, $attempts) * 1000000; // microseconds
                usleep($waitTime);
            }
        }
        
        return false;
    }

    /**
     * Send scheduled notification
     */
    public function sendScheduledNotification(string $type, array $data, \DateTime $scheduledAt): bool
    {
        // This would schedule the notification for later delivery
        // For now, we'll send immediately
        return $this->sendToAdmins($type, $data);
    }

    /**
     * Cancel scheduled notification
     */
    public function cancelScheduledNotification(string $notificationId): bool
    {
        // This would cancel a scheduled notification
        return true; // Placeholder
    }

    /**
     * Get notification delivery status
     */
    public function getNotificationStatus(string $notificationId): array
    {
        return [
            'id' => $notificationId,
            'status' => 'delivered',
            'sent_at' => now()->toISOString(),
            'recipients' => 5,
            'deliveries' => 5,
            'failures' => 0
        ]; // Placeholder
    }
}
