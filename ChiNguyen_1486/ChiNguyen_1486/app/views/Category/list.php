<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Quản lý Danh mục</h2>
        <a href="/Category/add" class="btn btn-primary">Thêm danh mục</a>
    </div>

    <table class="table table-bordered table-hover shadow-sm align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 25%;">Tên danh mục</th>
                <th>Mô tả</th>
                <th style="width: 15%; text-align: center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Chưa có danh mục nào trên hệ thống.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?php echo $cat->id; ?></td>
                    <td class="fw-bold text-secondary"><?php echo htmlspecialchars($cat->name); ?></td>
                    <td><?php echo htmlspecialchars($cat->description ?? ''); ?></td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="/Category/edit/<?php echo $cat->id; ?>" class="btn btn-sm btn-warning px-3">Sửa</a>
                            
                            <button class="btn btn-sm btn-danger px-3 btn-delete-cat" data-id="<?php echo $cat->id; ?>">Xóa</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.btn-delete-cat').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        if (confirm('Bạn có chắc chắn muốn xóa danh mục này? Hành động này không thể hoàn tác!')) {
            const token = localStorage.getItem('jwt_token') || '<?php echo $_SESSION['token'] ?? ""; ?>';
            const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
            
            fetch(basePath + '/api/category/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('jwt_token'),
                    'Content-Type': 'application/json'
                }
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
                if (res.status === false) {
                    alert("Lỗi: " + res.message);
                } else {
                    alert(res.message || "Xóa danh mục thành công!");
                    window.location.reload();
                }
            })
            .catch(err => {
                console.error(err);
            });
        }
    });
});
</script>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>