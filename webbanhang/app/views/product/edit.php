<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h2 class="mb-0 fs-4 fw-bold">Sửa sản phẩm</h2>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/Product/update" enctype="multipart/form-data" onsubmit="return validateForm();">
                        <input type="hidden" name="id" value="<?php echo $product->id; ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên sản phẩm:</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả sản phẩm:</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label fw-bold">Giá bán (VND):</label>
                            <input type="number" id="price" name="price" class="form-control" step="1" value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold">Danh mục sản phẩm:</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->id; ?>" <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label fw-bold">Hình ảnh sản phẩm:</label>
                            <input type="file" id="image" name="image" class="form-control mb-2">
                            
                            <input type="hidden" name="existing_image" value="<?php echo $product->image; ?>">
                            
                            <?php if ($product->image): ?>
                                <div class="mt-2 text-center bg-light p-2 rounded border">
                                    <p class="small text-muted mb-1">Hình ảnh hiện tại trên hệ thống:</p>
                                    <img src="/<?php echo $product->image; ?>" alt="Product Image" style="max-width: 120px;" class="img-thumbnail shadow-sm">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Lưu thay đổi</button>
                            <a href="/Product" class="btn btn-outline-secondary">Quay lại danh sách sản phẩm</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Định nghĩa hàm validate kiểm tra dữ liệu client cơ bản tránh để trống
function validateForm() {
    let name = document.getElementById('name').value.trim();
    let price = document.getElementById('price').value;
    if (name === "") {
        alert("Tên sản phẩm không được phép để khoảng trắng.");
        return false;
    }
    if (price < 0) {
        alert("Giá sản phẩm không thể âm.");
        return false;
    }
    return true;
}
</script>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>