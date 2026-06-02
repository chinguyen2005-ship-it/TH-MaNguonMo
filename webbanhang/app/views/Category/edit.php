<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Sửa danh mục</h2>
            <form method="POST" action="/Category/update">
                <input type="hidden" name="id" value="<?php echo $category->id; ?>">
                
                <div class="mb-3">
                    <label>Tên danh mục:</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($category->name); ?>" required>
                </div>
                <div class="mb-3">
                    <label>Mô tả:</label>
                    <textarea name="description" class="form-control"><?php echo htmlspecialchars($category->description); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="/Category" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>