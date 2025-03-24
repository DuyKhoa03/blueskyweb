</div> <!-- Đóng div container-fluid từ header.php -->

<!-- Footer -->
<footer class="footer mt-5 py-4 bg-dark text-light">
    <div class="container">
        <div class="row">
            <!-- Cột 1: Liên kết nhanh -->
            <div class="col-md-6 mb-4">
                <h5 class="footer-title text-uppercase mb-3">Liên kết nhanh</h5>
                <ul class="list-unstyled">
                    <li><a href="/blueskyweb/Product" class="footer-link"><i class="fas fa-chevron-right mr-2"></i>Sản phẩm</a></li>
                    <li><a href="/blueskyweb/Cart" class="footer-link"><i class="fas fa-chevron-right mr-2"></i>Giỏ hàng</a></li>
                </ul>
            </div>

            <!-- Cột 2: Liên hệ -->
            <div class="col-md-6 mb-4">
                <h5 class="footer-title text-uppercase mb-3">Liên hệ</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope mr-2"></i>support@blueskyshop.com</li>
                    <li><i class="fas fa-phone mr-2"></i>(+84) 123 456 789</li>
                </ul>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="text-center">
            <p class="mb-0 text-muted">© <?php echo date('Y'); ?> BlueSky Shop. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Thêm CSS để cải thiện giao diện -->
<style>
.footer {
    background-color: #343a40; /* Màu nền tối */
    color: #adb5bd; /* Màu chữ nhạt */
    padding-top: 3rem;
    padding-bottom: 3rem;
    border-top: 4px solid #007bff; /* Viền trên màu xanh */
}

.footer-title {
    color: #ffffff; /* Màu tiêu đề trắng */
    font-weight: 600;
    letter-spacing: 1px;
}

.footer-link {
    color: #adb5bd; /* Màu liên kết */
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #007bff; /* Màu khi hover */
    text-decoration: none;
}

.footer-link i {
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.footer-link:hover i {
    transform: translateX(5px); /* Hiệu ứng di chuyển mũi tên khi hover */
}

.footer-divider {
    border-color: #495057; /* Màu đường phân cách */
    margin: 1.5rem 0;
}

.text-muted {
    color: #6c757d !important; /* Màu chữ bản quyền */
    font-size: 0.9rem;
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>