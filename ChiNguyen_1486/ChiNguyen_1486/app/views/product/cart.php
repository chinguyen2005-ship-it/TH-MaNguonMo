<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Giỏ hàng của bạn</h1>
    <?php if (!empty($_SESSION['checkout_error'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['checkout_error'], ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['checkout_error']); ?>
    <?php endif; ?>
    <?php if (!empty($cart)): ?>
        <form method="POST" action="/Order/checkout">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle shadow-sm">
                    <thead class="table-dark text-center">
                        <tr>
                            <th style="width: 8%;">Chọn</th>
                            <th style="width: 12%;">Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th style="width: 15%;">Giá</th>
                            <th style="width: 10%;">Số lượng</th>
                            <th style="width: 15%;">Tổng cộng</th>
                            <th style="width: 12%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $id => $item): ?>
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" name="selected[]" value="<?php echo $id; ?>" class="form-check-input">
                            </td>
                            <td class="text-center">
                                <?php if ($item['image']): ?>
                                    <img src="/<?php echo $item['image']; ?>" alt="Product Image" style="max-width: 80px;" class="rounded shadow-sm">
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-secondary"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-danger fw-bold text-center"><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="/Product/updateCartQuantity/<?php echo $id; ?>/decrease" class="btn btn-sm btn-outline-secondary px-2 py-0.5 fw-bold" style="font-size: 14px;">-</a>
                                    <span class="fw-bold text-secondary px-1" style="min-width: 20px; display: inline-block;"><?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <a href="/Product/updateCartQuantity/<?php echo $id; ?>/increase" class="btn btn-sm btn-outline-secondary px-2 py-0.5 fw-bold" style="font-size: 14px;">+</a>
                                </div>
                            </td>
                            <td class="text-danger fw-bold text-center">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VND
                            </td>
                            <td class="text-center">
                                <a href="/Product/removeFromCart/<?php echo $id; ?>" 
                                   class="btn btn-sm btn-danger px-3" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?');">
                                    <i class="bi bi-trash3-fill"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 mt-4 flex-column flex-md-row">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Chọn các sản phẩm cần mua</button>
                    <a href="/Product" class="btn btn-secondary px-4">← Tiếp tục mua sắm</a>
                </div>
                <span class="text-muted small">Chọn sản phẩm rồi nhấn nút để thanh toán chỉ các mục đã chọn.</span>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center py-4 shadow-sm border-0">
            <p class="mb-0 fs-5">Giỏ hàng của bạn đang trống.</p>
            <a href="/Product" class="btn btn-primary mt-3">Mua sắm ngay!</a>
        </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>