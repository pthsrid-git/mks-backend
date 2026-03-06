<!-- HEADER -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <p class="text-muted mb-0">
                    Daftar permintaan penarikan poin pengguna. Approve atau tolak permintaan dari user.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CONTENT -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Permintaan</h3>
                    </div>

                    <div class="card-body table-responsive">

                        <?php if ($this->session->flashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= $this->session->flashdata('success'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= $this->session->flashdata('error'); ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Bank</th>
                                    <th>No Rekening</th>
                                    <th>Pemilik</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rows)): ?>
                                    <?php $no = 1; foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <strong><?= html_escape($r['username']) ?></strong><br>
                                                <small><?= html_escape($r['email']) ?></small>
                                            </td>
                                            <td><?= html_escape($r['bank_name']) ?></td>
                                            <td><?= html_escape($r['account_number']) ?></td>
                                            <td><?= html_escape($r['account_owner']) ?></td>
                                            <td><strong>$<?= number_format($r['amount'], 2) ?></strong></td>
                                            <td>
                                                <?php
                                                $cls = [
                                                    'pending'  => 'badge badge-warning',
                                                    'approved' => 'badge badge-success',
                                                    'rejected' => 'badge badge-danger',
                                                ];
                                                ?>
                                                <span class="<?= $cls[$r['status']] ?>">
                                                    <?= ucfirst($r['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= $r['created_at'] ?></small><br>
                                                <small class="text-muted">
                                                    Update: <?= $r['updated_at'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] == 'pending'): ?>
                                                    <a href="<?= base_url('admin/withdraw/approve/'.$r['id']) ?>"
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Approve penarikan ini?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="<?= base_url('admin/withdraw/reject/'.$r['id']) ?>"
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Tolak penarikan ini?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Done</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            Belum ada permintaan penarikan
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
