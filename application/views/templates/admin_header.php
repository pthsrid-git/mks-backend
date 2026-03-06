<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title ?? "Prediction System"; ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url("admin/logout"); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="<?php echo base_url("admin"); ?>" class="brand-link">
                <i class="fas fa-futbol brand-icon"></i>
                <span class="brand-text font-weight-light">Prediction System</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="<?php echo base_url("admin"); ?>" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <!-- Di sidebar, tambahkan menu: -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/leagues'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-trophy"></i>
                                <p>Leagues</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/teams'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Teams</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo base_url("admin/matches"); ?>" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Matches</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo base_url("admin/users"); ?>" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-toggle="modal" data-target="#bonusModal">
                                <i class="nav-icon fas fa-gift"></i>
                                <p>Calculate Bonus</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/match_update'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-sync-alt"></i>
                                <p>Match Updates</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/withdraw'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-wallet"></i>
                                <p>Tarik Saldo</p>
                            </a>
                        </li>

                        <!-- Di dalam <ul class="nav nav-pills nav-sidebar flex-column"> -->
                        <li class="nav-item">
                            <a href="<?php echo base_url('admin/bonus-reports'); ?>" class="nav-link">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Bonus Reports</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?php echo $title ?? "Dashboard"; ?></h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?php if ($this->session->flashdata("success")): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php echo $this->session->flashdata("success"); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata("error")): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <?php echo $this->session->flashdata("error"); ?>
                        </div>
                    <?php endif; ?>