<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Settings</h3>
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

        <!-- Bonus Settings Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Bonus & Prediction Settings</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo base_url('admin/update-bonus-settings'); ?>" method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Minimum Predictions for Bonus *</label>
                                <input type="number" name="min_predictions" class="form-control"
                                    value="<?php echo $bonus_settings['min_predictions']; ?>"
                                    min="1" max="50" required>
                                <small class="form-text text-muted">
                                    Minimum correct predictions required to get weekly bonus
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Bonus Amount Per League ($) *</label>
                            <input type="number" name="bonus_amount" class="form-control"
                                value="<?php echo $bonus_settings['bonus_amount']; ?>"
                                step="0.01" min="1" max="1000" required>
                            <small class="form-text text-muted">
                                <strong>Bonus amount per league</strong> that will be <strong>shared equally</strong> among all users who achieve minimum predictions <strong>in that league</strong>
                            </small>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Predictions Per Week *</label>
                                <input type="number" name="max_predictions" class="form-control"
                                    value="<?php echo $bonus_settings['max_predictions_per_week']; ?>"
                                    min="1" max="100" required>
                                <small class="form-text text-muted">
                                    Maximum predictions a user can make per week
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Bonus Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- All Settings Card -->
        <div class="card card-secondary mt-4">
            <div class="card-header">
                <h3 class="card-title">All System Settings</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo base_url('admin/update-settings'); ?>" method="POST">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Setting Key</th>
                                <th>Value</th>
                                <th>Description</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($settings as $setting): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $setting->setting_key; ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($setting->setting_type == 'boolean'): ?>
                                            <select name="settings[<?php echo $setting->setting_key; ?>]" class="form-control form-control-sm">
                                                <option value="1" <?php echo $setting->setting_value == '1' ? 'selected' : ''; ?>>Yes</option>
                                                <option value="0" <?php echo $setting->setting_value == '0' ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        <?php elseif ($setting->setting_type == 'integer'): ?>
                                            <input type="number" name="settings[<?php echo $setting->setting_key; ?>]"
                                                class="form-control form-control-sm" value="<?php echo $setting->setting_value; ?>">
                                        <?php elseif ($setting->setting_type == 'decimal'): ?>
                                            <input type="number" name="settings[<?php echo $setting->setting_key; ?>]"
                                                class="form-control form-control-sm" step="0.01" value="<?php echo $setting->setting_value; ?>">
                                        <?php else: ?>
                                            <input type="text" name="settings[<?php echo $setting->setting_key; ?>]"
                                                class="form-control form-control-sm" value="<?php echo $setting->setting_value; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo $setting->description; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $setting->setting_type; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update All Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Bonus Info -->
        <div class="card card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">Current Bonus Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-bullseye"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Min Predictions</span>
                                <span class="info-box-number"><?php echo $bonus_settings['min_predictions']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Bonus Amount</span>
                                <span class="info-box-number">$<?php echo number_format($bonus_settings['bonus_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Max Weekly</span>
                                <span class="info-box-number"><?php echo $bonus_settings['max_predictions_per_week']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Note:</strong> Users need to predict at least <strong><?php echo $bonus_settings['min_predictions']; ?></strong>
                    matches correctly in a week to receive <strong>$<?php echo number_format($bonus_settings['bonus_amount'], 2); ?></strong> bonus.
                    Maximum <strong><?php echo $bonus_settings['max_predictions_per_week']; ?></strong> predictions allowed per user per week.
                </div>
            </div>
        </div>
    </div>
</div>