@extends('layouts.adminlte')

@section('subtitle', 'Serialized Product Overview')
@section('content_header_title', 'Products')
@section('content_header_subtitle', 'Serialized Overview')

@section('content_body')
    <div class="container-fluid">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('serialized_products._index') }}" class="btn btn-default shadow-sm">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="row">
            <!-- LEFT COLUMN: Product Image & Barcode -->
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-navy">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box-open mr-2"></i> GYMNASTHENIQA PRODUCT
                        </h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <!-- Product Image -->
                        <div class="mb-4">
                            <img src="{{ $productImage }}" alt="{{ $serialized_product_details->supplierProducts->name }}"
                                class="img-fluid rounded shadow"
                                style="max-width: 100%; height: auto; max-height: 400px; object-fit: contain;">
                        </div>

                        <!-- Product Name -->
                        <h2 class="text-uppercase font-weight-bold mb-4">
                            {{ $serialized_product_details->supplierProducts->name }}
                        </h2>

                        <!-- SKU Info -->
                        <div class="alert alert-info">
                            <strong>SKU:</strong> {{ $serialized_product_details->supplierProducts->system_sku ?? 'N/A' }}
                        </div>

                        <!-- Barcode Display -->
                        <div class="card bg-white border-0 shadow-sm mt-4 p-3">
                            <h5 class="text-muted mb-3"> SERIAL NUMBER </h5>

                            <!-- Barcode Image -->
                            <div class="mb-3">
                                <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode" class="img-fluid"
                                    style="max-width: 100%; height: auto;">
                            </div>

                            <!-- Serial Number Text -->
                            <h4 class="font-weight-bold text-primary">
                                {{ $serial_number }}
                            </h4>

                            <!-- Traceability ID -->
                            <p class="text-muted small mt-2 mb-0">
                                Traceability ID: <strong>#TRK-{{ $serialized_product_details->id }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Product Details -->
            <div class="col-md-6">
                <!-- Acquisition & Source Details -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary mr-2"></i> ACQUISITION & SOURCE DETAILS
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="40%">SUPPLIER / SOURCE</th>
                                <td>
                                    <strong>{{ $serialized_product_details->supplierProducts->supplier->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $serialized_product_details->supplierProducts->supplier->address ?? 'No address' }}
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>Supplier SKU:</th>
                                <td>
                                    <span class="badge badge-secondary px-3 py-2">
                                        {{ $serialized_product_details->supplierProducts->supplier_sku ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Cost:</th>
                                <td>
                                    <strong class="text-success" style="font-size: 1.2rem;">
                                        ₱{{ number_format($serialized_product_details->supplierProducts->cost_price ?? 0, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <!-- PURCHASE DETAILS -->
                        <hr>
                        <h6 class="text-uppercase text-muted mb-3">Purchase Details</h6>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="40%">PO:</th>
                                <td>
                                    <a
                                        href="{{ route('purchase-order.show', $serialized_product_details->purchase_order_id) }}">
                                        PO-{{ $serialized_product_details->purchaseOrder->po_number ?? 'N/A' }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Order Date:</th>
                                <td>{{ $serialized_product_details->purchaseOrder->order_date ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Delivery:</th>
                                <td>{{ $serialized_product_details->purchaseOrder->delivery_date ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Internal Traceability -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-warehouse text-warning mr-2"></i> INTERNAL TRACEABILITY
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="40%">Scanned By</th>
                                <td>
                                    <i class="fas fa-user-circle text-primary mr-2"></i>
                                    <strong>{{ $serialized_product_details->scannedBy->full_name ?? 'N/A' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>
                                    <i class="fas fa-building text-info mr-2"></i>
                                    <strong>{{ $serialized_product_details->warehouse->name ?? 'Main Storage' }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @php
                                        $status = $serialized_product_details->productStatus;
                                        $statusColors = [
                                            1 => 'success', // Available
                                            2 => 'warning', // Reserved
                                            3 => 'danger', // Sold
                                            4 => 'dark', // Missing
                                            5 => 'secondary', // Damaged
                                        ];
                                        $color = $statusColors[$status->id ?? 1] ?? 'secondary';
                                    @endphp
                                    {{-- ✅ ADDED ID for dynamic update --}}
                                    <span id="current-status-badge" class="badge badge-{{ $color }} px-3 py-2"
                                        style="font-size: 1rem;">
                                        <i class="fas fa-circle mr-1"></i>
                                        <span id="status-text">{{ $status->name ?? 'Unknown' }}</span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Serial Number</th>
                                <td>
                                    <i class="fas fa-barcode text-secondary mr-2"></i>
                                    <strong class="font-monospace d-block mb-2">{{ $serial_number }}</strong>
                                    {{-- ✅ Barcode Image for Scanner Gun --}}
                                    <div class="mt-2">
                                        <img src="data:image/png;base64,{{ $barcodeImage }}" alt="Barcode"
                                            class="img-fluid" style="max-width: 250px; height: auto;">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Update Status Button -->
                <div class="mt-3">
                    <button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#updateStatusModal">
                        <i class="fas fa-edit mr-2"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i> Update Product Status
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                {{-- ✅ REMOVED action and method attributes - will use AJAX instead --}}
                <form id="updateStatusForm">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="status_id">New Status</label>
                            <select name="status_id" id="status_id" class="form-control" required>
                                <option value="">-- Select Status --</option>
                                <option value="1"
                                    {{ $serialized_product_details->product_status_id == 1 ? 'selected' : '' }}>
                                    Available
                                </option>
                                <option value="2"
                                    {{ $serialized_product_details->product_status_id == 2 ? 'selected' : '' }}>
                                    Reserved
                                </option>
                                <option value="3"
                                    {{ $serialized_product_details->product_status_id == 3 ? 'selected' : '' }}>
                                    Sold
                                </option>
                                <option value="4"
                                    {{ $serialized_product_details->product_status_id == 4 ? 'selected' : '' }}>
                                    Missing
                                </option>
                                {{-- ✅ ADDED Damaged option --}}
                                <option value="5"
                                    {{ $serialized_product_details->product_status_id == 5 ? 'selected' : '' }}>
                                    Damaged
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="remarks">Remarks (Optional)</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter any notes...">{{ $serialized_product_details->remarks ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        {{-- ✅ CHANGED to type="button" to prevent form submission --}}
                        <button type="button" id="saveStatusBtn" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .card {
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header {
            border-radius: 15px 15px 0 0;
        }

        .badge {
            border-radius: 8px;
        }

        /* ✅ Loading state styles */
        .btn-loading {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            // ✅ Status color mapping
            const statusColors = {
                1: {
                    badge: 'success',
                    name: 'Available'
                },
                2: {
                    badge: 'warning',
                    name: 'Reserved'
                },
                3: {
                    badge: 'danger',
                    name: 'Sold'
                },
                4: {
                    badge: 'dark',
                    name: 'Missing'
                },
                5: {
                    badge: 'secondary',
                    name: 'Damaged'
                }
            };

            // ✅ AJAX Update Status Handler - Remove existing handler first to prevent double trigger
            $('#saveStatusBtn').off('click').on('click', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const $form = $('#updateStatusForm');
                const statusId = $('#status_id').val();
                const remarks = $('#remarks').val();

                // Validation
                if (!statusId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops!',
                        text: 'Please select a status.',
                        confirmButtonColor: '#007bff'
                    });
                    return;
                }

                // Show loading state
                $btn.addClass('btn-loading').html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');

                // ✅ AJAX Request
                $.ajax({
                    url: "{{ route('serialized_products.update_status', $serialized_product_details->id) }}",
                    type: 'PUT',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status_id: statusId,
                        remarks: remarks
                    },
                    success: function(response) {
                        if (response.success) {
                            // ✅ Update UI without reload
                            const newStatus = statusColors[statusId];
                            const $badge = $('#current-status-badge');

                            // Remove all badge color classes
                            $badge.removeClass(
                                'badge-success badge-warning badge-danger badge-dark badge-secondary'
                                );

                            // Add new color class
                            $badge.addClass('badge-' + newStatus.badge);

                            // Update status text
                            $('#status-text').text(newStatus.name);

                            // Close modal
                            $('#updateStatusModal').modal('hide');

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message ||
                                    'Status updated successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Reset button
                            $btn.removeClass('btn-loading').html(
                                '<i class="fas fa-save mr-2"></i> Save Changes');
                        } else {
                            throw new Error(response.message || 'Update failed');
                        }
                    },
                    error: function(xhr) {
                        console.error('Update error:', xhr);

                        let errorMessage = 'Failed to update status. Please try again.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            confirmButtonColor: '#dc3545'
                        });

                        // Reset button
                        $btn.removeClass('btn-loading').html(
                            '<i class="fas fa-save mr-2"></i> Save Changes');
                    }
                });
            });

            // ✅ Reset form when modal closes
            $('#updateStatusModal').on('hidden.bs.modal', function() {
                $('#updateStatusForm')[0].reset();
                $('#saveStatusBtn').removeClass('btn-loading').html(
                    '<i class="fas fa-save mr-2"></i> Save Changes');
            });
        });
    </script>
@endpush
