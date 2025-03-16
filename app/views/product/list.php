<?php include 'app/views/shares/header.php'; ?>

<h1>Danh sách sản phẩm</h1>
<a href="/blueskyweb/Product/add" class="btn btn-success mb-2">Thêm sản phẩm mới</a>
<ul class="list-group" id="product-list">
    <!-- Danh sách sản phẩm sẽ được tải từ API và hiển thị tại đây -->
</ul>

<?php include 'app/views/shares/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            alert('Vui lòng đăng nhập');
            location.href = '/blueskyweb/account/login'; // Điều hướng đến trang đăng nhập 
            return;
        }
        fetch('/blueskyweb/api/product', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(response => response.json())
        .then(data => {
            const productList = document.getElementById('product-list');
            productList.innerHTML = ''; // Xóa danh sách cũ trước khi load mới
            
            data.forEach(product => {
                const productItem = document.createElement('li');
                productItem.className = 'list-group-item';
                productItem.innerHTML = ` 
                    <div class="d-flex align-items-center">
                        <img src="${product.image}" alt="${product.name}" class="product-image mr-3">
                        <div>
                            <h2><a href="/blueskyweb/Product/show/${product.id}">${product.name}</a></h2> 
                            <p>${product.description}</p> 
                            <p>Giá: ${product.price} VND</p> 
                            <p>Danh mục: ${product.category_name}</p> 
                            <a href="/blueskyweb/Product/edit/${product.id}" class="btn btn-warning">Sửa</a> 
                            <button class="btn btn-danger" onclick="deleteProduct(${product.id})">Xóa</button> 
                        </div>
                    </div>
                `; 
                productList.appendChild(productItem);
            });
        })
        .catch(error => console.error("Lỗi khi tải danh sách sản phẩm:", error));
    });

    function deleteProduct(id) {
    console.log("Deleting Product ID:", id); // Debug

    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        fetch(`/blueskyweb/api/product/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwtToken') }
        })
        .then(response => {
            console.log("Delete Response Status:", response.status); // Debug HTTP status
            return response.json();
        })
        .then(data => {
            console.log("Delete Response Data:", data); // Debug Response Data
            if (data.message === 'Sản phẩm đã bị xóa') {
                alert("Xóa sản phẩm thành công!");
                location.reload();
            } else {
                alert("Xóa sản phẩm thất bại: " + data.message);
            }
        })
        .catch(error => {
            console.error("Lỗi khi xóa sản phẩm:", error);
            alert("Lỗi hệ thống khi xóa sản phẩm!");
        });
    }
}

</script>

<style>
    .product-image {
        max-width: 100px;
        height: auto;
        border-radius: 5px;
    }
</style>
