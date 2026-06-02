<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-gradient-primary text-white p-4 position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Hồ sơ người dùng</h1>
                            <p class="mb-0 opacity-85">Quản lý thông tin cá nhân và nhanh chóng truy cập các tính năng của bạn.</p>
                        </div>
                        <span class="badge bg-white text-primary text-uppercase fw-bold py-2 px-3 shadow-sm"><?php echo htmlspecialchars($user->role === 'admin' ? 'Admin' : 'Thành viên', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="position-absolute top-0 end-0 me-4 mt-4 opacity-15" style="font-size: 6rem;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                            <?php echo strtoupper(substr(htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="text-muted mb-0">Tên đăng nhập: <span class="fw-semibold"><?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></span></p>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-uppercase text-muted small mb-2">Quyền hạn</div>
                                <div class="fw-semibold fs-5"><?php echo htmlspecialchars($user->role === 'admin' ? 'Quản trị viên' : 'Thành viên', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-uppercase text-muted small mb-2">Ngày tạo</div>
                                <div class="fw-semibold fs-5"><?php echo isset($user->created_at) ? date('d/m/Y', strtotime($user->created_at)) : 'Không xác định'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-uppercase text-muted small mb-2">Số liên hệ</div>
                                <div class="fw-semibold fs-5"><?php echo htmlspecialchars($_SESSION['phone'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-3">
                        <div class="col-md-6">
                            <div class="list-group rounded-4 shadow-sm">
                                <div class="list-group-item bg-light border-0">
                                    <div class="fw-semibold mb-1">Thông tin tài khoản</div>
                                    <p class="mb-0 text-muted small">Quản lý bảo mật và chi tiết đăng nhập.</p>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person-badge me-2 text-primary"></i> Họ tên</span>
                                    <span class="text-end text-secondary"><?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-card-text me-2 text-primary"></i> Username</span>
                                    <span class="text-end text-secondary"><?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-shield-lock me-2 text-primary"></i> Vai trò</span>
                                    <span class="text-end text-secondary text-uppercase"><?php echo htmlspecialchars($user->role, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light rounded-4 h-100 p-4">
                                <h3 class="h6 fw-semibold mb-3">Gợi ý trải nghiệm</h3>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Xem nhanh <strong>Đơn hàng đã đặt</strong> và trạng thái giao hàng.
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-lock-fill text-primary me-2"></i>
                                        Cập nhật thông tin an toàn và quản lý mật khẩu.
                                    </li>
                                    <li>
                                        <i class="bi bi-speedometer2 text-warning me-2"></i>
                                        Nếu bạn là admin, truy cập <strong>Quản trị hệ thống</strong> để quản lý nội dung.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2 flex-column flex-sm-row">
                        <a href="/Order/my_orders" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
                            <i class="bi bi-card-checklist"></i> Xem đơn hàng đã đặt
                        </a>
                        <a href="/Account/logout" class="btn btn-outline-danger btn-lg d-flex align-items-center gap-2" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
