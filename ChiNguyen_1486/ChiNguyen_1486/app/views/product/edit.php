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

                    <form id="editProductForm">
                        <input type="hidden" id="productId" value="<?php echo $product->id; ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên sản phẩm:</label>
                            <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả sản phẩm:</label>
                            <textarea id="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label fw-bold">Giá bán (VND):</label>
                            <input type="number" id="price" class="form-control" step="1" value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold">Danh mục sản phẩm:</label>
                            <select id="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->id; ?>" <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Lưu thay đổi</button>
                            <a href="/Product" class="btn btn-outline-secondary">Quay lại danh sách sản phẩm</a>
                        </div>
                    </form>

                    <script>
                    document.getElementById('editProductForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const token = localStorage.getItem('jwt_token') || '<?php echo $_SESSION['token'] ?? ""; ?>';
                        const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
                        const productId = document.getElementById('productId').value;
                        
                        const data = {
                            name: document.getElementById('name').value,
                            description: document.getElementById('description').value,
                            price: parseFloat(document.getElementById('price').value),
                            category_id: parseInt(document.getElementById('category_id').value)
                        };
                        
                        fetch(basePath + '/api/product/' + productId, {
                            method: 'PUT',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('jwt_token'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => {
                            if (response.status === 401) {
                                alert("Hết hạn phiên làm việc hoặc chưa đăng nhập. Vui lòng đăng nhập lại.");
                                window.location.href = basePath + '/account/login';
                                throw new Error('Unauthorized');
                            }
                            return response.json();
                        })
                        .then(res => {
                            if (res.status === false || res.errors || res.message === undefined) {
                                alert("Lỗi: " + (res.message || JSON.stringify(res.errors || res)));
                            } else {
                                alert(res.message || "Cập nhật sản phẩm thành công!");
                                window.location.href = basePath + '/Product';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                        });
                    });
                    </script>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>