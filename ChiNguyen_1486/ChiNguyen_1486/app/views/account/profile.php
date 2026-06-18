<?php
/**
 * @var stdClass $user
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', str_replace('\\', '/', dirname(__DIR__, 3)));
}
include BASE_PATH . '/app/views/shares/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header Card -->
                <div class="bg-gradient-primary text-white p-4 position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Hồ sơ người dùng</h1>
                            <p class="mb-0 opacity-85">Cập nhật thông tin cá nhân và quản lý mật khẩu tài khoản của bạn.</p>
                        </div>
                        <span class="badge bg-white text-primary text-uppercase fw-bold py-2 px-3 shadow-sm"><?php echo htmlspecialchars($user->role === 'admin' ? 'Admin' : 'Thành viên', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4">
                    <!-- Top User Info -->
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <?php if (!empty($user->avatar) && file_exists(BASE_PATH . '/' . ltrim($user->avatar, '/'))): ?>
                            <img src="/<?php echo htmlspecialchars(ltrim($user->avatar, '/')); ?>" class="rounded-circle border border-primary border-2 shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center border border-primary border-2 shadow-sm" style="width: 80px; height: 80px; font-size: 2.2rem; font-weight: bold;">
                                <?php echo strtoupper(substr(htmlspecialchars($user->fullname ?? $user->username, ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p class="text-muted mb-0">Tên đăng nhập: <span class="fw-semibold"><?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></span></p>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <?php if (isset($_SESSION['profile_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['profile_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['profile_error']; unset($_SESSION['profile_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['profile_errors']) && !empty($_SESSION['profile_errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['profile_errors'] as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; unset($_SESSION['profile_errors']); ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['password_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['password_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Tab Buttons -->
                    <ul class="nav nav-tabs rounded-3 overflow-hidden border-0 mb-4 bg-light p-1" id="profileTabs" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-bold border-0 py-3 active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab" aria-controls="info-pane" aria-selected="true">
                                <i class="bi bi-person-lines-fill me-2"></i>Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100 fw-bold border-0 py-3" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab" aria-controls="password-pane" aria-selected="false">
                                <i class="bi bi-shield-lock-fill me-2"></i>Đổi mật khẩu
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Tab 1: Personal Info -->
                        <div class="tab-pane fade show active p-2" id="info-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                            <form action="/Account/updateProfile" method="POST" enctype="multipart/form-data">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Tên đăng nhập (không thể thay đổi):</label>
                                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Họ và tên:</label>
                                            <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user->fullname, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Địa chỉ Email:</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Ảnh đại diện (avatar):</label>
                                            <input type="file" name="avatar" class="form-control" accept="image/*">
                                        </div>
                                    </div>

                                </div>
                                <div class="mt-4 border-top pt-3 d-flex justify-content-between align-items-center">
                                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2">
                                        <i class="bi bi-save"></i> Lưu thông tin
                                    </button>
                                    <span class="text-muted small">Đăng ký ngày: <?php echo date('d/m/Y', strtotime($user->created_at)); ?></span>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Change Password -->
                        <div class="tab-pane fade p-2" id="password-pane" role="tabpanel" aria-labelledby="password-tab" tabindex="0">
                            <form action="/Account/updatePassword" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mật khẩu hiện tại:</label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="Nhập mật khẩu hiện tại...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
                                    <input type="password" name="new_password" class="form-control" required placeholder="Nhập mật khẩu mới...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Xác nhận mật khẩu mới:</label>
                                    <input type="password" name="confirm_new_password" class="form-control" required placeholder="Nhập lại mật khẩu mới...">
                                </div>
                                <div class="mt-4 border-top pt-3">
                                    <button type="submit" class="btn btn-warning fw-semibold d-flex align-items-center gap-2 px-4 py-2">
                                        <i class="bi bi-shield-check"></i> Đổi mật khẩu ngay
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bottom Nav -->
                    <div class="mt-5 d-flex gap-2 flex-column flex-sm-row border-top pt-4">
                        <?php if ($user->role !== 'admin'): ?>
                            <a href="/Order/my_orders" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="bi bi-card-checklist"></i> Xem đơn hàng đã đặt
                            </a>
                        <?php endif; ?>
                        <?php if ($user->role === 'admin'): ?>
                            <a href="/Account/users" class="btn btn-outline-danger d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill"></i> Quản lý người dùng
                            </a>
                        <?php endif; ?>
                        <a href="/Account/logout" class="btn btn-outline-secondary ms-sm-auto d-flex align-items-center gap-2" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Auto Switch Hash JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.location.hash === '#password') {
            const passwordTabTrigger = document.querySelector('#password-tab');
            if (passwordTabTrigger) {
                bootstrap.Tab.getOrCreateInstance(passwordTabTrigger).show();
            }
        }
    });
</script>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
