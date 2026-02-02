<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class WeeklyAnalyticsReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $reportData,
        public int $week,
        public int $year
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
        return (new MailMessage)
            ->subject("📊 Weekly Analytics Report - Week {$this->week}, {$this->year}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Here's your comprehensive weekly analytics report for Week {$this->week}, {$this->year}.")
            ->line("Period: {$this->reportData['period']['start_date']} to {$this->reportData['period']['end_date']}")
            ->line('')
            ->line('📈 **Key Metrics:**')
            ->line("• New Properties: {$this->reportData['properties']['new']}")
            ->line("• Total Enquiries: {$this->reportData['enquiries']['total']}")
            ->line("• New Users: {$this->reportData['users']['new']}")
            ->line("• Response Rate: {$this->reportData['enquiries']['response_rate']}%")
            ->line('')
            ->line('🏠 **Property Statistics:**')
            ->line("• Active Properties: {$this->reportData['properties']['active']}")
            ->line("• Average Price: $" . number_format($this->reportData['properties']['avg_price'] ?? 0, 2))
            ->line("• Total Value: $" . number_format($this->reportData['properties']['total_value'] ?? 0, 2))
            ->line('')
            ->action('View Full Dashboard', route('admin.dashboard'))
            ->line('Thank you for using MyProperty-RL!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Weekly Analytics Report - Week {$this->week}",
            'message' => "Weekly report generated with {$this->reportData['properties']['new']} new properties and {$this->reportData['enquiries']['total']} enquiries.",
            'type' => 'analytics',
            'data' => [
                'week' => $this->week,
                'year' => $this->year,
                'report_data' => $this->reportData
            ],
            'action_url' => route('admin.analytics'),
            'icon' => 'chart-bar'
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
            'report_data' => $this->reportData,
            'week' => $this->week,
            'year' => $this->year,
        ];
    }
}
