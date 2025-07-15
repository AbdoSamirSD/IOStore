<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use App\Models\Notification as NotificationModel;

class NewOfferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $banner;

    public function __construct($banner)
    {
        $this->banner = $banner;
        NotificationModel::create([
            'notifiable_id' => null,
            'notifiable_type' => null,
            'title' => 'New Offer',
            'message' => 'New offer on ' . $this->banner->product->name . ' with ' . $this->banner->product->discount . '% discount.',
            'data' => $this->banner->product,
            'type' => 'offer'
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('New offer available: ' . $this->banner->product->name)
            ->line('Thank you for using our application!');
    }
    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setTopic('offers')
            ->setData([
                'banner_id' => $this->banner->id,
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle('New Offer!',)
                    ->setBody($this->banner->product->name . ' now has a ' . $this->banner->product->discount . '% discount!',)
                // ->setImage($this->image)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
                    ->setNotification(AndroidNotification::create()->setColor('#0A0A0A'))

            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'title' => 'New Offer',
            'message' => 'New offer on ' . $this->banner->product->name . ' with ' . $this->banner->product->discount . '% discount.',
            'data' => $this->banner->product,
            'type' => 'offer'
        ];
    }
}
