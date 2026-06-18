<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<?php
$fullname = $_SESSION['fullname'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$subtotal = 0;
$shipping = 0;
$discount = 0;
foreach ($cart as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
$total = $subtotal + $shipping - $discount;
?>

<div class="container my-5">
    <?php if (!empty($_SESSION['checkout_error'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['checkout_error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/Order/processCheckout">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom px-4 py-3">
                        <h4 class="mb-0">Thông tin giao hàng</h4>
                        <p class="text-muted small mb-0">Điền thông tin chính xác để chúng tôi giao đơn hàng đến đúng nơi.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label fw-semibold">Họ và tên</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Nguyễn Văn A" required value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="0912345678" required value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="address" class="form-label fw-semibold">Địa chỉ giao hàng</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="Số nhà, đường, phường/quận" required>
                            </div>
                            <div class="col-12">
                                <label for="note" class="form-label fw-semibold">Ghi chú đơn hàng <span class="text-muted small">(tùy chọn)</span></label>
                                <textarea id="note" name="note" class="form-control" rows="3" placeholder="Ghi chú cho người giao hàng, ví dụ: gọi trước khi đến..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold d-block mb-3">Phương thức thanh toán</label>
                                <div class="d-flex flex-column gap-3">
                                    <label class="payment-option p-3 rounded border">
                                        <input type="radio" name="payment_method" value="cod" checked>
                                        <div>
                                            <span class="fw-bold">Thanh toán khi nhận hàng (COD)</span>
                                            <p class="mb-0 text-muted small">Thanh toán trực tiếp cho nhân viên giao hàng.</p>
                                        </div>
                                    </label>
                                    <label class="payment-option p-3 rounded border">
                                        <input type="radio" name="payment_method" value="bank_transfer">
                                        <div>
                                            <span class="fw-bold">Chuyển khoản Ngân hàng / Quét mã QR</span>
                                            <p class="mb-0 text-muted small">Bạn sẽ nhận hướng dẫn chuyển khoản sau khi đặt hàng.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 summary-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Tóm tắt đơn hàng</h5>
                        <div class="mb-3">
                            <?php foreach ($cart as $item): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="me-3" style="width: 64px;">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php else: ?>
                                            <div class="bg-light rounded text-center py-3">No image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small mb-1"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-muted small">x<?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="text-end fw-bold text-danger small">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-4">
                            <label for="discount_code" class="form-label fw-semibold">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" id="discount_code" name="discount_code" class="form-control" placeholder="Nhập mã giảm giá">
                                <button class="btn btn-outline-secondary" type="button">Áp dụng</button>
                            </div>
                            <p class="text-muted small mt-2">Mã giảm giá sẽ được áp dụng nếu hợp lệ.</p>
                        </div>

                        <div class="border rounded-3 p-3 bg-light mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính</span>
                                <strong><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển</span>
                                <strong><?php echo ($shipping === 0) ? 'Miễn phí' : number_format($shipping, 0, ',', '.') . '₫'; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Giảm giá</span>
                                <strong><?php echo ($discount === 0) ? '0₫' : '-' . number_format($discount, 0, ',', '.') . '₫'; ?></strong>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Tổng tiền</span>
                                <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">XÁC NHẬN ĐẶT HÀNG</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.payment-option {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    position: relative;
}

.payment-option input[type="radio"] {
    opacity: 0;
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    cursor: pointer;
}

.payment-option:hover {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.1);
}

.payment-option input[type="radio"]:checked + div {
    border: 1px solid #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.12);
}

.payment-option div {
    padding-left: 1rem;
}

.summary-card {
    position: sticky;
    top: 80px;
}

@media (max-width: 991px) {
    .summary-card {
        position: static;
        top: auto;
    }
}
</style>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>