<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Beneficiary;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BeneficiaryRemarriage extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Beneficiary $beneficiary, private User $user, private User $receiver)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title_en' => 'Remarriage',
            'title_bn' => 'পুনর্বিবাহ',
            'text_en' => "{$this->user->full_name} has updated the status of {$this->beneficiary->name_en}({$this->beneficiary->beneficiary_id}) to 'Remarriage'",
            'text_bn' => "{$this->user->full_name} {$this->beneficiary->name_bn}({$this->beneficiary->beneficiary_id}) এর স্ট্যাটাস 'পুনর্বিবাহ' হিসবে আপডেট করেছেন",
            'link' => "/beneficiary-management/beneficiary-info/details/{$this->beneficiary->id}",
            'beneficary_id' => $this->beneficiary->beneficiary_id,
            'user_id' => $this->user->id,
        ];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'beneficiary-remarriage';
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('notifications.' . $this->receiver->id),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->toArray($this->user);
    }

    public function broadcastAs()
    {
        return 'notifications';
    }
}
