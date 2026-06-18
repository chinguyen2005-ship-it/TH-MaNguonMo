<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Thêm danh mục mới</h2>
            
            <form id="addCategoryForm">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên danh mục:</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: Đồ điện tử, Gia dụng..." required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả:</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Nhập mô tả ngắn..."></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Lưu danh mục</button>
                    <a href="/Category" class="btn btn-secondary px-4">Quay lại</a>
                </div>
            </form>

            <script>
            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const token = localStorage.getItem('jwt_token') || '<?php echo $_SESSION['token'] ?? ""; ?>';
                const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
                
                const data = {
                    name: document.getElementById('name').value,
                    description: document.getElementById('description').value
                };
                
                fetch(basePath + '/api/category', {
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
                    if (res.status === false || res.message === undefined) {
                        alert("Lỗi: " + (res.message || JSON.stringify(res)));
                    } else {
                        alert(res.message || "Thêm danh mục thành công!");
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

<?php include 'app/views/shares/footer.php'; ?>