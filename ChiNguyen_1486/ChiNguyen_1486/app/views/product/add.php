<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Thêm sản phẩm mới</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="addProductForm">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên sản phẩm:</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nhập tên sản phẩm..." required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả:</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Giá (VND):</label>
                    <input type="number" id="price" name="price" class="form-control" step="1000" required>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Danh mục:</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>"><?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Thêm sản phẩm</button>
                    <a href="/Product" class="btn btn-secondary px-4">Quay lại</a>
                </div>
            </form>

            <script>
            document.getElementById('addProductForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const token = localStorage.getItem('jwt_token') || '<?php echo $_SESSION['token'] ?? ""; ?>';
                const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
                
                const data = {
                    name: document.getElementById('name').value,
                    description: document.getElementById('description').value,
                    price: parseFloat(document.getElementById('price').value),
                    category_id: parseInt(document.getElementById('category_id').value)
                };
                
                fetch(basePath + '/api/product', {
                    method: 'POST',
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
                        alert(res.message || "Thêm sản phẩm thành công!");
                        window.location.href = basePath + '/Product';
                    }
                })
                .catch(err => {
                    console.error(err);
                });
            });
            </script>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>