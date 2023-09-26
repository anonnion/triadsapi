<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentOnVideo extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $comment_user_id;
    private $video_id;
    public function __construct($comment_user_id, $video_id)
    {
        //
        $this->comment_user_id = $comment_user_id;
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
        $comment_user = \App\Models\User::where('id', $this->comment_user_id)->first();
        $video = \App\Models\Video::where('id', $this->video_id)->first();
        return [
            //
            [
                "type" => "newCommentOnVideo",
                "comment_user_id" => $this->comment_user_id,
                "video_id" => $this->video_id,
                "video" => $video,
                "timestamp" => time(),
                "message" => "{$comment_user->username} commented on your video: {$video->description}",

            ]
        ];
    }
}
