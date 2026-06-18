<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Sửa danh mục</h2>
            <form id="editCategoryForm">
                <input type="hidden" id="categoryId" value="<?php echo $category->id; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục:</label>
                    <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả:</label>
                    <textarea id="description" class="form-control"><?php echo htmlspecialchars($category->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Cập nhật</button>
                    <a href="/Category" class="btn btn-secondary px-4">Quay lại</a>
                </div>
            </form>

            <script>
            document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const token = localStorage.getItem('jwt_token') || '<?php echo $_SESSION['token'] ?? ""; ?>';
                const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
                const categoryId = document.getElementById('categoryId').value;
                
                const data = {
                    name: document.getElementById('name').value,
                    description: document.getElementById('description').value
                };
                
                fetch(basePath + '/api/category/' + categoryId, {
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
                    if (res.status === false || res.message === undefined) {
                        alert("Lỗi: " + (res.message || JSON.stringify(res)));
                    } else {
                        alert(res.message || "Cập nhật danh mục thành công!");
                        window.location.href = basePath + '/Category';
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

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>