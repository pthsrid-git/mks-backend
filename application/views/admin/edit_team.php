<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Team: <?php echo htmlspecialchars($team->name, ENT_QUOTES, 'UTF-8'); ?></h3>
        <div class="card-tools">
            <a href="<?php echo base_url('admin/teams'); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Teams
            </a>
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

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo validation_errors('<div>', '</div>'); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo base_url('admin/edit-team/' . $team->id); ?>" 
              method="POST" enctype="multipart/form-data" id="editTeamForm">

            <div class="form-group">
                <label>Team Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?php echo set_value('name', $team->name); ?>"
                       placeholder="Enter team name" required>
            </div>

            <div class="form-group">
                <label>League *</label>
                <select name="league_id" class="form-control" required>
                    <option value="">Select League</option>
                    <?php foreach ($leagues as $league): ?>
                        <option value="<?php echo $league->id; ?>"
                            <?php echo set_select('league_id', $league->id, ($league->id == $team->league_id)); ?>>
                            <?php echo $league->name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Logo</label>
                <div>
                    <?php if (!empty($team->logo)): ?>
                        <img src="<?php echo base_url($team->logo); ?>" 
                             alt="<?php echo htmlspecialchars($team->name, ENT_QUOTES, 'UTF-8'); ?>"
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                    <?php else: ?>
                        <div style="width: 60px; height: 60px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                            <i class="fas fa-shield-alt text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <small class="form-text text-muted">
                    If you don't upload a new logo, the existing one will be kept.
                </small>
            </div>

            <div class="form-group">
                <label>Change Logo (Upload)</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                    <label class="custom-file-label" for="logo">Choose logo file</label>
                </div>
                <small class="form-text text-muted">Max size: 2MB. Allowed types: JPG, JPEG, PNG, GIF</small>
            </div>

            <div class="form-group">
                <label>Or Logo URL</label>
                <input type="url" name="logo_url" class="form-control"
                       value="<?php echo set_value('logo_url', isset($team->logo_url) ? $team->logo_url : ''); ?>"
                       placeholder="https://example.com/logo.png">
                <small class="form-text text-muted">
                    Alternative: Provide logo URL instead of uploading.
                    (Controller tentukan prioritas mana yang dipakai.)
                </small>
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="<?php echo base_url('admin/teams'); ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Preview log upload (opsional, sama kayak add)
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('New logo selected:', file.name);
            // Di sini kalau mau, kamu bisa tampilkan preview ke <img> baru
        }
        reader.readAsDataURL(file);
    }
});

// Bootstrap custom file input
$(document).ready(function() {
    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    }
});
</script>
