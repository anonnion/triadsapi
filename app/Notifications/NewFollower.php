<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $follower_id;
    public function __construct($follower_id)
    {
        //
        $this->follower_id = $follower_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
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
            //
        ];
    }
    public function toDatabase(object $notifiable): array
    {
        $follower = \App\Models\User::where('id', $this->follower_id)->first();
        return [
            //
            [
                "type" => "newFollower",
                "follower_id" => $this->follower_id,
                "timestamp" => time(),
                "message" => "{$follower->username} follows you",

            ]
        ];
    }
}
