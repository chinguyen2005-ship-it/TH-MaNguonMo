<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 bg-dark text-white rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-3 text-uppercase text-warning text-center">Đặt lại mật khẩu</h2>
                    <p class="text-white-50 mb-4 text-center">Vui lòng nhập mật khẩu mới của bạn bên dưới.</p>

                    <!-- Alert messages -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/Account/updatePasswordWithToken" method="POST" class="text-start">
                        <!-- Token ẩn để bảo mật và định danh -->
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
                            <input type="password" name="password" class="form-control form-control-lg" required placeholder="Nhập mật khẩu mới...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu mới:</label>
                            <input type="password" name="confirm_password" class="form-control form-control-lg" required placeholder="Xác nhận mật khẩu mới...">
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning btn-lg fw-bold" type="submit">Cập nhật mật khẩu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
