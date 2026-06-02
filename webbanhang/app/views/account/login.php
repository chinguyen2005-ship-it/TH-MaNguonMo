<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 bg-dark text-white">
                <div class="card-body p-5 text-center">
                    <h2 class="fw-bold mb-3 text-uppercase text-warning">ĐĂNG NHẬP HỆ THỐNG</h2>
                    <p class="text-white-50 mb-4">Vui lòng nhập chính xác tên tài khoản và mật khẩu!</p>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            ⚠ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/Account/checkLogin" method="POST" class="text-start">
                        <div class="mb-3">
                            <label class="form-label">Tên tài khoản (Username):</label>
                            <input type="text" name="username" class="form-control form-control-lg" required placeholder="Tên đăng nhập...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Mật khẩu bảo mật:</label>
                            <input type="password" name="password" class="form-control form-control-lg" required placeholder="Mật khẩu của bạn...">
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning btn-lg" type="submit">Đăng nhập ngay</button>
                        </div>
                    </form>
                    <div class="mt-4 pt-2 border-top border-secondary">
                        <p class="mb-0 text-white-50">Chưa có tài khoản thành viên? <a href="/Account/register" class="text-warning fw-bold text-decoration-none">Đăng ký ngay tại đây</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>