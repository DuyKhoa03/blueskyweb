<?php include 'app/views/shares/header.php'; ?>

<?php
// Lấy token để xác thực nếu cần
if (session_status() == PHP_SESSION_NONE) session_start();
$token = $_SESSION['jwtToken'] ?? null;
?>

<div class="container py-5">
    <?php if ($product): ?>
        <div class="row">
            <div class="col-md-5">
                <img src="/blueskyweb/<?= $product->image ?>" class="img-fluid rounded shadow-sm" alt="<?= $product->name ?>">
            </div>
            <div class="col-md-7">
                <h2 class="mb-3"><?= $product->name ?></h2>
                <p class="text-muted"><?= $product->description ?></p>
                <h4 class="text-primary mb-3">Giá: <?= number_format($product->price, 0, ',', '.') ?> VND</h4>
                <p><strong>Danh mục:</strong> <?= $product->category_name ?? 'Không rõ' ?></p>
                <button class="btn btn-success mt-3" onclick="addToCart(<?= $product->id ?>)">
    <i class="fas fa-cart-plus me-1"></i> Thêm vào giỏ hàng
</button>

            </div>
        </div>
    <?php else: ?>
        <p class="text-danger">Không tìm thấy sản phẩm.</p>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    const token = <?php echo json_encode($token); ?>;
    const userId = <?php echo json_encode($userid ?? null); ?>;

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
