<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class DailyPropertyDigest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $summaryData,
        public \Carbon\Carbon $targetDate
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
        $dateFormatted = $this->targetDate->format('l, F j, Y');
        
        return (new MailMessage)
            ->subject("📋 Daily Property Digest - {$dateFormatted}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Here's your daily property summary for {$dateFormatted}.")
            ->line('')
            ->line('📊 **Daily Overview:**')
            ->line("• New Properties: {$this->summaryData['new_properties']}")
            ->line("• New Enquiries: {$this->summaryData['new_enquiries']}")
            ->line("• New Users: {$this->summaryData['new_users']}")
            ->line("• Active Properties: {$this->summaryData['active_properties']}")
            ->line('')
            ->line('📈 **Platform Statistics:**')
            ->line("• Total Properties: {$this->summaryData['total_properties']}")
            ->line("• Total Users: {$this->summaryData['total_users']}")
            ->line('')
            ->action('View Dashboard', route('admin.dashboard'))
            ->line('Have a great day!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Daily Property Digest - {$this->targetDate->format('M j')}",
            'message' => "Daily summary: {$this->summaryData['new_properties']} new properties, {$this->summaryData['new_enquiries']} enquiries.",
            'type' => 'daily_digest',
            'data' => [
                'date' => $this->targetDate->toDateString(),
                'summary_data' => $this->summaryData
            ],
            'action_url' => route('admin.dashboard'),
            'icon' => 'document-text'
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
            'summary_data' => $this->summaryData,
            'target_date' => $this->targetDate->toDateString(),
        ];
    }
}
