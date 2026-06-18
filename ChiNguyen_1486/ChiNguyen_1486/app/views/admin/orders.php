<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h4 fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Quản lý đơn hàng</h1>
                    <p class="mb-0 opacity-85">Danh sách các đơn đặt hàng trên toàn bộ hệ thống.</p>
                </div>
                <a href="/Admin" class="btn btn-light btn-sm text-primary fw-semibold"><i class="bi bi-arrow-left"></i> Dashboard</a>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3">Mã đơn</th>
                            <th scope="col" class="py-3">Tên khách hàng</th>
                            <th scope="col" class="py-3">Số điện thoại</th>
                            <th scope="col" class="py-3">Ngày đặt</th>
                            <th scope="col" class="py-3 text-end">Tổng tiền</th>
                            <th scope="col" class="py-3">PT Thanh toán</th>
                            <th scope="col" class="py-3 text-center">Trạng thái</th>
                            <th scope="col" class="py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có đơn hàng nào được tạo.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">#<?php echo $o->id; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($o->phone, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($o->created_at)); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($o->total_amount, 0, ',', '.'); ?>₫</td>
                                    <td>
                                        <span class="badge bg-light text-dark text-uppercase border"><?php echo htmlspecialchars($o->payment_method); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        $statusText = $o->status;
                                        if ($o->status === 'pending') {
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = 'Chờ duyệt';
                                        } elseif ($o->status === 'processing') {
                                            $statusClass = 'bg-primary text-white';
                                            $statusText = 'Đang xử lý';
                                        } elseif ($o->status === 'completed') {
                                            $statusClass = 'bg-success text-white';
                                            $statusText = 'Hoàn thành';
                                        } elseif ($o->status === 'canceled') {
                                            $statusClass = 'bg-danger text-white';
                                            $statusText = 'Đã hủy';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> px-2.5 py-1.5 fw-semibold"><?php echo $statusText; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/Admin/orderDetail/<?php echo $o->id; ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill"><i class="bi bi-eye"></i> Chi tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
