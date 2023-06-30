<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class PasswordResetUserNotification extends Notification
{
    use Queueable;

    private string $token;
    /**
     * Create a new notification instance.
     */
    public function __construct(string $token )
    {
        $this->token = $token;
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
    public function toMail($request)//public function toMail(object $notifiable): MailMessage
    {
//        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
        $url = urldecode(url('reset-password', $this->token . '?email=' . $request->email));//パスワードリセットリンクを作る
        return (new BareMail)
            ->to($request->email)//送り先のメールアドレスがあるカラムを指定
            ->subject('【' . config('app.name') . '】パスワード再設定')
            ->text('emails.passwordreset', ['reset_url' => $url]);//bladeのファイルを指定
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
}

//パスワードリセットメールのカスタマイズのため追加
class BareMail extends Mailable {
    use Queueable, SerializesModels;
    public function build() {}
}
