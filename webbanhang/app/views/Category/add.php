<?php include 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Thêm danh mục mới</h2>
            
            <form method="POST" action="/Category/save">
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
                    <a href="/Category/list" class="btn btn-secondary px-4">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>