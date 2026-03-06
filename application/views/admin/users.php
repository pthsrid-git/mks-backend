<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Debug Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Balance</th>
                    <th>Predictions</th>
                    <th>Accuracy</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user->id; ?></td>
                            <td><?php echo $user->username; ?></td>
                            <td><?php echo $user->email; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user->role == 'admin' ? 'danger' : 'info'; ?>">
                                    <?php echo ucfirst($user->role); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($user->balance, 2); ?></td>
                            <td><?php echo $user->total_correct_predictions . "/" . $user->total_predictions; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user->accuracy > 70 ? 'success' : ($user->accuracy > 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo number_format($user->accuracy, 1); ?>%
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url('admin/edit-user/' . $user->id); ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="<?php echo base_url('admin/delete-user/' . $user->id); ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo base_url('admin/add-user'); ?>" method="POST" id="addUserForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        <small class="form-text text-muted">Username must be unique</small>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                        <small class="form-text text-muted">Email must be unique</small>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password (min 6 characters)" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <small class="form-text text-muted">Admin can access web panel, User can only use mobile app</small>
                    </div>
                    <div class="form-group">
                        <label>Initial Balance ($)</label>
                        <input type="number" name="balance" class="form-control" value="0.00" step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitUserBtn">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Debug untuk Users -->
<script>
console.log('Users page loaded');

// Form submission handler untuk Add User
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    console.log('Add User Form submitted');
    
    // Log form data untuk debugging
    const formData = new FormData(this);
    console.log('Form Data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitUserBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    }
});

// jQuery version (jika jQuery terload)
$(document).ready(function() {
    console.log('jQuery ready on Users page');
    
    $('#addUserModal').on('show.bs.modal', function () {
        console.log('Add User Modal opening');
        // Reset form ketika modal dibuka
        $('#addUserForm')[0]?.reset();
    });
    
    $('#addUserModal').on('shown.bs.modal', function () {
        console.log('Add User Modal is now visible');
    });
    
    $('#addUserModal').on('hide.bs.modal', function () {
        console.log('Add User Modal closing');
        // Reset button state
        $('#submitUserBtn').prop('disabled', false).html('<i class="fas fa-user-plus"></i> Add User');
    });
});

// Check jika modal bisa dibuka
function testModal() {
    $('#addUserModal').modal('show');
    console.log('Manual modal test triggered');
}
</script>