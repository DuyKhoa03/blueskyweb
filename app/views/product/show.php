<?php include 'app/views/shares/header.php'; ?>

<?php
// Lấy token để xác thực nếu cần
if (session_status() == PHP_SESSION_NONE) session_start();
$token = $_SESSION['jwtToken'] ?? null;
?>

<div class="container py-5">
    <?php if ($product): ?>
        <div class="row">
            <!-- Cột hình ảnh -->
            <div class="col-md-5 mb-4">
                <div class="product-image-wrapper">
                    <img src="/blueskyweb/<?= $product->image ?>" class="img-fluid rounded shadow-sm product-image" alt="<?= $product->name ?>">
                </div>
            </div>

            <!-- Cột thông tin chính -->
            <div class="col-md-7 mb-4">
                <h2 class="product-title mb-3"><?= $product->name ?></h2>
                <h4 class="product-price text-primary mb-3">Giá: <?= number_format($product->price, 0, ',', '.') ?> VND</h4>
                <p class="product-category mb-3"><strong>Danh mục:</strong> <?= $product->category_name ?? 'Không rõ' ?></p>
                <button class="btn btn-success btn-add-to-cart mt-3" onclick="addToCart(<?= $product->id ?>)">
                    <i class="fas fa-cart-plus me-1"></i> Thêm vào giỏ hàng
                </button>
            </div>

            <!-- Mô tả sản phẩm -->
            <div class="col-12 mt-4">
                <h5 class="description-title mb-2">Mô tả sản phẩm</h5>
                <p class="product-description text-muted"><?= $product->description ?></p>
            </div>

            <!-- Sản phẩm tương tự -->
            <div class="col-12 mt-5">
                <h5 class="related-products-title mb-4">Sản phẩm tương tự</h5>
                <div class="row" id="related-products">
                    <!-- Sản phẩm tương tự sẽ được hiển thị ở đây -->
                </div>
            </div>
        </div>
    <?php else: ?>
        <p class="text-danger text-center">Không tìm thấy sản phẩm.</p>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<!-- Thêm CSS để cải thiện giao diện -->
<style>
/* Container chính */
.container {
    padding: 3rem 0;
}

/* Hình ảnh sản phẩm */
.product-image-wrapper {
    background-color: #f8f9fa; /* Nền sáng để nổi bật ảnh */
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.product-image {
    width: 100%;
    height: 300px; /* Chiều cao cố định */
    object-fit: contain; /* Đảm bảo ảnh vừa khung */
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.05); /* Hiệu ứng phóng to nhẹ khi hover */
}

/* Tiêu đề sản phẩm */
.product-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #343a40;
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.5rem;
}

/* Giá sản phẩm */
.product-price {
    font-size: 1.5rem;
    font-weight: 600;
    color: #28a745; /* Màu xanh lá để nổi bật */
}

/* Danh mục */
.product-category {
    font-size: 1rem;
    color: #6c757d;
}

/* Nút thêm vào giỏ hàng */
.btn-add-to-cart {
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
    border-radius: 5px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-add-to-cart:hover {
    background-color: #218838;
    transform: translateY(-2px);
}

.btn-add-to-cart i {
    font-size: 0.9rem;
}

/* Mô tả sản phẩm */
.description-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #343a40;
    border-left: 4px solid #007bff;
    padding-left: 1rem;
}

.product-description {
    font-size: 1rem;
    line-height: 1.6;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
}

/* Sản phẩm tương tự */
.related-products-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #343a40;
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.5rem;
}

