<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie"></i> Bonus Distribution Reports
        </h3>
        <div class="card-tools">
            <span class="badge badge-info">Per League System</span>
        </div>
    </div>
    <div class="card-body">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Payments</span>
                        <span class="info-box-number"><?php echo count($bonus_payments); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Distributed</span>
                        <span class="info-box-number">
                            $<?php 
                                $total = 0;
                                foreach ($bonus_payments as $payment) {
                                    $total += $payment->individual_share;
                                }
                                echo number_format($total, 2);
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unique Leagues</span>
                        <span class="info-box-number">
                            <?php
                                $leagues = [];
                                foreach ($bonus_payments as $payment) {
                                    $leagues[$payment->league_id] = true;
                                }
                                echo count($leagues);
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unique Users</span>
                        <span class="info-box-number">
                            <?php
                                $users = [];
                                foreach ($bonus_payments as $payment) {
                                    $users[$payment->user_id] = true;
                                }
                                echo count($users);
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bonus Payments Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Week</th>
                        <th>User</th>
                        <th>League</th>
                        <th>Correct</th>
                        <th>League Pool</th>
                        <th>Eligible Users</th>
                        <th>Individual Share</th>
                        <th>Date Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bonus_payments)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($bonus_payments as $payment): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td>
                                    <span class="badge badge-primary">Week <?php echo $payment->week_number; ?></span>
                                    <br><small><?php echo $payment->year; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo $payment->username; ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo $payment->league_name; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?php echo $payment->correct_predictions; ?> correct</span>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($payment->league_bonus_pool, 2); ?></strong>
                                </td>
                                <td>
                                    <?php echo $payment->total_eligible_users; ?> users
                                </td>
                                <td>
                                    <strong class="text-success">$<?php echo number_format($payment->individual_share, 2); ?></strong>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($payment->paid_at)); ?>
                                    <br><small><?php echo date('H:i', strtotime($payment->paid_at)); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                    <h4>No Bonus Payments Found</h4>
                                    <p>Bonus payments will appear here after calculating weekly bonuses.</p>
                                    <a href="<?php echo base_url('admin'); ?>" class="btn btn-primary">
                                        <i class="fas fa-gift"></i> Calculate Bonus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Export Options (Future Feature) -->
        <div class="mt-4">
            <div class="alert alert-info">
                <h5><i class="fas fa-download"></i> Export Options</h5>
                <p>Export bonus reports for accounting and analysis purposes.</p>
                <button class="btn btn-outline-primary btn-sm" disabled>
                    <i class="fas fa-file-excel"></i> Export to Excel (Coming Soon)
                </button>
                <button class="btn btn-outline-secondary btn-sm" disabled>
                    <i class="fas fa-file-pdf"></i> Export to PDF (Coming Soon)
                </button>
            </div>
        </div>
    </div>
</div>