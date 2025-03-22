<?php 
include 'app/views/shares/header.php'; 
// Khởi động session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Lấy token từ session (nếu có)
$token = $_SESSION['jwtToken'] ?? null;
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Danh mục sản phẩm</h1>
    </div>

    <!-- Danh sách danh mục -->
    <div class="row" id="category-list">
        <!-- Danh sách danh mục sẽ được tải từ API và hiển thị tại đây -->
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script>
// Chèn token từ PHP vào JavaScript
const token = <?php echo json_encode($token); ?>;

document.addEventListener("DOMContentLoaded", function () {
    // Không cần kiểm tra token vì đây là trang công khai cho user
    // Tuy nhiên, nếu API yêu cầu token, bạn có thể để lại kiểm tra

    // Hàm render danh sách danh mục
    function renderCategories(data) {
        const categoryList = document.getElementById('category-list');
        categoryList.innerHTML = ''; // Xóa dữ liệu cũ

        if (!Array.isArray(data) || data.length === 0) {
            categoryList.innerHTML = '<div class="col-12 text-center text-muted">Không có danh mục nào.</div>';
            return;
        }

        data.forEach(category => {
            const categoryItem = document.createElement('div');
            categoryItem.className = 'col-lg-4 col-md-6 mb-4';
            categoryItem.innerHTML = `
                <a href="/blueskyweb/Product?category=${category.id}" class="category-card">
                    <div class="card-body">
                        <h5 class="category-name">${category.name}</h5>
                        <p class="category-description">${category.description}</p>
                        <div class="view-products">
                            <span class="view-products-link">Xem sản phẩm <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </div>
                </a>
            `;
            categoryList.appendChild(categoryItem);
        });
    }

    // Lấy danh sách danh mục từ API
    fetch('/blueskyweb/api/category', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + (token || '')
        }
    })
    .then(response => {
        if (response.status === 401) {
            // Nếu API yêu cầu token nhưng không có, có thể bỏ qua vì đây là trang công khai
            console.warn('Không có token, nhưng tiếp tục tải danh mục công khai');
        }
        if (!response.ok) {
            throw new Error('Lỗi khi lấy dữ liệu danh mục: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        renderCategories(data); // Hiển thị danh mục
    })
    .catch(error => {
        console.error("Lỗi khi tải danh sách danh mục:", error);
        document.getElementById('category-list').innerHTML = '<div class="col-12 text-center text-muted">Lỗi khi tải danh mục. Vui lòng thử lại sau.</div>';
    });
});
</script>

<style>
/* Tùy chỉnh tổng thể */
.container {
    padding-top: 20px;
    padding-bottom: 40px;
}

/* Tiêu đề trang */
.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a73e8;
    border-bottom: 3px solid #1a73e8;
    padding-bottom: 10px;
    margin-bottom: 30px;
    position: relative;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, #1a73e8, #34c759);
}

/* Thẻ danh mục */
.category-card {
    display: block;
    border: none;
    border-radius: 12px;
    background: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.card-body {
    padding: 20px;
}

.category-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.category-description {
    font-size: 0.9rem;
    color: #666;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 15px;
}

.view-products {
    text-align: right;
}

.view-products-link {
    font-size: 0.9rem;
    color: #1a73e8;
    font-weight: 500;
    transition: color 0.3s ease;
}

.view-products-link:hover {
    color: #34c759;
}

.view-products-link i {
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 992px) {
    .category-card {
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 1.8rem;
    }

    .category-name {
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }

    .category-name {
        font-size: 1.1rem;
    }
}
</style>