/* Card sản phẩm tương tự */
.related-product-card {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.related-product-image {
    height: 150px; /* Chiều cao nhỏ hơn để phù hợp với danh sách tương tự */
    width: 100%;
    object-fit: contain;
    background-color: #f8f9fa;
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.related-product-image:hover {
    opacity: 0.9;
}

.related-product-name {
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    transition: color 0.3s ease;
}

.related-product-name:hover {
    color: #007bff;
}

.related-product-price {
    font-size: 0.9rem;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.related-product-category {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

/* Nút thêm vào giỏ hàng trong sản phẩm tương tự */
.related-product-card .btn-add-to-cart {
    padding: 0.3rem 0.8rem;
    font-size: 0.9rem;
}

/* Thông báo lỗi */
.text-danger {
    font-size: 1.2rem;
    font-weight: 500;
}
</style>

<script>
    const token = <?php echo json_encode($token); ?>;
    const userId = <?php echo json_encode($userid ?? null); ?>;
    const currentProductId = <?php echo json_encode($product->id ?? null); ?>;
    const categoryId = <?php echo json_encode($product->category_id ?? null); ?>;

    // Load sản phẩm tương tự khi trang được tải
    document.addEventListener("DOMContentLoaded", function () {
        if (categoryId) {
            loadRelatedProducts(categoryId, currentProductId);
        }
    });

    // Hàm lấy danh sách sản phẩm tương tự
    function loadRelatedProducts(categoryId, excludeProductId) {
        fetch(`/blueskyweb/api/product?category=${encodeURIComponent(categoryId)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + (token || '')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Lọc bỏ sản phẩm hiện tại
            const relatedProducts = data.filter(product => product.id !== excludeProductId);
            renderRelatedProducts(relatedProducts);
        })
        .catch(error => {
            console.error("Lỗi khi tải sản phẩm tương tự:", error);
            document.getElementById('related-products').innerHTML = '<p class="text-muted text-center w-100">Không thể tải sản phẩm tương tự.</p>';
        });
    }

    // Hàm render sản phẩm tương tự
    function renderRelatedProducts(products) {
        const relatedProductsList = document.getElementById('related-products');
        relatedProductsList.innerHTML = '';

        if (products.length === 0) {
            relatedProductsList.innerHTML = '<p class="text-muted text-center w-100">Không có sản phẩm tương tự.</p>';
            return;
        }

        // Giới hạn số lượng sản phẩm hiển thị (ví dụ: tối đa 4 sản phẩm)
        const maxItems = 4;
        const displayProducts = products.slice(0, maxItems);

        displayProducts.forEach(product => {
            const productItem = document.createElement('div');
            productItem.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
            productItem.innerHTML = `
                <div class="card related-product-card shadow-sm h-100">
                    <a href="/blueskyweb/Product/show/${product.id}">
                        <img src="${product.image}" alt="${product.name}" class="card-img-top related-product-image">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <a href="/blueskyweb/Product/show/${product.id}" class="text-decoration-none">
                            <h5 class="related-product-name">${product.name}</h5>
                        </a>
                        <p class="related-product-price text-success font-weight-bold">Giá: ${parseFloat(product.price).toLocaleString()} VND</p>
                        <p class="related-product-category text-secondary">Danh mục: ${product.category_name}</p>
                        <div class="mt-auto d-flex justify-content-end">
                            <button class="btn btn-primary btn-sm btn-add-to-cart" onclick="addToCart(${product.id})">
                                <i class="fas fa-cart-plus mr-1"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
            `;
            relatedProductsList.appendChild(productItem);
        });
    }

    // Hàm thêm vào giỏ hàng
    function addToCart(productId) {
        fetch('/blueskyweb/api/cart/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Added to cart') {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                updateCartCount();
            } else {
                alert('Thêm vào giỏ hàng thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => {
            console.error("Lỗi khi thêm vào giỏ hàng:", error);
            alert("Lỗi hệ thống khi thêm vào giỏ hàng!");
        });
    }

    // Hàm cập nhật số lượng giỏ hàng
    function updateCartCount() {
        if (!userId) return;
        fetch(`/blueskyweb/api/cart/${userId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(cart => {
            if (cart && Array.isArray(cart)) {
                document.getElementById('cart-count').innerText = cart.length;
            }
        })
        .catch(error => console.error("Lỗi khi cập nhật số lượng giỏ hàng:", error));
    }
</script>