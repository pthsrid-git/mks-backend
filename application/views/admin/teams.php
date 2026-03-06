<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Teams</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTeamModal">
                <i class="fas fa-plus"></i> Add New Team
            </button>
        </div>
    </div>
    <div class="card-body">
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
                    <th>Logo</th>
                    <th>Team Name</th>
                    <th>League</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teams)): ?>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?php echo $team->id; ?></td>
                            <td>
                                <?php if ($team->logo): ?>
                                    <img src="<?php echo base_url($team->logo); ?>" alt="<?php echo $team->name; ?>" 
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                        <i class="fas fa-shield-alt text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $team->name; ?></td>
                            <td>
                                <?php if ($team->league_name): ?>
                                    <span class="badge badge-info"><?php echo $team->league_name; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No League</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($team->created_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url('admin/edit-team/' . $team->id); ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="<?php echo base_url('admin/delete-team/' . $team->id); ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this team?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No teams found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Team Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Team</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo base_url('admin/add-team'); ?>" method="POST" id="addTeamForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Team Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter team name" required>
                    </div>
                    <div class="form-group">
                        <label>League *</label>
                        <select name="league_id" class="form-control" required>
                            <option value="">Select League</option>
                            <?php foreach ($leagues as $league): ?>
                            <option value="<?php echo $league->id; ?>"><?php echo $league->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Team Logo</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                            <label class="custom-file-label" for="logo">Choose logo file</label>
                        </div>
                        <small class="form-text text-muted">Max size: 2MB. Allowed types: JPG, JPEG, PNG, GIF</small>
                    </div>
                    <div class="form-group">
                        <label>Or Logo URL</label>
                        <input type="url" name="logo_url" class="form-control" placeholder="https://example.com/logo.png">
                        <small class="form-text text-muted">Alternative: Provide logo URL instead of uploading</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview image before upload
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You can add image preview here if needed
            console.log('Image selected:', file.name);
        }
        reader.readAsDataURL(file);
    }
});

// Bootstrap file input
$(document).ready(function() {
    bsCustomFileInput.init();
});
</script>