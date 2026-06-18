<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 bg-dark text-white">
                <div class="card-body p-5 text-center">
                    <h2 class="fw-bold mb-3 text-uppercase text-warning">ĐĂNG NHẬP HỆ THỐNG</h2>
                    <p class="text-white-50 mb-4">Vui lòng nhập chính xác tên tài khoản và mật khẩu!</p>

                    <?php if (isset($_SESSION['register_success'])): ?>
                        <div class="alert alert-success py-2 text-start small">
                            ✓ <?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['register_error'])): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            ⚠ <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger py-2 text-start small">
                            ⚠ <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                        </div>
                    <?php endif; ?>

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
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu bảo mật:</label>
                            <input type="password" name="password" class="form-control form-control-lg" required placeholder="Mật khẩu của bạn...">
                        </div>
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
                                <label class="form-check-label text-white-50" for="rememberMe">Ghi nhớ đăng nhập</label>
                            </div>
                            <a href="/Account/forgotPassword" class="text-warning small text-decoration-none">Quên mật khẩu?</a>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const usernameInput = form.querySelector('input[name="username"]').value;
            const passwordInput = form.querySelector('input[name="password"]').value;
            
            // Xóa thông báo cũ
            const oldAlerts = document.querySelectorAll('.alert-danger');
            oldAlerts.forEach(el => el.style.display = 'none');
            
            let alertDiv = document.getElementById('login-alert');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.id = 'login-alert';
                alertDiv.className = 'alert alert-danger py-2 text-start small';
                form.parentNode.insertBefore(alertDiv, form);
            }
            alertDiv.style.display = 'none';

            fetch('/api/account/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: usernameInput,
                    password: passwordInput
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.status && data.token) {
                    // Lưu token vào localStorage
                    localStorage.setItem('jwt_token', data.token);
                    // Điều hướng về trang sản phẩm
                    window.location.href = '/Product';
                } else {
                    alertDiv.textContent = '⚠ ' + (data.message || 'Đăng nhập không thành công.');
                    alertDiv.style.display = 'block';
                }
            })
            .catch(error => {
                alertDiv.textContent = '⚠ ' + (error.message || 'Tên đăng nhập hoặc mật khẩu không chính xác.');
                alertDiv.style.display = 'block';
            });
        });
    }
});
</script>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>