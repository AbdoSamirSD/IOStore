<?php

namespace App\Notifications;

use App\Models\Order;
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

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct($order)
    {
        $this->order = $order;

        // $order->user->notifications()->create($this->toArray());

        NotificationModel::create([
            'notifiable_id' => $order->user_id,
            'notifiable_type' => 'App\Models\User',
            'title' => 'Order Status Changed',
            'message' => 'Order #' . $order->order_number . ' status changed to ' . $order->status,
            'data' => json_encode([
                'order_id' => $order->id,
                'status' => $order->status
            ]),
            'type' => 'order_status_changed',
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
            ->line('Order #' . $this->order->order_number . ' status has changed to ' . $this->order->status)
            // ->action('Notification Action', url('/'))
            ->line('Order ID: ' . $this->order->id)
            ->line('Status: ' . $this->order->status)
            ->line('Thank you for using our application!');
    }
    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData([
                'order_id' => $this->order->id,
                'status' => $this->order->status,
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle('Order Status Updated',)
                    ->setBody('Your order #' . $this->order->order_number . ' is now ' . $this->order->status,)
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
    public function toArray()
    {
        return [
            'title' => 'Order Status Changed',
            'message' => 'Order #' . $this->order->order_number . ' status changed to ' . $this->order->status,
            'data' => $this->order,
            'type' => 'order_status_changed',

        ];
    }
}
