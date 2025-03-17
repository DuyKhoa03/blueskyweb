<h2>Giỏ hàng của bạn</h2>

<?php if (empty($cartItems)) : ?>
    <p>Giỏ hàng trống.</p>
<?php else : ?>
    <table border="1">
        <tr>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Tổng</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($cartItems as $item) : ?>
            <tr>
                <td><img src="/<?= $item['image'] ?>" width="50"></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>
                    <form method="post" action="/cart/update">
                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                        <button type="submit">Cập nhật</button>
                    </form>
                </td>
                <td><?= number_format($item['price'], 2) ?>đ</td>
                <td><?= number_format($item['quantity'] * $item['price'], 2) ?>đ</td>
                <td><a href="/cart/delete/<?= $item['id'] ?>">Xóa</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="/cart/clear">Xóa toàn bộ giỏ hàng</a>
<?php endif; ?>
