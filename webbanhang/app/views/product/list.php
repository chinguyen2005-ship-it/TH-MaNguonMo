<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="hero-banner p-4 mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <?php if (isset($_GET['keyword']) && $_GET['keyword'] !== ''): ?>
                    <h1 class="fw-bold mb-2">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($_GET['keyword'], ENT_QUOTES, 'UTF-8'); ?>"</h1>
                <?php else: ?>
                    <h1 class="fw-bold mb-2">Flash Sale / Danh sách sản phẩm</h1>
                <?php endif; ?>
                <p class="mb-0 text-white-75">Khám phá sản phẩm hot nhất với ưu đãi tốt nhất. Thêm vào giỏ hàng nhanh chóng và thanh toán tiện lợi.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if (SessionHelper::isAdmin()): ?>
                    <a href="/Product/add" class="btn btn-light btn-sm px-4">Thêm sản phẩm mới</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-5 border-0 shadow-sm bg-light">
        <div class="card-body p-3">
            <form method="GET" action="/Product" class="row g-3 align-items-center">
                <div class="col-md-4 col-sm-8">
                    <label for="category_id" class="form-label fw-bold text-secondary small">Tìm kiếm sản phẩm theo phân loại:</label>
                    <select name="category_id" id="category_id" class="form-select border-0 shadow-sm" onchange="this.form.submit()">
                        <option value="">-- Hiển thị tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4 align-self-end">
                    <button type="submit" class="btn btn-dark w-100 shadow-sm">
                        <i class="bi bi-filter"></i> Áp dụng lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($products)): ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-3"> <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border border-light position-relative product-card bg-white rounded-3 overflow-hidden d-flex flex-column">
                        
                        <span class="position-absolute badge bg-warning text-dark px-2 py-1 rounded-end shadow-sm" style="top: 12px; left: 0; z-index: 2; font-size: 11px; font-weight: bold;">
                            Trả góp 0%
                        </span>

                        <div class="product-card-img-container p-3 d-flex align-items-center justify-content-center bg-white" style="height: 180px;">
                            <?php
                                $productImage = $product->image ?? '';
                                $imagePath = $productImage && file_exists(BASE_PATH . '/' . ltrim($productImage, '/'))
                                    ? '/' . ltrim($productImage, '/')
                                    : 'https://via.placeholder.com/400x300?text=No+Image';
                            ?>
                            <img src="<?php echo $imagePath; ?>" class="card-img-top product-card-img" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" style="object-fit: contain; max-height: 100%; max-width: 100%;">
                        </div>
                        
                        <div class="card-body p-3 d-flex flex-column flex-grow-1 border-top border-light">
                            
                            <h5 class="card-title mb-1" style="font-size: 14px; line-height: 20px; height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <a href="/Product/show/<?php echo $product->id; ?>" class="text-decoration-none text-dark fw-bold">
                                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted mb-3" style="font-size: 11px;"><i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></p>
                            
                            <div class="mt-auto mb-2">
                                <p class="text-muted text-decoration-line-through small mb-0" style="font-size: 12px;">
                                    <?php echo number_format($product->price * 1.1, 0, ',', '.'); ?>₫
                                </p>
                                <p class="fw-bold text-danger fs-5 mb-0" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                    <?php echo number_format($product->price, 0, ',', '.'); ?>₫
                                </p>
                            </div>

                            <div class="d-flex flex-column gap-2 mt-2">
                                <?php if (SessionHelper::isAdmin()): ?>
                                    <div class="d-flex gap-1">
                                        <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-outline-warning btn-xs py-1 flex-fill style-admin-btn" style="font-size: 11px;">Sửa</a>
                                        <a href="/Product/delete/<?php echo $product->id; ?>" class="btn btn-outline-danger btn-xs py-1 flex-fill style-admin-btn" style="font-size: 11px;" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn btn-danger w-100 btn-add-cart rounded-0 py-2 fw-bold" style="font-size: 13px; background-color: #d32f2f; border: none;">
                            Thêm vào giỏ
                        </a>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center py-5 border-0 shadow-sm my-4">
            <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
            Không tìm thấy sản phẩm nào phù hợp!
        </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>