<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <span class="badge bg-success rounded-pill px-4 py-2 mb-4">ĐẶT HÀNG THÀNH CÔNG</span>
                    <h1 class="fw-bold mb-3">Cảm ơn bạn đã đặt hàng tại Shop IT!</h1>
                    <p class="text-muted mb-4">Đơn hàng của bạn đã được ghi nhận và sẽ được xử lý sớm nhất có thể.</p>
                    <?php if (!empty($_GET['order_id'])): ?>
                        <p class="mb-4">Mã đơn hàng của bạn là <strong>#<?php echo htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <?php endif; ?>
                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <a href="/Product" class="btn btn-primary btn-lg px-5">Tiếp tục mua sắm</a>
                        <a href="/Product" class="btn btn-outline-secondary btn-lg px-5">Xem sản phẩm khác</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
