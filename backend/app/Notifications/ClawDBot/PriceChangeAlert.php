<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PriceChangeAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\Property $property,
        public float $priceChange,
        public string $alertType = 'decrease'
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
        $changeType = $this->priceChange > 0 ? 'increased' : 'decreased';
        $priceChangeFormatted = number_format(abs($this->priceChange), 2);
        $newPrice = number_format($this->property->price, 2);
        $oldPrice = number_format($this->property->price - $this->priceChange, 2);

        $subject = match($this->alertType) {
            'decrease' => '📉 Price Drop Alert - Great Deal Available!',
            'increase' => '📈 Price Update Alert',
            default => '💰 Price Change Alert'
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! There's been a price change for a property you're interested in:")
            ->line('')
            ->line("**Property Details:**")
            ->line("• Title: {$this->property->title}")
            ->line("• Location: {$this->property->location?->name ?? 'Not specified'}")
            ->line("• Category: {$this->property->category}")
            ->line('')
            ->line("**Price Change:**")
            ->line("• Previous Price: \${$oldPrice}")
            ->line("• New Price: \${$newPrice}")
            ->line("• Change: {$changeType} by \${$priceChangeFormatted}")
            ->line('')
            ->when($this->priceChange < 0, function ($message) {
                return $message->line('🎉 **This is a great opportunity!** The price has dropped, making it more affordable.')
                    ->line('💡 **Quick tip:** Properties with price drops often get more attention, so act fast!');
            })
            ->when($this->priceChange > 0, function ($message) {
                return $message->line('📈 The price has been updated to reflect current market conditions.')
                    ->line('💡 **Market insight:** Price increases often indicate high demand or improved property value.');
            })
            ->line('')
            ->action('View Property Details', route('properties.show', $this->property->id))
            ->line('Don\'t miss out on this opportunity!')
            ->line('Thank you for using MyProperty-RL!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $changeType = $this->priceChange > 0 ? 'increase' : 'decrease';
        $priceChangeFormatted = number_format(abs($this->priceChange), 2);

        return [
            'title' => match($this->alertType) {
                'decrease' => 'Price Drop Alert',
                'increase' => 'Price Update',
                default => 'Price Change Alert'
            },
            'message' => "Property '{$this->property->title}' price has {$changeType} by \${$priceChangeFormatted}",
            'type' => 'price_change',
            'severity' => $this->priceChange < 0 ? 'success' : 'info',
            'data' => [
                'property_id' => $this->property->id,
                'property_title' => $this->property->title,
                'old_price' => $this->property->price - $this->priceChange,
                'new_price' => $this->property->price,
                'price_change' => $this->priceChange,
                'change_type' => $changeType,
                'alert_type' => $this->alertType
            ],
            'action_url' => route('properties.show', $this->property->id),
            'icon' => $this->priceChange < 0 ? 'trending-down' : 'trending-up'
        ];
    }

    /**
     * Determine the notification's delivery delay.
     */
    public function delay(): ?\DateInterval
    {
        return new \DateInterval('PT0S'); // No delay for price alerts
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'property' => $this->property,
            'price_change' => $this->priceChange,
            'alert_type' => $this->alertType,
        ];
    }
}
