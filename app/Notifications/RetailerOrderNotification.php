<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RetailerOrderNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $orderNumber;
    protected $retailerName;
    protected $orderId;

    /**
     * @param string $type - 'created' | 'approved' | 'rejected' | 'completed'
     */
    public function __construct(string $type, string $orderNumber, string $retailerName, int $orderId)
    {
        $this->type         = $type;
        $this->orderNumber  = $orderNumber;
        $this->retailerName = $retailerName;
        $this->orderId      = $orderId;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $messages = [
            'created'   => "🛍️ New Retailer Order {$this->orderNumber} from {$this->retailerName}",
            'approved'  => "✅ Retailer Order {$this->orderNumber} has been approved",
            'rejected'  => "❌ Retailer Order {$this->orderNumber} has been rejected",
            'completed' => "📦 Retailer Order {$this->orderNumber} has been completed",
        ];

        $icons = [
            'created'   => 'fas fa-shopping-bag text-primary',
            'approved'  => 'fas fa-check-circle text-success',
            'rejected'  => 'fas fa-times-circle text-danger',
            'completed' => 'fas fa-box-open text-success',
        ];

        return [
            'type'          => 'retailer_order',
            'action'        => $this->type,
            'message'       => $messages[$this->type] ?? "Retailer Order {$this->orderNumber} updated",
            'icon'          => $icons[$this->type] ?? 'fas fa-bell text-info',
            'order_id'      => $this->orderId,
            'order_number'  => $this->orderNumber,
            'retailer_name' => $this->retailerName,
            'url'           => route('retailer.orders.index'),
        ];
    }
}
