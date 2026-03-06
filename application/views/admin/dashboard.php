<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="<?php echo base_url('admin/users'); ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $total_admins; ?></h3>
                <p>Admin Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="<?php echo base_url('admin/users'); ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $total_regular_users; ?></h3>
                <p>Regular Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-user"></i>
            </div>
            <a href="<?php echo base_url('admin/users'); ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo $total_matches; ?></h3>
                <p>Total Matches</p>
            </div>
            <div class="icon">
                <i class="fas fa-futbol"></i>
            </div>
            <a href="<?php echo base_url('admin/matches'); ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Users</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td><?php echo $user->username; ?></td>
                                <td><?php echo $user->email; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user->role == 'admin' ? 'danger' : 'info'; ?>">
                                        <?php echo ucfirst($user->role); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Bonus Information Card -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bonus Information</h3>
            </div>
            <div class="card-body">
                <?php if (isset($bonus_settings) && !empty($bonus_settings)): ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-gift"></i> Weekly Bonus System (Per League)</h5>
                        <p class="mb-1"><strong>Minimum Predictions:</strong> <?php echo $bonus_settings['min_predictions']; ?> correct per league</p>
                        <p class="mb-1"><strong>Bonus Pool Per League:</strong> $<?php echo number_format($bonus_settings['bonus_amount'], 2); ?></p>
                        <p class="mb-1"><strong>Distribution:</strong> Equally shared among eligible users in each league</p>
                        <p class="mb-0">
                            <strong>Example:</strong><br>
                            - Premier League: 2 users → $<?php echo number_format($bonus_settings['bonus_amount'] / 2, 2); ?> each<br>
                            - La Liga: 3 users → $<?php echo number_format($bonus_settings['bonus_amount'] / 3, 2); ?> each
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Bonus Settings Not Configured</h5>
                        <p class="mb-0">Please configure the bonus settings in the settings panel.</p>
                    </div>
                <?php endif; ?>

                <a href="<?php echo base_url('admin/settings'); ?>" class="btn btn-outline-primary btn-sm btn-block">
                    <i class="fas fa-cog"></i> Configure Settings
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="<?php echo base_url('admin/matches'); ?>" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus"></i> Add New Match
                </a>
                <a href="<?php echo base_url('admin/users'); ?>" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
                <a href="<?php echo base_url('admin/profile'); ?>" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-user-cog"></i> My Profile
                </a>
                <button class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#bonusModal">
                    <i class="fas fa-gift"></i> Calculate Weekly Bonus
                </button>
            </div>
        </div>
    </div>
</div>