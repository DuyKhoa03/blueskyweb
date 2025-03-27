<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h1 class="page-title text-center mb-4">Trang Quản lý Admin</h1>
    <p class="text-center mb-5">Chào mừng đến với trang quản lý dành cho admin!</p>

    <div class="row justify-content-center g-4">
        <!-- Card Thống kê Doanh thu -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100 hover-effect d-flex flex-column">
                <div class="card-body text-center flex-grow-1">
                    <i class="fas fa-chart-line fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Thống kê Doanh thu</h5>
                    <p class="card-text">Xem báo cáo doanh thu và sản phẩm bán chạy.</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="/blueskyweb/admin/statistics" class="btn btn-success btn-block">Xem thống kê</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Người dùng -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100 hover-effect d-flex flex-column">
                <div class="card-body text-center flex-grow-1">
                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Người dùng</h5>
                    <p class="card-text">Xem và chỉnh sửa thông tin người dùng.</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="/blueskyweb/admin/users" class="btn btn-primary btn-block">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Sản phẩm -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100 hover-effect d-flex flex-column">
                <div class="card-body text-center flex-grow-1">
                    <i class="fas fa-box-open fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Sản phẩm</h5>
                    <p class="card-text">Thêm, sửa, xóa sản phẩm.</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="/blueskyweb/admin/products" class="btn btn-primary btn-block">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Danh mục -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100 hover-effect d-flex flex-column">
                <div class="card-body text-center flex-grow-1">
                    <i class="fas fa-tags fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Danh mục</h5>
                    <p class="card-text">Thêm, sửa, xóa danh mục.</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="/blueskyweb/admin/categories" class="btn btn-primary btn-block">Quản lý</a>
                </div>
            </div>
        </div>

        <!-- Card Quản lý Đơn hàng -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100 hover-effect d-flex flex-column">
                <div class="card-body text-center flex-grow-1">
                    <i class="fas fa-shopping-cart fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Quản lý Đơn hàng</h5>
                    <p class="card-text">Xem danh sách đơn hàng.</p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="/blueskyweb/admin/orders" class="btn btn-primary btn-block">Quản lý</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 700;
}

.card {
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.card-body {
    padding: 2rem;
}

.hover-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.btn-block {
    width: 100%;
    border-radius: 5px;
    padding: 8px 0;
}

.fas {
    transition: all 0.3s ease;
}

.card:hover .fas {
    transform: scale(1.1);
}

.card-title {
    color: #34495e;
    margin-bottom: 1rem;
}

.card-text {
    color: #7f8c8d;
    margin-bottom: 1rem;
}
</style>

<?php include 'app/views/shares/footer.php'; ?>