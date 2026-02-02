<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ListingRemoved extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('🗑️ Your Property Listing Has Been Removed')
            ->greeting("Hello {$notifiable->name},")
            ->line('We regret to inform you that your property listing has been removed from our platform.')
            ->line('')
            ->line("**Property Details:**")
            ->line("• Title: {$this->property->title}")
            ->line("• Location: {$this->property->location?->name ?? 'Not specified'}")
            ->line("• Original Price: $" . number_format($this->property->price, 2))
            ->line("• Removal Date: " . now()->format('M j, Y'))
            ->line('')
            ->line('**Reason for Removal:**')
            ->line('Your property listing was removed because it has been expired for more than 90 days and was no longer active.')
            ->line('')
            ->line('**What can you do?**')
            ->line('• You can relist your property by creating a new listing')
            ->line('• Update your property information to make it more attractive')
            ->line('• Consider adjusting the price to be more competitive')
            ->line('')
            ->action('Create New Listing', route('agent.properties.create'))
            ->line('If you believe this was done in error, please contact our support team.')
            ->line('Thank you for using MyProperty-RL!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Property Listing Removed',
            'message' => "Your property '{$this->property->title}' has been removed from the platform.",
            'type' => 'listing_removed',
            'severity' => 'warning',
            'data' => [
                'property_id' => $this->property->id,
                'property_title' => $this->property->title,
                'removal_date' => now()->toDateString(),
                'reason' => 'Expired for 90+ days'
            ],
            'action_url' => route('agent.properties.create'),
            'icon' => 'trash'
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
