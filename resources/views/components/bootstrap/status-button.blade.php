<div>
    <!-- The whole future lies in uncertainty: live immediately. - Seneca -->

    @if (in_array($status->id, [2, 3]))
        <a class="btn btn-sm btn-primary"
            href="{{ auth()->user()->is_student ? route('purchase_orders.patch_status', ['id' => $po_id, 'status' => 5]) : route('purchase_orders.create_email', ['id' => $status]) }}">
            Make Order
        </a>
    @elseif (in_array($status->id, [4, 5]))
        {{-- for delivered or completed order --}}
        <p class="m-0 text-success font-weight-bolder text-uppercase">To receive</p>
    @endif
</div>
