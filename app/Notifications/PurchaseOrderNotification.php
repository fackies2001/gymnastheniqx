<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseOrderNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $poNumber;
    protected $approverName;
    protected $poId;

    /**
     * @param string $type - 'created' | 'completed'
     */
    public function __construct(string $type, string $poNumber, string $approverName, int $poId)
    {
        $this->type         = $type;
        $this->poNumber     = $poNumber;
        $this->approverName = $approverName;
        $this->poId         = $poId;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $messages = [
            'created'   => "🛒 Purchase Order {$this->poNumber} created by {$this->approverName}",
            'completed' => "📦 Purchase Order {$this->poNumber} has been completed",
        ];

        $icons = [
            'created'   => 'fas fa-shopping-cart text-success',
            'completed' => 'fas fa-box text-info',
        ];

        return [
            'type'          => 'purchase_order',
            'action'        => $this->type,
            'message'       => $messages[$this->type] ?? "Purchase Order {$this->poNumber} updated",
            'icon'          => $icons[$this->type] ?? 'fas fa-bell text-info',
            'po_id'         => $this->poId,
            'po_number'     => $this->poNumber,
            'approver_name' => $this->approverName,
            'url'           => route('purchase-order.index'),
        ];
    }
}
