<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <a href="/Product" class="btn btn-outline-secondary mb-4">← Quay lại danh sách</a>
    
    <div class="row">
        <div class="col-md-6">
            <?php
                $productImage = $product->image ?? '';
                $imageFile = $productImage && file_exists(BASE_PATH . '/' . ltrim($productImage, '/'))
                    ? '/' . ltrim($productImage, '/')
                    : 'https://via.placeholder.com/700x500?text=Hình+ảnh+không+tồn+tại';
            ?>
            <img src="<?php echo $imageFile; ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="col-md-6">
            <h2 class="mb-3"><?php echo htmlspecialchars($product->name); ?></h2>
            <p class="text-muted">Danh mục: <strong><?php echo htmlspecialchars($product->category_name); ?></strong></p>
            <h3 class="text-danger my-4"><?php echo number_format($product->price, 0, ',', '.'); ?> VND</h3>
            
            <hr>
            <p><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
            
            <?php if (!SessionHelper::isAdmin()): ?>
                <div class="mt-4">
                    <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn btn-danger btn-lg px-4 py-2.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="background-color: #d32f2f; border: none;">
                        <i class="bi bi-cart-plus-fill"></i> Thêm vào giỏ hàng
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (SessionHelper::isAdmin()): ?>
                <div class="mt-4">
                    <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning">Sửa sản phẩm</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>