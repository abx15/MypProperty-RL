<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BotStatusAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $alertTitle,
        public string $alertMessage,
        public string $severity = 'warning'
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
        $severityIcon = $this->getSeverityIcon();
        $severityColor = $this->getSeverityColor();
        
        return (new MailMessage)
            ->subject("{$severityIcon} ClawDBot Alert: {$this->alertTitle}")
            ->greeting("Hello {$notifiable->name},")
            ->line("ClawDBot has generated an alert that requires your attention:")
            ->line('')
            ->line("**{$this->alertTitle}**")
            ->line($this->alertMessage)
            ->line('')
            ->line("**Severity:** {$severityColor} {$this->severity}")
            ->line("**Time:** " . now()->format('Y-m-d H:i:s'))
            ->line('')
            ->action('View Bot Status', route('admin.clawdbot.status'))
            ->line('Please review this alert and take appropriate action.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->alertTitle,
            'message' => $this->alertMessage,
            'type' => 'bot_alert',
            'severity' => $this->severity,
            'data' => [
                'alert_title' => $this->alertTitle,
                'alert_message' => $this->alertMessage,
                'severity' => $this->severity,
                'timestamp' => now()->toISOString()
            ],
            'action_url' => route('admin.clawdbot.status'),
            'icon' => $this->getSeverityIcon()
        ];
    }

    /**
     * Get severity icon.
     */
    private function getSeverityIcon(): string
    {
        return match($this->severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            'success' => '✅',
            default => '🤖'
        };
    }

    /**
     * Get severity color.
     */
    private function getSeverityColor(): string
    {
        return match($this->severity) {
            'critical' => '🔴',
            'warning' => '🟡',
            'info' => '🔵',
            'success' => '🟢',
            default => '⚪'
        };
    }

    /**
     * Determine the notification's delivery delay.
     */
    public function delay(): ?\DateInterval
    {
        return new \DateInterval('PT0S'); // No delay for alerts
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_title' => $this->alertTitle,
            'alert_message' => $this->alertMessage,
            'severity' => $this->severity,
        ];
    }
}
