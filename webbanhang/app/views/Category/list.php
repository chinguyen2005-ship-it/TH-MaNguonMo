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
                            
                            <a href="/Category/delete/<?php echo $cat->id; ?>" 
                               class="btn btn-sm btn-danger px-3" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Hành động này không thể hoàn tác!');">Xóa</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>