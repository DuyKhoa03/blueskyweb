<?php include 'app/views/shares/header.php'; ?>

<div class="container">
    <h1 class="page-title">Trang Quản lý Admin</h1>
    <p>Chào mừng đến với trang quản lý dành cho admin!</p>

    <div class="row">
        <!-- Card Quản lý Người dùng -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Người dùng</h5>
                    <p class="card-text">Xem và chỉnh sửa thông tin người dùng.</p>
                    <a href="/blueskyweb/admin/users" class="btn btn-primary">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Sản phẩm -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-box-open fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Sản phẩm</h5>
                    <p class="card-text">Thêm, sửa, xóa sản phẩm.</p>
                    <a href="/blueskyweb/admin/products" class="btn btn-primary">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Danh mục -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Danh mục</h5>
                    <p class="card-text">Thêm, sửa, xóa danh mục.</p>
                    <a href="/blueskyweb/admin/categories" class="btn btn-primary">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Đơn hàng -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Đơn hàng</h5>
                    <p class="card-text">Xem danh sách đơn hàng.</p>
                    <a href="/blueskyweb/admin/orders" class="btn btn-primary">Quản lý</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>