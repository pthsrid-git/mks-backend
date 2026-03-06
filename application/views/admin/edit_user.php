<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit User</h3>
    </div>
    <div class="card-body">
        <form action="<?php echo base_url('admin/edit-user/' . $user->id); ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $user->username; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $user->email; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Balance ($)</label>
                        <input type="number" name="balance" class="form-control" value="<?php echo $user->balance; ?>" step="0.01" min="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Total Correct Predictions</label>
                        <input type="number" name="total_correct_predictions" class="form-control" value="<?php echo $user->total_correct_predictions; ?>" min="0">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Total Predictions</label>
                        <input type="number" name="total_predictions" class="form-control" value="<?php echo $user->total_predictions; ?>" min="0">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="<?php echo base_url('admin/users'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>