<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h2 class="mb-0 fs-4 fw-bold">Đăng ký thành viên</h2>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 small">
                                <?php foreach ($errors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/Account/save" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tài khoản đăng nhập (Username):</label>
                            <input type="text" name="username" class="form-control" required placeholder="Nhập tên tài khoản...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên của bạn:</label>
                            <input type="text" name="fullname" class="form-control" required placeholder="Nhập họ tên...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ Email:</label>
                            <input type="email" name="email" class="form-control" required placeholder="Nhập email kích hoạt...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu:</label>
                            <input type="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Xác nhận mật khẩu:</label>
                            <input type="password" name="confirmpassword" class="form-control" required placeholder="Nhập lại mật khẩu giống trên...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò thành viên:</label>
                            <select name="role" class="form-select">
                                <option value="user">Khách hàng thông thường (User)</option>
                                <option value="admin">Quản trị viên (Admin)</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Xác nhận Đăng ký tài khoản</button>
                            <a href="/Account/login" class="text-center small mt-2">Đã có tài khoản? Đăng nhập tại đây</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>