<?php include BASE_PATH . '/app/views/shares/header.php'; ?>

<div class="container my-5">
    <?php if (isset($_SESSION['access_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Thông báo:</strong> <?php echo $_SESSION['access_error']; unset($_SESSION['access_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="hero-banner p-4 mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <?php if (isset($_GET['keyword']) && $_GET['keyword'] !== ''): ?>
                    <h1 class="fw-bold mb-2">Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($_GET['keyword'], ENT_QUOTES, 'UTF-8'); ?>"</h1>
                <?php else: ?>
                    <h1 class="fw-bold mb-2">Flash Sale / Danh sách sản phẩm</h1>
                <?php endif; ?>
                <p class="mb-0 text-white-75">Khám phá sản phẩm hot nhất với ưu đãi tốt nhất. Thêm vào giỏ hàng nhanh chóng và thanh toán tiện lợi.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if (SessionHelper::isAdmin()): ?>
                    <a href="/Product/add" class="btn btn-light btn-sm px-4">Thêm sản phẩm mới</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

<style>
    .category-pill {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(15, 23, 42, 0.08) !important;
        border-radius: 30px;
        background-color: #ffffff;
        cursor: pointer;
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none !important;
    }
    .category-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
    .category-pill.active-category {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2) !important;
    }
    .category-pill.active-category .category-icon {
        color: #ffffff !important;
    }
    .category-pill .category-icon {
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        color: var(--primary-color);
        transition: transform 0.2s ease;
    }
    .category-pill:hover .category-icon {
        transform: scale(1.1);
    }
</style>

    <div class="mb-4">
        <div class="p-3 bg-white rounded-4 shadow-sm border" style="border-color: rgba(15, 23, 42, 0.05) !important;">
            <h5 class="fw-bold text-dark mb-3" style="font-size: 16px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">Danh mục nổi bật</h5>
            
            <div class="d-flex flex-wrap gap-2">
                <!-- Pill "Tất cả" -->
                <a href="/Product" class="category-pill <?php echo (!isset($_GET['category_id']) || $_GET['category_id'] === '') ? 'active-category' : 'text-dark'; ?>">
                    <span class="category-icon">
                        <i class="bi bi-grid-fill"></i>
                    </span>
                    <span>Tất cả</span>
                </a>

                <?php foreach ($categories as $cat): ?>
                    <?php 
                        $isActive = (isset($_GET['category_id']) && $_GET['category_id'] == $cat->id);
                        
                        // Lựa chọn icon dựa trên tên danh mục để giao diện sinh động
                        $iconClass = 'bi-tag-fill';
                        $catNameLower = mb_strtolower($cat->name, 'UTF-8');
                        if (strpos($catNameLower, 'laptop') !== false) {
                            $iconClass = 'bi-laptop';
                        } elseif (strpos($catNameLower, 'điện thoại') !== false || strpos($catNameLower, 'phone') !== false || strpos($catNameLower, 'di dong') !== false || strpos($catNameLower, 'di động') !== false) {
                            $iconClass = 'bi-phone';
                        } elseif (strpos($catNameLower, 'tablet') !== false || strpos($catNameLower, 'máy tính bảng') !== false || strpos($catNameLower, 'ipad') !== false) {
                            $iconClass = 'bi-tablet';
                        } elseif (strpos($catNameLower, 'phụ kiện') !== false || strpos($catNameLower, 'accessories') !== false || strpos($catNameLower, 'tai nghe') !== false || strpos($catNameLower, 'chuột') !== false || strpos($catNameLower, 'bàn phím') !== false || strpos($catNameLower, 'sạc') !== false) {
                            $iconClass = 'bi-headset';
                        } elseif (strpos($catNameLower, 'đồng hồ') !== false || strpos($catNameLower, 'watch') !== false) {
                            $iconClass = 'bi-watch';
                        } elseif (strpos($catNameLower, 'tivi') !== false || strpos($catNameLower, 'tv') !== false || strpos($catNameLower, 'màn hình') !== false) {
                            $iconClass = 'bi-tv';
                        }
                    ?>
                    <a href="/Product?category_id=<?php echo $cat->id; ?>" class="category-pill <?php echo $isActive ? 'active-category' : 'text-dark'; ?>">
                        <span class="category-icon">
                            <i class="bi <?php echo $iconClass; ?>"></i>
                        </span>
                        <span><?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (SessionHelper::isAdmin()): ?>
        <h3 class="fw-bold text-dark mb-3">Tất cả sản phẩm ( <span id="product-count">0</span> )</h3>
        <div id="product-list-container" class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-3">
            <!-- Sản phẩm sẽ được tải động qua jQuery AJAX ở đây -->
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(document).ready(function() {
             // Đồng bộ token từ Session vào localStorage nếu có
             const sessionToken = '<?php echo $_SESSION['token'] ?? ""; ?>';
             if (sessionToken) {
                 localStorage.setItem('jwt_token', sessionToken);
             }
 
             // Xóa sạch token khi người dùng click đăng xuất
             $('a[href*="/Account/logout"]').on('click', function() {
                 localStorage.removeItem('jwt_token');
             });
 
             const basePath = '<?php echo str_replace("\\", "/", rtrim(dirname($_SERVER['SCRIPT_NAME']), "/\\")); ?>';
             const API_URL = basePath + '/api/product';
             const token = localStorage.getItem('jwt_token') || '';
 
             function loadProducts() {
                 const urlParams = new URLSearchParams(window.location.search);
                 const categoryId = urlParams.get('category_id');
                 const keyword = urlParams.get('keyword');
                 
                 let dataParams = {};
                 if (categoryId) dataParams.category_id = categoryId;
                 if (keyword) dataParams.keyword = keyword;
 
                 $.ajax({
                     url: API_URL,
                     method: 'GET',
                     dataType: 'json',
                     headers: {
                         'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
                     },
                    data: dataParams,
                    success: function(response) {
                        const data = Array.isArray(response) ? response : (response.data || []);
                        const totalCount = Array.isArray(response) ? response.length : (response.total_items ?? data.length);
                        
                        // Cập nhật số lượng sản phẩm động bằng jQuery
                        $('#product-count').text(totalCount);

                        const $container = $('#product-list-container');
                        $container.empty();

                        if (data.length === 0) {
                            $container.html(`
                                <div class="col-12 w-100">
                                    <div class="alert alert-warning text-center py-5 border-0 shadow-sm my-4">
                                        <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                                        Không tìm thấy sản phẩm nào phù hợp!
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        data.forEach(function(product) {
                            // Xử lý chuỗi đường dẫn ảnh mặc định hoặc thực tế
                            let imagePath = 'https://via.placeholder.com/400x300?text=No+Image';
                            if (product.image) {
                                imagePath = product.image.startsWith('http') ? product.image : basePath + '/' + product.image.replace(/^\//, '');
                            }

                            const productHtml = `
                                <div class="col">
                                    <div class="card h-100 position-relative product-card bg-white d-flex flex-column">
                                        <span class="position-absolute badge bg-warning text-dark px-2 py-1 rounded-end shadow-sm" style="top: 12px; left: 0; z-index: 2; font-size: 11px; font-weight: bold;">
                                            Trả góp 0%
                                        </span>

                                        <div class="product-card-img-container p-3 d-flex align-items-center justify-content-center" style="height: 180px; background-color: #f8fafc; border-bottom: 1px solid rgba(15, 23, 42, 0.04);">
                                            <img src="${imagePath}" class="card-img-top product-card-img" alt="${escapeHtml(product.name)}" style="object-fit: contain; max-height: 100%; max-width: 100%;">
                                        </div>
                                        
                                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                            <h5 class="card-title mb-1" style="font-size: 14px; line-height: 20px; height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <a href="${basePath}/Product/show/${product.id}" class="text-decoration-none text-dark fw-bold">${escapeHtml(product.name)}</a>
                                            </h5>
                                            
                                            <p class="text-muted mb-2" style="font-size: 11px;">
                                                <i class="bi bi-tag-fill me-1"></i>${escapeHtml(product.category_name || 'Chưa phân loại')}
                                            </p>
                                            
                                            <!-- Mô tả sản phẩm gán qua html() dưới đây để hiển thị đúng thực thể HTML như nháy kép -->
                                            <p class="product-description-container text-muted mb-2 small" style="font-size: 12px; line-height: 1.4; height: 36px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"></p>

                                            <div class="mt-auto pt-2">
                                                <div class="mb-3">
                                                    <p class="text-muted text-decoration-line-through small mb-0" style="font-size: 11px; opacity: 0.8;">
                                                        ${formatCurrency(product.price * 1.1)}₫
                                                    </p>
                                                    <p class="fw-bold text-danger fs-5 mb-0" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                                        ${formatCurrency(product.price)}₫
                                                    </p>
                                                </div>

                                                <div class="d-flex flex-column gap-2">
                                                    <div class="d-flex gap-1">
                                                        <a href="${basePath}/Product/edit/${product.id}" class="btn btn-outline-warning btn-xs py-1.5 flex-fill style-admin-btn" style="font-size: 11px; border-radius: 8px;">Sửa</a>
                                                        <button class="btn btn-outline-danger btn-xs py-1.5 flex-fill style-admin-btn btn-delete-product" data-id="${product.id}" style="font-size: 11px; border-radius: 8px;">Xóa</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            const $col = $(productHtml);

                            // SỬA LỖI HIỂN THỊ KÝ TỰ ĐẶC BIỆT: Gán nội dung mô tả bằng html() để trình duyệt giải mã thực thể HTML thô như &quot;
                            $col.find('.product-description-container').html(product.description || '');

                            $container.append($col);
                        });

                        // Gắn sự kiện xóa với confirm chống bấm nhầm dùng jQuery
                        $('.btn-delete-product').off('click').on('click', function() {
                            const id = $(this).attr('data-id');
                            deleteProduct(id);
                        });
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            alert("Hết hạn phiên làm việc hoặc chưa đăng nhập. Vui lòng đăng nhập lại.");
                            window.location.href = basePath + '/account/login';
                            return;
                        }
                        console.error('Lỗi tải sản phẩm:', error);
                        $('#product-list-container').html(`<div class="col-12 text-center text-danger py-4">Có lỗi xảy ra khi tải danh sách sản phẩm.</div>`);
                    }
                });
            }

             function deleteProduct(id) {
                 if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
                     $.ajax({
                         url: API_URL + '/' + id,
                         method: 'DELETE',
                         dataType: 'json',
                         headers: {
                             'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
                         },
                         success: function(data) {
                             loadProducts(); // Load lại sản phẩm sau khi xóa thành công
                         },
                         error: function(xhr, status, error) {
                             if (xhr.status === 401) {
                                 alert("Hết hạn phiên làm việc hoặc chưa đăng nhập. Vui lòng đăng nhập lại.");
                                 window.location.href = basePath + '/account/login';
                                 return;
                             }
                             alert('Xóa sản phẩm thất bại: ' + (xhr.responseJSON?.message || error));
                         }
                     });
                 }
             }

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }

            function formatCurrency(val) {
                return new Intl.NumberFormat('vi-VN').format(val);
            }

            // Gọi tải danh sách sản phẩm lần đầu
            loadProducts();
        });
        </script>
    <?php else: ?>
        <!-- Standard PHP rendering for normal users -->
        <?php if (!empty($products)): ?>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-3"> <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 position-relative product-card bg-white d-flex flex-column">
                            
                            <span class="position-absolute badge bg-warning text-dark px-2 py-1 rounded-end shadow-sm" style="top: 12px; left: 0; z-index: 2; font-size: 11px; font-weight: bold;">
                                Trả góp 0%
                            </span>

                            <div class="product-card-img-container p-3 d-flex align-items-center justify-content-center" style="height: 180px; background-color: #f8fafc; border-bottom: 1px solid rgba(15, 23, 42, 0.04);">
                                <?php
                                    $productImage = $product->image ?? '';
                                    $imagePath = $productImage && file_exists(BASE_PATH . '/' . ltrim($productImage, '/'))
                                        ? '/' . ltrim($productImage, '/')
                                        : 'https://via.placeholder.com/400x300?text=No+Image';
                                ?>
                                <img src="<?php echo $imagePath; ?>" class="card-img-top product-card-img" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" style="object-fit: contain; max-height: 100%; max-width: 100%;">
                            </div>
                            
                            <div class="card-body p-3 d-flex flex-column flex-grow-1">
                                
                                <h5 class="card-title mb-1" style="font-size: 14px; line-height: 20px; height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <a href="/Product/show/<?php echo $product->id; ?>" class="text-decoration-none text-dark fw-bold">
                                        <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h5>
                                
                                <p class="text-muted mb-2" style="font-size: 11px;"><i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></p>
                                
                                <div class="mt-auto pt-2">
                                    <div class="mb-3">
                                        <p class="text-muted text-decoration-line-through small mb-0" style="font-size: 11px; opacity: 0.8;">
                                            <?php echo number_format($product->price * 1.1, 0, ',', '.'); ?>₫
                                        </p>
                                        <p class="fw-bold text-danger fs-5 mb-0" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                            <?php echo number_format($product->price, 0, ',', '.'); ?>₫
                                        </p>
                                    </div>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn btn-primary w-100 rounded-3 py-2 fw-bold btn-add-cart d-flex align-items-center justify-content-center gap-1" style="font-size: 12px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none;">
                                            <i class="bi bi-cart-plus-fill"></i> Thêm vào giỏ
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center py-5 border-0 shadow-sm my-4">
                <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                Không tìm thấy sản phẩm nào phù hợp!
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/app/views/shares/footer.php'; ?>