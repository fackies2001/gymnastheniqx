<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseRequestNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $prNumber;
    protected $requesterName;
    protected $prId;

    /**
     * @param string $type - 'created' | 'approved' | 'rejected'
     */
    public function __construct(string $type, string $prNumber, string $requesterName, int $prId)
    {
        $this->type          = $type;
        $this->prNumber      = $prNumber;
        $this->requesterName = $requesterName;
        $this->prId          = $prId;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $messages = [
            'created'  => "📋 New Purchase Request {$this->prNumber} submitted by {$this->requesterName}",
            'approved' => "✅ Your Purchase Request {$this->prNumber} has been approved",
            'rejected' => "❌ Your Purchase Request {$this->prNumber} has been rejected",
        ];

        $icons = [
            'created'  => 'fas fa-file-alt text-primary',
            'approved' => 'fas fa-check-circle text-success',
            'rejected' => 'fas fa-times-circle text-danger',
        ];

        return [
            'type'           => 'purchase_request',
            'action'         => $this->type,
            'message'        => $messages[$this->type] ?? "Purchase Request {$this->prNumber} updated",
            'icon'           => $icons[$this->type] ?? 'fas fa-bell text-info',
            'pr_id'          => $this->prId,
            'pr_number'      => $this->prNumber,
            'requester_name' => $this->requesterName,
            'url'            => route('pr.index'),
        ];
    }
}
