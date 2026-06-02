<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Đơn hàng của bạn</h1>
                            <p class="text-muted mb-0">Xem lịch sử đơn hàng và trạng thái đơn hàng mới nhất.</p>
                        </div>
                        <span class="badge bg-primary fs-6">Tính năng sắp có</span>
                    </div>

                    <div class="alert alert-info rounded-4 border-0 shadow-sm">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-clock-history fs-2"></i>
                            <div>
                                <strong>Chúng tôi đang nâng cấp trang đơn hàng.</strong>
                                <p class="mb-0">Hiện tại chức năng này được xây dựng để đảm bảo an toàn và chính xác khi liên kết đơn hàng với tài khoản của bạn. Vui lòng quay lại sau vài phút.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <i class="bi bi-card-checklist fs-2 text-primary"></i>
                                    <div>
                                        <h2 class="h6 mb-1">Theo dõi đơn hàng</h2>
                                        <p class="text-muted small mb-0">Chúng tôi sẽ cập nhật khi chức năng hoàn thiện.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <i class="bi bi-gear-wide-connected fs-2 text-success"></i>
                                    <div>
                                        <h2 class="h6 mb-1">Kết nối tài khoản</h2>
                                        <p class="text-muted small mb-0">Khi đơn hàng được liên kết, bạn sẽ xem ngay tại đây.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="/Product" class="btn btn-outline-primary btn-lg">Quay lại cửa hàng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>
