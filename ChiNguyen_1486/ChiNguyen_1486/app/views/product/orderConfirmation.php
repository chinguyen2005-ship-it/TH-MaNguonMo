<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5 text-center">
    <div class="py-5 shadow-sm rounded bg-light border border-success">
        <div class="text-success display-1 mb-4">✔</div>
        <h1 class="text-success mb-3">Xác nhận đơn hàng thành công!</h1>
        <p class="lead text-muted px-4">Cảm ơn bạn đã lựa chọn đặt sản phẩm. Đơn hàng của bạn đang được xử lý trên hệ thống.</p>
        <?php if (!empty($_GET['order_id'])): ?>
            <p class="mb-3"><strong>Mã đơn hàng:</strong> #<?php echo htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="mt-4">
            <a href="/Product" class="btn btn-primary btn-lg px-5">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>