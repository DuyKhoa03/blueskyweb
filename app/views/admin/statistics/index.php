<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h1 class="page-title text-center mb-4">Thống kê Doanh thu</h1>

    <!-- Form lọc -->
    <div class="row mb-5 g-3 align-items-end">
        <div class="col-md-3">
            <label for="type" class="form-label">Chọn kiểu thống kê:</label>
            <select id="type" class="form-select shadow-sm">
                <option value="day">Theo ngày</option>
                <option value="month">Theo tháng</option>
                <option value="year">Theo năm</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="from" class="form-label">Từ ngày:</label>
            <input type="date" id="from" class="form-control shadow-sm">
        </div>
        <div class="col-md-3">
            <label for="to" class="form-label">Đến ngày:</label>
            <input type="date" id="to" class="form-control shadow-sm">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100 shadow-sm" onclick="fetchRevenue()">Xem thống kê</button>
        </div>
    </div>

    <!-- Tổng quan -->
    <div id="summary" class="card shadow-sm mb-5">
        <div class="card-body">
            <h5 class="card-title">Tổng quan</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0"><strong>Tổng doanh thu:</strong> <span id="totalRevenue" class="text-primary">0</span> VND</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><strong>Số đơn hàng hoàn tất:</strong> <span id="orderCount" class="text-primary">0</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h5 class="card-title">Thống kê theo thời gian</h5>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <!-- Bảng doanh thu theo sản phẩm -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Doanh thu theo sản phẩm</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng đã bán</th>
                            <th>Tổng doanh thu</th>
                        </tr>
                    </thead>
                    <tbody id="productRevenue"></tbody>
                </table>
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

.card-title {
    color: #34495e;
    margin-bottom: 1.5rem;
}

.form-control, .form-select {
    border-radius: 5px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

.btn-primary {
    border-radius: 5px;
    padding: 10px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    color: #495057;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.text-primary {
    font-weight: 600;
}
</style>

<?php include 'app/views/shares/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function fetchRevenue() {
    const type = document.getElementById('type').value;
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;

    let url = `/blueskyweb/api/statistics/revenue?type=${type}`;
    if (from && to) url += `&from=${from}&to=${to}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + <?php echo json_encode($_SESSION['jwtToken'] ?? ''); ?>
        }
    })
    .then(res => res.json())
    .then(data => {
        // Tổng doanh thu và đơn hàng
        let totalRevenue = 0;
        let orderCount = 0;
        data.revenue_stats.forEach(item => {
            totalRevenue += parseFloat(item.total_revenue);
            orderCount += parseInt(item.order_count);
        });

        document.getElementById('totalRevenue').textContent = totalRevenue.toLocaleString();
        document.getElementById('orderCount').textContent = orderCount;

        // Vẽ biểu đồ
        const ctx = document.getElementById('revenueChart').getContext('2d');
        if (window.revenueChart && typeof window.revenueChart.destroy === 'function') {
            window.revenueChart.destroy();
        }

        window.revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.revenue_stats.map(r => r.time_key),
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: data.revenue_stats.map(r => r.total_revenue),
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: '#007bff',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString() + ' VND'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Bảng doanh thu theo sản phẩm
        const tbody = document.getElementById('productRevenue');
        tbody.innerHTML = '';
        data.revenue_by_product.forEach(p => {
            const row = `<tr>
                <td>${p.name}</td>
                <td>${p.quantity_sold}</td>
                <td>${parseInt(p.total_revenue).toLocaleString()} VND</td>
            </tr>`;
            tbody.innerHTML += row;
        });
    })
    .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', fetchRevenue);
</script>