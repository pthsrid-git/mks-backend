    <div class="login-logo">
        <a href="#"><b>Prediction</b>System</a>
    </div>
    
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Sign in to start your session</p>
            
            <?php if ($this->session->flashdata("error")): ?>
                <div class="alert alert-danger">
                    <?php echo $this->session->flashdata("error"); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo base_url("auth/admin_login"); ?>" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </div>
            </form>
            
            <p class="mt-3 mb-1 text-center">
                <strong>Default Login:</strong> admin / admin123
            </p>
        </div>
    </div>
</div>