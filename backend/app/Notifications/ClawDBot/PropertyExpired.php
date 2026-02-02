<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PropertyExpired extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\Property $property
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
        $expiryDate = $this->property->expires_at ? $this->property->expires_at->format('M j, Y') : 'Today';
        
        return (new MailMessage)
            ->subject('❌ Your Property Listing Has Expired')
            ->greeting("Hello {$notifiable->name},")
            ->line("We're writing to inform you that your property listing has expired:")
            ->line('')
            ->line("**Property Details:**")
            ->line("• Title: {$this->property->title}")
            ->line("• Location: {$this->property->location?->name ?? 'Not specified'}")
            ->line("• Price: $" . number_format($this->property->price, 2))
            ->line("• Expired: {$expiryDate}")
            ->line('')
            ->line("Your property is no longer visible to potential buyers. To reactivate your listing, please renew it as soon as possible.")
            ->line('')
            ->action('Reactivate Property', route('agent.properties.edit', $this->property->id))
            ->line('Thank you for using MyProperty-RL!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Property Expired',
            'message' => "Your property '{$this->property->title}' has expired and is no longer visible.",
            'type' => 'property_expired',
            'severity' => 'critical',
            'data' => [
                'property_id' => $this->property->id,
                'property_title' => $this->property->title,
                'expiry_date' => $this->property->expires_at?->toDateString(),
            ],
            'action_url' => route('agent.properties.edit', $this->property->id),
            'icon' => 'times-circle'
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
            'property' => $this->property,
        ];
    }
}
