<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVideoFromFollowing extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $following_id;
    private $video_id;
    public function __construct($following_id, $video_id)
    {
        //
        $this->following_id = $following_id;
        $this->video_id = $video_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
        $f = \App\Models\User::where('id', $this->following_id)->first();
        $video = \App\Models\Video::where('id', $this->video_id)->first();
        return [
            //
            [
                "type" => "newVideoFromFollowing",
                "following_id" => $this->following_id,
                "video_id" => $this->video_id,
                "video" => $video,
                "timestamp" => time(),
                "message" => "{$f->username} that you follow uploaded a new video: {$video->description}",

            ]
        ];
    }
}
