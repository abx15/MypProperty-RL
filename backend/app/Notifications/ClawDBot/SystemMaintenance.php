<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SystemMaintenance extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $message,
        public \DateTime $scheduledAt
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $scheduledTime = $this->scheduledAt->format('l, F j, Y \a\t g:i A');
        $timeUntil = $this->getTimeUntilMaintenance();

        return (new MailMessage)
            ->subject('🔧 System Maintenance Scheduled - MyProperty-RL')
            ->greeting("Hello {$notifiable->name},")
            ->line('We wanted to inform you about scheduled system maintenance for MyProperty-RL.')
            ->line('')
            ->line("**Maintenance Details:**')
            ->line("• Scheduled Time: {$scheduledTime}")
            ->line("• Duration: {$timeUntil}")
            ->line("• Reason: {$this->message}")
            ->line('')
            ->line("**What to Expect:**")
            ->line('• During maintenance, some features may be temporarily unavailable')
            ->line('• Property listings will remain visible to users')
            ->line('• Existing enquiries and communications will not be affected')
            ->line('• System performance may be temporarily slower')
            ->line('')
            ->line("**Impact on Your Account:**')
            ->line('• You can still browse and view properties')
            ->line('• Creating new listings may be temporarily disabled')
            ->line('• Enquiry responses may experience slight delays')
            ->line('• Dashboard analytics may not update in real-time')
            ->line('')
            ->line("**After Maintenance:**')
            ->line('• All features will be fully restored')
            ->line('• System performance should be improved')
            ->line('• New features and enhancements may be available')
            ->line('')
            ->action('View System Status', route('status'))
            ->line('We apologize for any inconvenience and appreciate your patience.')
            ->line('Thank you for being a valued member of MyProperty-RL!');

        // Add admin-specific information
        if ($notifiable->hasRole('admin')) {
            return (new MailMessage)
                ->subject('🔧 ADMIN: System Maintenance Scheduled - MyProperty-RL')
                ->greeting("Hello {$notifiable->name},")
                ->line('🔧 **SYSTEM MAINTENANCE NOTIFICATION**')
                ->line('')
                ->line('This is an administrative notification regarding scheduled system maintenance.')
                ->line('')
                ->line("**Maintenance Schedule:**")
                ->line("• Start Time: {$scheduledTime}")
                ->line("• Duration: {$timeUntil}")
                ->line("• Purpose: {$this->message}")
                ->line('')
                ->line("**Administrative Actions Required:**')
                ->line('1. Monitor system performance during maintenance window')
                ->line('2. Verify all services are restored after completion')
                ->line('3. Check for any failed processes or errors')
                ->line('4. Update maintenance logs and documentation')
                ->line('5. Notify users of completed maintenance')
                ->line('')
                ->line("**Expected Impact:**')
                ->line('• Database optimization may slow down queries')
                ->line('• Cache clearing may temporarily affect performance')
                ->line('• Queue processing may be paused temporarily')
                ->line('• Background jobs may be delayed')
                ->line('')
                ->action('🔧 Monitor Maintenance', route('admin.maintenance.monitor'))
                ->action('📊 View System Status', route('admin.system.status'))
                ->action('📋 Maintenance Logs', route('admin.maintenance.logs'))
                ->line('')
                ->line('Please ensure all critical processes are backed up before maintenance begins.')
                ->line('Contact the development team if any issues arise during maintenance.');
        }
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'System Maintenance Scheduled',
            'message' => "System maintenance scheduled for {$this->scheduledAt->format('Y-m-d H:i:s')}: {$this->message}",
            'type' => 'system_maintenance',
            'severity' => 'info',
            'data' => [
                'message' => $this->message,
                'scheduled_at' => $this->scheduledAt->toISOString(),
                'time_until' => $this->getTimeUntilMaintenance(),
                'maintenance_type' => 'scheduled'
            ],
            'action_url' => route('status'),
            'icon' => 'cog'
        ];
    }

    /**
     * Determine the notification's delivery delay.
     */
    public function delay(): ?\DateInterval
    {
        return new \DateInterval('PT0S'); // No delay
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'scheduled_at' => $this->scheduledAt,
        ];
    }

    /**
     * Get time until maintenance in human readable format
     */
    private function getTimeUntilMaintenance(): string
    {
        $now = now();
        $maintenance = \Carbon\Carbon::instance($this->scheduledAt);
        
        if ($maintenance->isPast()) {
            return 'Maintenance completed';
        }
        
        $diff = $now->diff($maintenance);
        
        if ($diff->days > 0) {
            return "{$diff->days} days, {$diff->h} hours";
        } elseif ($diff->h > 0) {
            return "{$diff->h} hours, {$diff->i} minutes";
        } elseif ($diff->i > 0) {
            return "{$diff->i} minutes";
        } else {
            return "Less than a minute";
        }
    }
}
