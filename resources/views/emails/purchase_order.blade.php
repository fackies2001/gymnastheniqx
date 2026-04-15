<!DOCTYPE html>
<html>
<head>
    <title>New Purchase Order</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #007bff; color: white; padding: 10px 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Purchase Order: {{ $po->po_number }}</h2>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $po->supplier->contact_person ?? $po->supplier->name ?? 'Supplier' }}</strong>,</p>
            <p>We are pleased to submit the following Purchase Order for your processing. Please find the details below:</p>

            <ul>
                <li><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($po->order_date)->format('M d, Y') }}</li>
                <li><strong>Estimated Delivery:</strong> {{ \Carbon\Carbon::parse($po->delivery_date)->format('M d, Y') }}</li>
                <li><strong>Payment Terms:</strong> {{ ucwords(str_replace('_', ' ', $po->payment_terms ?? 'cash_on_delivery')) }}</li>
                <li><strong>Requested By:</strong> {{ $po->requestedBy->full_name ?? 'Gymnastheniqx Corp.' }}</li>
            </ul>

            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $item)
                        <tr>
                            <td>{{ $item->supplierProduct->name ?? 'Unknown' }}</td>
                            <td>{{ $item->quantity_ordered }}</td>
                            <td>PHP {{ number_format($item->unit_cost, 2) }}</td>
                            <td>PHP {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold;">Grand Total:</td>
                        <td style="font-weight: bold; color: #28a745;">PHP {{ number_format($po->grand_total, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if($po->remarks)
                <p><strong>Remarks / Notes:</strong><br/>
                {{ $po->remarks }}</p>
            @endif

            <p>Please confirm receipt of this order. If you have any questions, feel free to reply to this email.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from Gymnastheniqx Inventory System.</p>
        </div>
    </div>
</body>
</html>
