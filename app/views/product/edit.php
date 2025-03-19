<?php include 'app/views/shares/header.php'; ?>

<h1>Chỉnh sửa sản phẩm</h1>

<form id="edit-product-form" enctype="multipart/form-data">
    <input type="hidden" id="id" name="id" value="<?= $product->id ?>">

    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= $product->name ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required><?= $product->description ?></textarea>
    </div>

    <div class="form-group">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?= $product->price ?>" required>
    </div>

    <div class="form-group">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <?php foreach ($categories as $category) : ?>
                <option value="<?= $category->id ?>" <?= ($category->id == $product->category_id) ? 'selected' : '' ?>>
                    <?= $category->name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
    <label for="image">Chọn ảnh mới (nếu có):</label>
    <input type="file" id="image" name="image" class="form-control" accept="image/*">

    <p>Ảnh hiện tại:</p>
    <?php if (!empty($product->image)) : ?>
        <img src="/blueskyweb/<?= htmlspecialchars($product->image) ?>" width="150" alt="Ảnh sản phẩm">
        <input type="hidden" id="current_image" name="current_image" value="<?= htmlspecialchars($product->image) ?>">
    <?php else : ?>
        <p>Không có ảnh</p>
    <?php endif; ?>
</div>


    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
</form>

<script>
document.getElementById('edit-product-form').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    formData.append('_method', 'PUT'); // Bổ sung _method=PUT
    for (let [key, value] of formData.entries()) {
    console.log(`${key}:`, value);  // Debug xem ảnh có được gửi không
}
fetch('/blueskyweb/api/product/' + document.getElementById('id').value, { 
    method: 'POST',  // Vẫn dùng POST để bypass lỗi trình duyệt không hỗ trợ PUT
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log("Phản hồi từ API:", data);
    if (data.message === 'Sản phẩm đã được cập nhật') {
        location.href = '/blueskyweb/Product';
    } else {
        alert('Cập nhật sản phẩm thất bại: ' + (data.message || 'Không rõ nguyên nhân'));
    }
})
.catch(error => {
    console.error("Lỗi Fetch API:", error);
    alert('Lỗi kết nối đến server!');
});
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
