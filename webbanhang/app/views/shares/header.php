<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng của bạn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --site-bg: #f3f5f8;
            --card-bg: #ffffff;
            --primary-color: #0d6efd;
            --secondary-color: #212529;
        }

        body {
            background-color: var(--site-bg);
        }

        .product-card {
            transition: all 0.25s ease-in-out;
            border: none !important;
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.12) !important;
            z-index: 1;
        }

        .product-card-img-container {
            height: 220px;
            overflow: hidden;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card-img {
            transition: transform 0.35s ease;
            object-fit: contain;
            max-height: 100%;
            width: auto;
        }

        .product-card:hover .product-card-img {
            transform: scale(1.05);
        }

        .btn-add-cart {
            transition: all 0.2s ease;
        }

        .btn-add-cart:hover {
            letter-spacing: 0.4px;
            box-shadow: 0 6px 18px rgba(13, 110, 253, 0.18);
        }

        .hero-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            color: #ffffff;
            border-radius: 18px;
        }

        .hero-banner .badge {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .navbar-brand {
            letter-spacing: 0.08em;
        }

        .top-alert-bar {
            background: #0d6efd;
            color: #fff;
            font-size: 0.95rem;
        }
    </style>
    </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-uppercase text-primary" href="/Product">
                <i class="bi bi-laptop me-2"></i>Shop IT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/Product">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Product">Sản phẩm</a></li>
                    <?php if (SessionHelper::isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="/Category">Danh mục</a></li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex mx-lg-3 flex-grow-1" method="GET" action="/Product/search">
                    <input class="form-control me-2" type="search" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Bạn tìm gì hôm nay?" aria-label="Search">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="/Product/cart">
                        <i class="bi bi-cart3"></i>
                        <?php 
                        $totalItems = 0;
                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $totalItems += $item['quantity'];
                            }
                        }
                        ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $totalItems; ?></span>
                    </a>
                    <?php if (SessionHelper::isLoggedIn()): ?>
                        <?php
                            $displayName = htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Khách', ENT_QUOTES, 'UTF-8');
                            $userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'user';
                            $roleLabel = $userRole === 'admin' ? 'Admin' : 'Thành viên';
                            $roleClass = $userRole === 'admin' ? 'bg-danger' : 'bg-secondary';
                        ?>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdownMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5 text-primary"></i>
                                <span class="small fw-semibold mb-0"><?php echo $displayName; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow mt-2" aria-labelledby="userDropdownMenu">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-badge-fill text-primary fs-4"></i>
                                        <div>
                                            <div class="fw-bold"><?php echo $displayName; ?></div>
                                            <span class="badge <?php echo $roleClass; ?> text-white text-uppercase small"><?php echo $roleLabel; ?></span>
                                        </div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2" href="/Account/profile"><i class="bi bi-person-circle"></i> Tài khoản của tôi</a></li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2" href="/Order/my_orders"><i class="bi bi-card-checklist"></i> Đơn hàng đã đặt</a></li>
                                <?php if ($userRole === 'admin'): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="/admin/index.php"><i class="bi bi-speedometer2"></i> Quản trị hệ thống</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="/Account/logout" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/Account/login" class="btn btn-outline-primary btn-sm">Đăng nhập</a>
                        <a href="/Account/register" class="btn btn-primary btn-sm">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="top-alert-bar py-2">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div><strong>Khuyến mãi hôm nay:</strong> Miễn phí giao hàng toàn quốc cho đơn hàng từ 2.000.000đ</div>
            <div>Hotline: <strong>1900 1234</strong> | Đổi trả trong 7 ngày</div>
        </div>
    </div>
    <div class="container mt-4">