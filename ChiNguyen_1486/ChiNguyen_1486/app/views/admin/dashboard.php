<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard Quản trị</h1>
            <p class="text-muted mb-0">Thống kê hoạt động bán hàng và sản phẩm của hệ thống.</p>
        </div>
        <span class="badge bg-primary text-uppercase px-3 py-2 fw-bold">Live Data</span>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-5">
        <!-- Revenue Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold mb-1">Tổng Doanh Thu</div>
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>₫</h3>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-dark-50 small text-uppercase fw-bold mb-1">Đơn Chờ Duyệt</div>
                            <h3 class="fw-bold mb-0"><?php echo $stats['pending_orders']; ?> đơn</h3>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold mb-1">Tổng Sản Phẩm</div>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_products']; ?> SP</h3>
                        </div>
                        <i class="bi bi-laptop fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-info text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-bold mb-1">Tổng Thành Viên</div>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_users']; ?> TV</h3>
                        </div>
                        <i class="bi bi-people-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Links Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4">
            <h4 class="h5 fw-bold mb-3"><i class="bi bi-grid-3x3-gap-fill me-2 text-secondary"></i>Phân hệ quản trị nhanh</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="/Admin/orders" class="btn btn-outline-primary px-4 py-2 rounded-3"><i class="bi bi-receipt me-1"></i> Quản lý đơn đặt hàng</a>
                <a href="/Product" class="btn btn-outline-success px-4 py-2 rounded-3"><i class="bi bi-laptop me-1"></i> Quản lý sản phẩm (Trang chủ)</a>
                <a href="/Category" class="btn btn-outline-info px-4 py-2 rounded-3"><i class="bi bi-tags-fill me-1"></i> Quản lý danh mục sản phẩm</a>
                <a href="/Account/users" class="btn btn-outline-danger px-4 py-2 rounded-3"><i class="bi bi-people-fill"></i> Quản lý thành viên</a>
            </div>
        </div>
    </div>

    <!-- Top Selling Products Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <h3 class="h5 fw-bold mb-0 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 sản phẩm bán chạy nhất</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3 px-4">Ảnh</th>
                            <th scope="col" class="py-3">Tên sản phẩm</th>
                            <th scope="col" class="py-3">Đơn giá</th>
                            <th scope="col" class="py-3 text-center">Số lượng đã bán</th>
                            <th scope="col" class="py-3 text-end px-4">Tổng doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Chưa có dữ liệu bán hàng cho các sản phẩm.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $p): ?>
                                <tr>
                                    <td class="px-4">
                                        <?php if (!empty($p->image) && file_exists(BASE_PATH . '/' . ltrim($p->image, '/'))): ?>
                                            <img src="/<?php echo htmlspecialchars(ltrim($p->image, '/')); ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: contain;">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/50x50?text=SP" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/Product/show/<?php echo $p->id; ?>" class="fw-semibold text-decoration-none text-dark"><?php echo htmlspecialchars($p->name); ?></a>
                                    </td>
                                    <td><?php echo number_format($p->price, 0, ',', '.'); ?>₫</td>
                                    <td class="text-center fw-bold text-primary"><?php echo $p->total_sold; ?></td>
                                    <td class="text-end px-4 fw-bold text-success"><?php echo number_format($p->total_revenue, 0, ',', '.'); ?>₫</td>
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
