<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Leagues</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addLeagueModal">
                <i class="fas fa-plus"></i> Add New League
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
                    <th>Name</th>
                    <th>Country</th>
                    <th>Season</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leagues)): ?>
                    <?php foreach ($leagues as $league): ?>
                        <tr>
                            <td><?php echo $league->id; ?></td>
                            <td><?php echo $league->name; ?></td>
                            <td><?php echo $league->country; ?></td>
                            <td><?php echo $league->season; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $league->is_active ? 'success' : 'secondary'; ?>">
                                    <?php echo $league->is_active ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($league->created_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url('admin/edit-league/' . $league->id); ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="<?php echo base_url('admin/delete-league/' . $league->id); ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this league?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No leagues found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add League Modal -->
<div class="modal fade" id="addLeagueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New League</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo base_url('admin/add-league'); ?>" method="POST" id="addLeagueForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>League Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter league name" required>
                    </div>
                    <div class="form-group">
                        <label>Country *</label>
                        <input type="text" name="country" class="form-control" placeholder="Enter country" required>
                    </div>
                    <div class="form-group">
                        <label>Season *</label>
                        <input type="text" name="season" class="form-control" placeholder="e.g., 2024/2025" required>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Active League</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add League
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>