<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h4 fw-bold mb-1">Quản lý người dùng</h1>
                    <p class="mb-0 opacity-85">Xem thông tin thành viên, kiểm tra kích hoạt email và thực hiện khóa/mở khóa tài khoản.</p>
                </div>
                <span class="badge bg-white text-danger text-uppercase fw-bold py-2 px-3 shadow-sm">Khu vực Admin</span>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Alert for Admin errors -->
            <?php if (isset($_SESSION['admin_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3">Ảnh</th>
                            <th scope="col" class="py-3">Tài khoản (Username)</th>
                            <th scope="col" class="py-3">Họ và tên</th>
                            <th scope="col" class="py-3">Email</th>
                            <th scope="col" class="py-3">Vai trò</th>
                            <th scope="col" class="py-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Không có người dùng nào trên hệ thống.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="avatar rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 1.1rem;">
                                            <?php echo strtoupper(substr(htmlspecialchars($u->fullname ?? $u->username, ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($u->fullname, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div class="small"><i class="bi bi-envelope me-1 text-muted"></i><?php echo htmlspecialchars($u->email ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($u->role === 'admin'): ?>
                                            <span class="badge bg-danger text-uppercase px-2.5 py-1.5 fw-bold">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-uppercase px-2.5 py-1.5">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($u->username === $_SESSION['username']): ?>
                                            <span class="text-muted small italic">Tài khoản cá nhân</span>
                                        <?php else: ?>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="/Account/deleteUser/<?php echo urlencode($u->username); ?>" class="btn btn-sm btn-outline-danger px-2 py-1.5 d-inline-flex align-items-center gap-1" onclick="return confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN tài khoản này? Thao tác này sẽ xóa mọi dữ liệu liên quan và không thể khôi phục.');">
                                                    <i class="bi bi-trash-fill"></i> Xóa
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <a href="/Product" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại trang chủ</a>
                <a href="/Category" class="btn btn-outline-primary"><i class="bi bi-grid-fill"></i> Quản lý danh mục</a>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
