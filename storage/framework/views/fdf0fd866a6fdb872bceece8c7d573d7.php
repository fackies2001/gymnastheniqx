<?php $__env->startSection('subtitle', 'User Management'); ?>
<?php $__env->startSection('content_header_title', 'User Management'); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title text-uppercase" style="letter-spacing: 0.2em;">Employees</h3>
                    <button class="btn btn-sm btn-primary ml-auto" id="create_employee">Create Employee</button>
                </div>
                <div class="card-body">
                    <table id="sampleId" class="table table-bordered w-100">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Photo</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Date Hired</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->profile_photo): ?>
                                            <?php
                                                $photoPath = storage_path('app/public/' . $employee->profile_photo);
                                            ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists($photoPath)): ?>
                                                <img src="<?php echo e(asset('storage/' . $employee->profile_photo)); ?>" width="40"
                                                    height="40" class="img-circle border shadow-sm"
                                                    style="object-fit: cover;"
                                                    onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode(substr($employee->full_name, 0, 1))); ?>&background=6777ef&color=fff';">
                                            <?php else: ?>
                                                <div class="img-circle bg-secondary d-inline-block text-center shadow-sm"
                                                    style="width:40px; height:40px; line-height:40px;">
                                                    <?php echo e(substr($employee->full_name, 0, 1)); ?>

                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <div class="img-circle bg-secondary d-inline-block text-center shadow-sm"
                                                style="width:40px; height:40px; line-height:40px;">
                                                <?php echo e(substr($employee->full_name, 0, 1)); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td><?php echo e($employee->full_name); ?></td>

                                    
                                    <td><?php echo e($employee->email ?? 'No Email'); ?></td>

                                    <td><?php echo e($employee->username); ?></td>
                                    <td><?php echo e($employee->contact_number ?? 'N/A'); ?></td>
                                    <td><?php echo e(Str::limit($employee->address, 20) ?? 'N/A'); ?></td>
                                    <td><?php echo e($employee->date_hired ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span
                                            class="badge <?php echo e($employee->status === 'active' ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo e(ucfirst($employee->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($employee->role->role_name ?? 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-xs btn-success edit-employee-btn"
                                            data-id="<?php echo e($employee->id); ?>" data-full_name="<?php echo e($employee->full_name); ?>"
                                            data-email="<?php echo e($employee->email); ?>" data-username="<?php echo e($employee->username); ?>"
                                            data-role="<?php echo e($employee->role_id); ?>"
                                            data-warehouse="<?php echo e($employee->assigned_at); ?>"
                                            data-status="<?php echo e($employee->status); ?>"
                                            data-contact_number="<?php echo e($employee->contact_number); ?>"
                                            data-address="<?php echo e($employee->address); ?>"
                                            data-hired="<?php echo e($employee->date_hired); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        
                                        <button class="btn btn-xs btn-warning reset-pin-btn" data-id="<?php echo e($employee->id); ?>">
                                            <i class="fas fa-undo"></i> Reset PIN
                                        </button>

                                        <button class="btn btn-xs btn-danger delete-employee-btn"
                                            data-id="<?php echo e($employee->id); ?>" data-name="<?php echo e($employee->full_name); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="employeeForm" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="emp_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Username</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Role</label>
                                <select name="role_id" id="role_id" class="form-control" required>
                                    <option value="">-- Select Role --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>"><?php echo e($role->role_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Assigned Warehouse</label>
                                <select name="assigned_at" id="assigned_at" class="form-control">
                                    <option value="">-- Select Warehouse --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Date Hired</label>
                                <input type="date" name="date_hired" id="date_hired" class="form-control">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control-file">
                                <small class="text-muted">Leave blank if you don't want to change the photo.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="save_btn">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('js'); ?>
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                if ($.fn.DataTable.isDataTable('#sampleId')) {
                    $('#sampleId').DataTable().destroy();
                }
                let table = $('#sampleId').DataTable({
                    responsive: true,
                    autoWidth: false,
                    retrieve: true
                });

                // Create Mode
                $('#create_employee').on('click', function() {
                    $('#employeeForm')[0].reset();
                    $('#emp_id').val('');
                    $('.modal-title').text('Create New User');
                    $('#save_btn').text('Save Employee');
                    $('#createUserModal').modal('show');
                });


                // Edit Mode
                // Edit Mode
                $(document).on('click', '.edit-employee-btn', function() {
                    let btn = $(this);
                    let id = btn.data('id');

                    console.log("Click detected! Employee ID: " + id);

                    // 1. Siguraduhin na ang ID ay nailalagay sa hidden input
                    $('#emp_id').val(id);

                    // 2. I-fill ang basic fields
                    $('#full_name').val(btn.data('full_name'));
                    $('#email').val(btn.data('email'));
                    $('#username').val(btn.data('username'));

                    // 3. I-fill ang Contact at Address ✅ FIXED
                    $('#contact_number').val(btn.data('contact_number') ||
                        ''); // ✅ contact_number (with underscore)
                    $('#address').val(btn.data('address') || '');

                    // 4. I-fill ang dropdowns at date
                    $('#role_id').val(btn.data('role'));
                    $('#assigned_at').val(btn.data('warehouse'));
                    $('#status').val(btn.data('status'));
                    $('#date_hired').val(btn.data('hired'));

                    // 5. Palitan ang Modal UI
                    $('.modal-title').text('Edit Employee Details');
                    $('#save_btn').text('Update Employee');
                    $('#createUserModal').modal('show');
                });

                // AJAX Form Submission (Store & Update)
                $('#employeeForm').off('submit').on('submit', function(e) {
                    e.preventDefault();

                    let id = $('#emp_id').val();
                    let formData = new FormData(this);

                    // 1. DYNAMIC URL: Kung may ID, UPDATE. Kung wala, STORE (Create).
                    let url = id ? "<?php echo e(route('user.management.update')); ?>" :
                        "<?php echo e(route('user.management.store')); ?>";

                    console.log("Submitting to: " + url + " | ID: " + (id ? id : "New Record"));

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log(response);
                            Swal.fire('Success', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            // --- DITO MO ILALAGAY O PAPALITAN ---
                            console.log(xhr.responseText);

                            // Kinukuha nito yung error message mula sa Laravel (tulad ng "Email taken")
                            let errorMsg = xhr.responseJSON?.message ||
                                "May mali sa validation o sa server.";

                            // Mas maganda kung pati specific validation errors ay ma-display (optional)
                            if (xhr.responseJSON?.errors) {
                                let errors = Object.values(xhr.responseJSON.errors).flat().join(
                                    "<br>");
                                errorMsg = errors;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: errorMsg, // Ginagamit ang 'html' para gumana ang <br>
                            });
                        }
                    });
                }); // <--- SARA NG SUBMIT FUNCTION


                // 🟢 RESET PIN AJAX
                $(document).on('click', '.reset-pin-btn', function() {
                    let id = $(this).data('id'); // Siguraduhin na user ID ito (check Step 2)

                    Swal.fire({
                        title: 'Reset PIN?',
                        text: "This user will need to set a new PIN on their next action.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, Reset it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                // Gamitin ang route name na dinefine mo sa web.php
                                url: "<?php echo e(route('admin.reset.pin')); ?>",
                                type: "POST",
                                data: {
                                    id: id,
                                    _token: "<?php echo e(csrf_token()); ?>"
                                },
                                success: function(res) {
                                    Swal.fire('Reset!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    // Mas magandang debugging para makita ang 500 error
                                    let errorMsg = xhr.responseJSON?.message ||
                                        "Failed to reset PIN.";
                                    Swal.fire('Error', errorMsg, 'error');
                                }
                            });
                        }
                    });
                });
            });

            // 🔴 DELETE EMPLOYEE AJAX
            $(document).on('click', '.delete-employee-btn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'Sigurado ka ba?',
                    text: "Mabubura ang account ni " + name + ". Hindi mo na ito maibabalik!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oo, Burahin na!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            // Ito yung route na ginawa natin sa web.php kanina
                            url: "/user-management/delete/" + id,
                            type: "DELETE",
                            data: {
                                _token: "<?php echo e(csrf_token()); ?>"
                            },
                            success: function(res) {
                                Swal.fire('Deleted!', res.message, 'success').then(() => {
                                    location.reload(); // Refresh para maalis sa table
                                });
                            },
                            error: function(xhr) {
                                let errorMsg = xhr.responseJSON?.message ||
                                    "Hindi mabura ang user. Baka may naka-link na record.";
                                Swal.fire('Error!', errorMsg, 'error');
                            }
                        });
                    }
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/settings/user-management/index.blade.php ENDPATH**/ ?>