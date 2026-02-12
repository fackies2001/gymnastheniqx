<?php $__env->startSection('subtitle', 'Supplier Products'); ?>
<?php $__env->startSection('content_header_title', 'Supplier Products'); ?>
<?php $__env->startSection('content_header_subtitle', 'All Supplier Products'); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan product to check details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <span class="col-auto">Track by SRN:</span>
                                <input class="col-auto" type="text" class="form-control" placeholder="Enter SRN"
                                    id="barcodeInput"
                                    style="border: none; border-bottom: 1px solid salmon; outline: none; box-shadow: none;">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="lottie-animation" style="width: 500px; height: 500px;"></div>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item disabled">How to check serialized product details</li>
                                    <li class="list-group-item">You can use input field/scanner</li>
                                    <li class="list-group-item">Once done it will redirect you to product overview</li>
                                    <li class="list-group-item">You can also change product status</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('js'); ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Make sure Lottie is loaded
                if (typeof lottie !== "undefined") {
                    var animation = lottie.loadAnimation({
                        container: document.getElementById('lottie-animation'), // the div
                        renderer: 'svg', // 'svg', 'canvas', 'html'
                        loop: true, // loop forever
                        autoplay: true, // start automatically
                        path: "<?php echo e(asset('images/Scanning.json')); ?>" // URL to JSON animation
                    });
                }

                $('#barcodeInput').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const code = $(this).val().trim();
                        $(this).val('');
                        console.log(code);

                        processBarcode(code);
                    }
                });

                function processBarcode(code) {
                    if (code.trim() === "") return;

                    // Linisin ang code sa anumang posibleng HTML tags kung galing ito sa copy-paste
                    const cleanCode = code.replace(/<\/?[^>]+(>|$)/g, "").trim();
                    const encoded_serial_number = encodeURIComponent(cleanCode);

                    // Siguraduhin na ang base URL ay tama
                    const url = "<?php echo e(url('serialized_products/overview')); ?>/" + encoded_serial_number;
                    window.location.href = url;
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/scan/index.blade.php ENDPATH**/ ?>