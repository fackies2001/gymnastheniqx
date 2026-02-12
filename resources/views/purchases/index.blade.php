@extends('adminlte::page')

@section('subtitle', 'Purchase Order')
@section('content_header_title', 'Purchase Order')

{{-- 1. Dito ang Livewire Styles para hindi masira ang layout --}}
@section('adminlte_css')
    @livewireStyles
    @stack('css')
@stop

@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="container">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title mb-0" style="letter-spacing: 0.1ch; text-transform: uppercase;" id="title_emp">
                            <h3 class="my-4">Create Purchase Order</h3>
                        </div>
                    </div>

                    <form id="purchaseForm" method="POST" action="{{ route('purchases.store') }}">
                        <div class="card-body">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <x-bootstrap.label for="po_number" value="Purchase Order Number:" />
                                    <x-bootstrap.input id="po_number" name="po_number" required
                                        placeholder="Enter Purchase Order" />
                                    <x-bootstrap.input-error :messages="$errors->get('po_number')" />
                                </div>
                            </div>

                            {{-- 🟢 LIVEWIRE COMPONENT HERE --}}
                            <livewire:suppliers-products />

                            <hr>

                            <h5>Products</h5>
                            <table class="table table-bordered" id="productsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit Cost</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dito papasok ang mga rows via JavaScript --}}
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-sm btn-outline-primary" id="addProductRow">
                                <i class="fas fa-plus"></i> Add Product
                            </button>

                            <div class="mt-4 text-right">
                                <h5>Total: ₱<span id="total">0.00</span></h5>
                            </div>

                            <div class="mt-4">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="mt-4 d-flex">
                                <button type="submit" class="btn btn-success ml-auto">Save Purchase Order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- 2. Dito ang Livewire Scripts at ang iyong Custom JS --}}
@section('adminlte_js')
    @livewireScripts
    @stack('js')

    <script>
        $(document).ready(function() {
            // Function para mag-calculate ng subtotal at total
            function calculateTotals() {
                let grandTotal = 0;
                $('.subtotal-input').each(function() {
                    let val = parseFloat($(this).val()) || 0;
                    grandTotal += val;
                });
                $('#total').text(grandTotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
            }

            // Kapag clinick ang Add Product button
            $('#addProductRow').on('click', function() {
                // Kunin natin ang data mula sa dropdown sa loob ng Livewire component
                const productSelect = $('#product_dropdown_lookup'); // Ito yung ID sa livewire view
                const productId = productSelect.val();
                const productName = productSelect.find(':selected').data('name');
                const productPrice = productSelect.find(':selected').data('price');

                if (!productId) {
                    alert('Please select a product first.');
                    return;
                }

                // Check kung existing na sa table
                let exists = false;
                $('input[name="product_id[]"]').each(function() {
                    if ($(this).val() == productId) exists = true;
                });

                if (exists) {
                    alert('Product already added.');
                    return;
                }

                // Mag-append ng bagong row sa table
                const newRow = `
                    <tr>
                        <td>
                            ${productName}
                            <input type="hidden" name="product_id[]" value="${productId}">
                        </td>
                        <td>
                            <input type="number" name="unit_cost[]" class="form-control cost-input" value="${productPrice}" step="0.01" readonly>
                        </td>
                        <td>
                            <input type="number" name="quantity[]" class="form-control qty-input" value="1" min="1">
                        </td>
                        <td>
                            <input type="number" name="subtotal[]" class="form-control subtotal-input" value="${productPrice}" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#productsTable tbody').append(newRow);
                calculateTotals();
            });

            // Update subtotal kapag binago ang quantity
            $(document).on('input', '.qty-input', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val()) || 0;
                const cost = parseFloat(row.find('.cost-input').val()) || 0;
                const subtotal = qty * cost;
                row.find('.subtotal-input').val(subtotal.toFixed(2));
                calculateTotals();
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });
        });
    </script>
@stop
