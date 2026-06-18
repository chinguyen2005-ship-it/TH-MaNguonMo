<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 bg-dark text-white rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-3 text-uppercase text-warning text-center">Khôi phục mật khẩu</h2>
                    <p class="text-white-50 mb-4 text-center">Vui lòng điền địa chỉ email đã đăng ký. Chúng tôi sẽ gửi một liên kết khôi phục mật khẩu mới đến email của bạn.</p>

                    <!-- Alert messages -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success py-2 text-start small">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/Account/sendResetLink" method="POST" class="text-start">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Địa chỉ Email của bạn:</label>
                            <input type="email" name="email" class="form-control form-control-lg" required placeholder="Nhập email...">
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning btn-lg fw-bold" type="submit">Gửi liên kết khôi phục</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 pt-3 border-top border-secondary text-center">
                        <p class="mb-0 text-white-50">Nhớ ra mật khẩu? <a href="/Account/login" class="text-warning fw-bold text-decoration-none">Đăng nhập tại đây</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
