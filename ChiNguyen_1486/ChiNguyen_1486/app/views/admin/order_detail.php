<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="mb-4 d-flex gap-2">
        <a href="/Admin/orders" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
        <a href="/Admin" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>

    <!-- Alert notifications -->
    <?php if (isset($_SESSION['admin_order_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['admin_order_success']; unset($_SESSION['admin_order_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['admin_order_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['admin_order_error']; unset($_SESSION['admin_order_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Recipient Information and Status Action -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h3 class="h6 fw-bold mb-0 text-dark"><i class="bi bi-person-fill text-primary me-2"></i>Thông tin giao hàng</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 py-2.5">
                            <span class="text-muted">Mã đơn hàng:</span>
                            <span class="fw-bold text-dark">#<?php echo $order->id; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 py-2.5">
                            <span class="text-muted">Người nhận:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($order->name); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 py-2.5">
                            <span class="text-muted">Số điện thoại:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($order->phone); ?></span>
                        </li>
                        <li class="list-group-item px-0 py-2.5">
                            <div class="text-muted mb-1">Địa chỉ giao:</div>
                            <div class="fw-semibold text-dark border p-2 rounded bg-light"><?php echo htmlspecialchars($order->address); ?></div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 py-2.5">
                            <span class="text-muted">PT Thanh toán:</span>
                            <span class="badge bg-light text-dark text-uppercase border fw-semibold"><?php echo htmlspecialchars($order->payment_method); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 py-2.5">
                            <span class="text-muted">Ngày đặt hàng:</span>
                            <span class="fw-semibold text-dark"><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></span>
                        </li>
                        <li class="list-group-item px-0 py-2.5">
                            <div class="text-muted mb-1">Ghi chú của khách:</div>
                            <div class="text-dark font-monospace border p-2 rounded bg-light small" style="white-space: pre-wrap;"><?php echo !empty($order->note) ? htmlspecialchars($order->note) : 'Không có ghi chú'; ?></div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Update Order Status Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light py-3 border-0">
                    <h3 class="h6 fw-bold mb-0 text-dark"><i class="bi bi-gear-fill text-warning me-2"></i>Trạng thái đơn hàng</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center justify-content-between">
                        <span class="text-muted small">Trạng thái hiện tại:</span>
                        <?php
                        $statusClass = 'bg-secondary';
                        $statusText = $order->status;
                        if ($order->status === 'pending') {
                            $statusClass = 'bg-warning text-dark';
                            $statusText = 'Chờ duyệt';
                        } elseif ($order->status === 'processing') {
                            $statusClass = 'bg-primary text-white';
                            $statusText = 'Đang xử lý';
                        } elseif ($order->status === 'completed') {
                            $statusClass = 'bg-success text-white';
                            $statusText = 'Hoàn thành';
                        } elseif ($order->status === 'canceled') {
                            $statusClass = 'bg-danger text-white';
                            $statusText = 'Đã hủy';
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?> px-3 py-2 fw-semibold fs-6"><?php echo $statusText; ?></span>
                    </div>

                    <form action="/Admin/updateOrderStatus" method="POST" class="border-top pt-3">
                        <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold text-secondary small">Cập nhật sang trạng thái mới:</label>
                            <select name="status" id="status" class="form-select border shadow-sm">
                                <option value="pending" <?php echo ($order->status === 'pending') ? 'selected' : ''; ?>>Chờ duyệt (Pending)</option>
                                <option value="processing" <?php echo ($order->status === 'processing') ? 'selected' : ''; ?>>Đang xử lý (Processing)</option>
                                <option value="completed" <?php echo ($order->status === 'completed') ? 'selected' : ''; ?>>Hoàn thành (Completed)</option>
                                <option value="canceled" <?php echo ($order->status === 'canceled') ? 'selected' : ''; ?>>Đã hủy (Canceled)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2"><i class="bi bi-save-fill"></i> Cập nhật trạng thái</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ordered Items list -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h3 class="h5 fw-bold mb-0 text-dark"><i class="bi bi-cart-check-fill text-success me-2"></i>Danh sách sản phẩm đã đặt</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Ảnh</th>
                                    <th scope="col" class="py-3">Tên sản phẩm</th>
                                    <th scope="col" class="py-3 text-end">Giá mua</th>
                                    <th scope="col" class="py-3 text-center">Số lượng</th>
                                    <th scope="col" class="py-3 text-end px-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalOrderPrice = 0;
                                foreach ($items as $item): 
                                    $subtotal = $item->price * $item->quantity;
                                    $totalOrderPrice += $subtotal;
                                ?>
                                    <tr>
                                        <td class="px-4">
                                            <?php if (!empty($item->product_image) && file_exists(BASE_PATH . '/' . ltrim($item->product_image, '/'))): ?>
                                                <img src="/<?php echo htmlspecialchars(ltrim($item->product_image, '/')); ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: contain;">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/50x50?text=SP" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/Product/show/<?php echo $item->product_id; ?>" class="fw-semibold text-decoration-none text-dark"><?php echo htmlspecialchars($item->product_name); ?></a>
                                        </td>
                                        <td class="text-end"><?php echo number_format($item->price, 0, ',', '.'); ?>₫</td>
                                        <td class="text-center fw-bold"><?php echo $item->quantity; ?></td>
                                        <td class="text-end px-4 fw-bold text-dark"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end py-3 fw-bold fs-6">Tổng cộng giá trị đơn hàng:</td>
                                    <td class="text-end px-4 py-3 fw-bold text-danger fs-5"><?php echo number_format($totalOrderPrice, 0, ',', '.'); ?>₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
