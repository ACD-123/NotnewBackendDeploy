<?php

namespace App\Notifications;

use App\Mail\BaseMailable;
use App\Models\Order;
use App\Models\Media;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Models\Prices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedSeller extends Notification
{
    use Queueable;

    protected  $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return
     */
    public function toMail($notifiable)
    {
        $baseMailable = new BaseMailable();
        $product = Product::where('id', $this->order->product_id)->with('user')->first();
        $media = Media::where
        ('product_id', $this->order->product_id)->with('user')->first();
        $shipping = ShippingDetail::where('id', $this->order->shipping_detail_id)->get();
        $prices = Prices::all();
        $totalprices = Order::Where(
            'id',$this->order->id
        )->get('prices');
        // $totalprices = json_decode($totalprices,TRUE);
        return $baseMailable->to($notifiable->email)
            ->subject($notifiable->name . '- Item Sold')
            ->markdown('emails.order.seller.placed', ['totalprices' => $totalprices, 'media' => $media,'prices' => $prices, 'user' => $notifiable, 'order' => $this->order, 'product' => $product, 'shipping' => $shipping]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
