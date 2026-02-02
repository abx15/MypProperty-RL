<?php

namespace App\Notifications\ClawDBot;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SuspiciousListingAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public \App\Models\Property $property,
        public array $suspiciousReasons
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
            ->subject('🚨 Suspicious Listing Alert - Immediate Review Required')
            ->greeting("Hello {$notifiable->name},")
            ->line('Our automated system has detected suspicious activity in a property listing that requires your immediate attention.')
            ->line('')
            ->line("**Suspicious Listing Details:**")
            ->line("• Property ID: {$this->property->id}")
            ->line("• Title: {$this->property->title}")
            ->line("• Owner: {$this->property->owner?->name ?? 'Unknown'}")
            ->line("• Price: $" . number_format($this->property->price, 2))
            ->line("• Location: {$this->property->location?->name ?? 'Not specified'}")
            ->line("• Listed: {$this->property->created_at->format('M j, Y')}")
            ->line('')
            ->line("**Suspicious Activity Detected:**")
            foreach ($this->suspiciousReasons as $reason) {
                $line("• ⚠️  {$reason}");
            }
            ->line('')
            ->line("**Recommended Actions:**')
            ->line("1. Review the listing immediately")
            ->line("2. Contact the property owner for verification")
            ->line("3. Remove or suspend the listing if confirmed suspicious")
            ->line("4. Report to security team if this appears to be fraud")
            ->line('')
            ->action('Review Listing Now', route('admin.properties.edit', $this->property->id))
            ->action('Suspend Listing', route('admin.properties.suspend', $this->property->id))
            ->line('⚠️  This alert requires immediate attention to maintain platform integrity.')
            ->line('Thank you for helping keep MyProperty-RL safe and trustworthy!');

        // Add admin-only warning
        if ($notifiable->hasRole('admin')) {
            return (new MailMessage)
                ->subject('🚨 CRITICAL: Suspicious Listing Alert - Immediate Action Required')
                ->greeting("Hello {$notifiable->name},")
                ->line('⚠️  **CRITICAL SECURITY ALERT** ⚠️')
                ->line('')
                ->line('Our ClawDBot system has detected highly suspicious activity that requires your immediate intervention:')
                ->line('')
                ->line("**Listing Details:**")
                ->line("• Property ID: {$this->property->id}")
                ->line("• Title: {$this->property->title}")
                ->line("• Owner: {$this->property->owner?->name ?? 'Unknown'} (ID: {$this->property->owner_id})")
                ->line("• Price: $" . number_format($this->property->price, 2))
                ->line("• IP Address: " . ($this->property->created_at->format('Y-m-d H:i:s'))) // Placeholder
                ->line('')
                ->line("**Suspicious Indicators:**")
                foreach ($this->suspiciousReasons as $reason) {
                    $line("• 🚨 {$reason}");
                }
                ->line('')
                ->line("**Immediate Actions Required:**')
                ->line("1. **URGENT**: Review listing within 1 hour")
                ->line("2. Verify owner identity and contact information")
                ->line("3. Check for duplicate or similar listings")
                ->line("4. Consider immediate suspension if high risk")
                ->line("5. Report to security team if fraud suspected")
                ->line('')
                ->action('🚨 Review Listing Now', route('admin.properties.edit', $this->property->id))
                ->action('⛔ Suspend Immediately', route('admin.properties.suspend', $this->property->id))
                ->action('📋 Security Report', route('admin.security.report', ['property_id' => $this->property->id]))
                ->line('')
                ->line('⚠️  This is a high-priority security alert. Please take immediate action.')
                ->line('Platform security and user trust depend on your prompt response.');
        }
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Suspicious Listing Alert',
            'message' => "Suspicious activity detected in property '{$this->property->title}'",
            'type' => 'security_alert',
            'severity' => 'critical',
            'data' => [
                'property_id' => $this->property->id,
                'property_title' => $this->property->title,
                'owner_id' => $this->property->owner_id,
                'suspicious_reasons' => $this->suspiciousReasons,
                'detected_at' => now()->toISOString(),
                'requires_immediate_action' => true
            ],
            'action_url' => route('admin.properties.edit', $this->property->id),
            'icon' => 'exclamation-triangle'
        ];
    }

    /**
     * Determine the notification's delivery delay.
     */
    public function delay(): ?\DateInterval
    {
        return new \DateInterval('PT0S'); // No delay for security alerts
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'property' => $this->property,
            'suspicious_reasons' => $this->suspiciousReasons,
        ];
    }
}
