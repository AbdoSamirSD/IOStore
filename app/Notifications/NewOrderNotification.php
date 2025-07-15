<?php

namespace App\Notifications;

use App\Events\NewOrderEvent;
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

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
        // $order->user->notifications()->create($this->toArray());
        NotificationModel::create([
            'notifiable_id' => $order->user_id,
            'notifiable_type' => 'App\Models\User',
            'title' => 'New Order',
            'message' => 'You have a new order #' . $order->order_number,
            'data' => json_encode([
                'order_id' => $order->id,
                'status' => $order->status
            ]),
            'type' => 'order',
        ]);
        event(new NewOrderEvent($this->order));
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('A new order has been placed: #' . $this->order->order_number)
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for shopping with us!');
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
                    ->setTitle('New Order')
                    ->setBody('Order #' . $this->order->order_number . ' has been placed',)
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

    // public function toArray()
    // {
    //     return [
    //         'title' => 'New Order',
    //         'message' => 'A new order has been placed: #' . $this->order->order_number,
    //         'data' => $this->order,
    //         'type' => 'new_order',
    //     ];
    // }
}
