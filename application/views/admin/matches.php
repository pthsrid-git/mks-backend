<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Matches</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addMatchModal">
                <i class="fas fa-plus"></i> Add New Match
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Debug Info -->
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger">
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>League</th>
                    <th>Match</th>
                    <th>Date</th>
                    <th>Week</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($matches)): ?>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?php echo $match->id; ?></td>
                            <td><?php echo $match->league_name; ?></td>
                            <td><?php echo $match->home_team . " vs " . $match->away_team; ?></td>
                            <td><?php echo date('M d, Y', strtotime($match->match_date)); ?></td>
                            <td><?php echo $match->week_number; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $match->status == 'finished' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($match->status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo base_url('admin/edit-match/' . $match->id); ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="<?php echo base_url('admin/delete-match/' . $match->id); ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this match?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No matches found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Match Modal -->
<div class="modal fade" id="addMatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Match</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo base_url('admin/add-match'); ?>" method="POST" id="addMatchForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>League *</label>
                                <select name="league_id" class="form-control" required>
                                    <option value="">Select League</option>
                                    <?php foreach ($leagues as $league): ?>
                                    <option value="<?php echo $league->id; ?>"><?php echo $league->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Season *</label>
                                <input type="text" name="season" class="form-control" value="2024/2025" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Home Team *</label>
                                <select name="home_team_id" class="form-control" required>
                                    <option value="">Select Home Team</option>
                                    <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team->id; ?>"><?php echo $team->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Away Team *</label>
                                <select name="away_team_id" class="form-control" required>
                                    <option value="">Select Away Team</option>
                                    <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team->id; ?>"><?php echo $team->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Match Date *</label>
                                <input type="date" name="match_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Match Time *</label>
                                <input type="time" name="match_time" class="form-control" value="15:00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Week Number *</label>
                                <input type="number" name="week_number" class="form-control" value="1" min="1" max="52" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitMatchBtn">Add Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Debug -->
<script>
console.log('Matches page loaded');

// Form submission handler
document.getElementById('addMatchForm')?.addEventListener('submit', function(e) {
    console.log('Form submitted');
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
});

// Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
} else {
    console.log('jQuery version:', jQuery.fn.jquery);
}

// Check if Bootstrap modal works
$('#addMatchModal').on('show.bs.modal', function () {
    console.log('Modal is opening');
});

$('#addMatchModal').on('shown.bs.modal', function () {
    console.log('Modal is open');
});
</script>