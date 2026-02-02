<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PropertyExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\Property $property,
        public string $alertType = 'warning'
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
        $expiryDate = $this->property->expires_at ? $this->property->expires_at->format('M j, Y') : 'Not set';
        $daysUntilExpiry = $this->property->expires_at ? now()->diffInDays($this->property->expires_at) : 0;
        
        $subject = $this->getSubject();
        $urgencyText = $this->getUrgencyText($daysUntilExpiry);
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("This is an important notification regarding your property listing:")
            ->line('')
            ->line("**Property Details:**")
            ->line("• Title: {$this->property->title}")
            ->line("• Location: {$this->property->location?->name ?? 'Not specified'}")
            ->line("• Price: $" . number_format($this->property->price, 2))
            ->line("• Expires: {$expiryDate}")
            ->line('')
            ->line($urgencyText)
            ->line('')
            ->action('Manage Property', route('agent.properties.edit', $this->property->id))
            ->line('Thank you for using MyProperty-RL!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $daysUntilExpiry = $this->property->expires_at ? now()->diffInDays($this->property->expires_at) : 0;
        
        return [
            'title' => $this->getTitle(),
            'message' => $this->getMessage($daysUntilExpiry),
            'type' => 'property_expiry',
            'severity' => $this->alertType,
            'data' => [
                'property_id' => $this->property->id,
                'property_title' => $this->property->title,
                'expiry_date' => $this->property->expires_at?->toDateString(),
                'days_until_expiry' => $daysUntilExpiry,
                'alert_type' => $this->alertType
            ],
            'action_url' => route('agent.properties.edit', $this->property->id),
            'icon' => $this->getIcon()
        ];
    }

    /**
     * Get email subject based on alert type.
     */
    private function getSubject(): string
    {
        return match($this->alertType) {
            'critical' => '🚨 Critical: Your Property Listing Expires Soon!',
            'warning' => '⚠️  Reminder: Your Property Listing is Expiring',
            'expired' => '❌ Your Property Listing Has Expired',
            'inactive' => '💤 Your Property Listing is Now Inactive',
            default => '🤖 Property Listing Status Update'
        };
    }

    /**
     * Get urgency text based on days until expiry.
     */
    private function getUrgencyText(int $days): string
    {
        return match($this->alertType) {
            'critical' => "🚨 **CRITICAL:** Your property listing expires in {$days} days! Please renew it soon to avoid interruption.",
            'warning' => "⚠️ **REMINDER:** Your property listing expires in {$days} days. Consider renewing to maintain visibility.",
            'expired' => "❌ **EXPIRED:** Your property listing expired today. It's no longer visible to potential buyers.",
            'inactive' => "💤 **INACTIVE:** Your property listing has been marked as inactive due to lack of activity.",
            default => "📋 **UPDATE:** Your property listing status has been updated."
        };
    }

    /**
     * Get notification title.
     */
    private function getTitle(): string
    {
        return match($this->alertType) {
            'critical' => 'Critical: Property Expiring Soon',
            'warning' => 'Property Expiry Reminder',
            'expired' => 'Property Expired',
            'inactive' => 'Property Inactive',
            default => 'Property Status Update'
        };
    }

    /**
     * Get notification message.
     */
    private function getMessage(int $days): string
    {
        return match($this->alertType) {
            'critical' => "Your property '{$this->property->title}' expires in {$days} days!",
            'warning' => "Your property '{$this->property->title}' expires in {$days} days.",
            'expired' => "Your property '{$this->property->title}' has expired.",
            'inactive' => "Your property '{$this->property->title}' is now inactive.",
            default => "Your property '{$this->property->title}' status has been updated."
        };
    }

    /**
     * Get notification icon.
     */
    private function getIcon(): string
    {
        return match($this->alertType) {
            'critical' => 'exclamation-triangle',
            'warning' => 'exclamation-circle',
            'expired' => 'times-circle',
            'inactive' => 'pause-circle',
            default => 'home'
        };
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
            'property' => $this->property,
            'alert_type' => $this->alertType,
        ];
    }
}
