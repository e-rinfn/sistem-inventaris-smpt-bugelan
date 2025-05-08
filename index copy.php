<?php
require_once(__DIR__ . '/config/database.php'); // path absolut
require_once 'includes/auth_check.php';

$pdo = getPDO();

// Hitung total bahan baku
$totalIngredients = $pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();

// Hitung total produk
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

// Hitung bahan dengan stok rendah
$lowStockIngredients = $pdo->query("
    SELECT COUNT(*) 
    FROM ingredients 
    WHERE current_stock < min_stock AND min_stock > 0
")->fetchColumn();

// Ambil 5 penjualan terbaru
$recentSales = $pdo->query("
    SELECT s.sale_id, s.sale_date, s.customer_name, s.total_amount, s.payment_method, u.username
    FROM sales s
    JOIN users u ON s.recorded_by = u.user_id
    ORDER BY s.sale_date DESC, s.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Ambil 5 produksi terbaru
$recentProductions = $pdo->query("
    SELECT p.production_id, p.production_date, pr.product_name, p.quantity, p.total_ingredient_cost, u.username
    FROM productions p
    JOIN products pr ON p.product_id = pr.product_id
    JOIN users u ON p.recorded_by = u.user_id
    ORDER BY p.production_date DESC, p.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Hitung total penjualan hari ini
$todaySales = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0) as total
    FROM sales
    WHERE sale_date = CURDATE()
")->fetch(PDO::FETCH_ASSOC);

// Hitung total pengeluaran hari ini
$todayExpenses = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM expenses
    WHERE expense_date = CURDATE()
")->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

    <div class="card shadow border p-3">
        <div class="row">
            <div class="col-md-3 p-3">
                <h2>BERANDA</h2>
                <p class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
            </div>

            <div class="col-md-9">
                <!-- Quick Actions -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Aksi Cepat</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2" style="justify-content: center;">
                                    <a href="modules/sales/add.php" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i> Tambah Penjualan
                                    </a>
                                    <a href="modules/production/add.php" class="btn btn-success">
                                        <i class="bi bi-gear"></i> Tambah Produksi
                                    </a>
                                    <a href="modules/purchases/add.php" class="btn btn-warning">
                                        <i class="bi bi-cart-check"></i> Tambah Pembelian
                                    </a>
                                    <a href="modules/expenses/add.php" class="btn btn-danger">
                                        <i class="bi bi-cash-stack"></i> Tambah Pengeluaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        
        <div class="row mb-4 justify-content-center">

            <!-- Card: Laba Hari Ini -->
            <div class="col-md-4 mb-3">
                <div class="card text-white bg-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Laba Kotor Hari Ini</h6>
                                <h2 class="mb-0">Rp <?= number_format($todaySales['total'] - $todayExpenses['total'], 2) ?></h2>
                            </div>
                            <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="modules/reports/profit_loss.php" class="text-white stretched-link">Lihat detail</a>
                    </div>
                </div>
            </div>
            
            
            <!-- Card: Total Bahan Baku -->
            <div class="col-md-2 mb-3">
                <div class="card text-white bg-light h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-dark">Jenis Bahan Baku</h6>
                                <h2 class="mb-0 text-dark"><?= $totalIngredients ?></h2>
                            </div>
                            <i class="bi bi-box-seam bg-primary rounded-start-circle p-2 shadow" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="modules/ingredients/" class="text-dark stretched-link">Lihat detail</a>
                    </div>
                </div>
            </div>
            
            <!-- Card: Total Produk -->
            <div class="col-md-2 mb-3">
                <div class="card text-white bg-light h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-dark">Total Produk Aktif</h6>
                                <h2 class="mb-0 text-dark"><?= $totalProducts ?></h2>
                            </div>
                            <i class="bi bi-gear text-white bg-success rounded-start-circle p-2 shadow" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="modules/products/" class="text-dark stretched-link">Lihat detail</a>
                    </div>
                </div>
            </div>
            
            <!-- Card: Stok Rendah -->
            <div class="col-md-2 mb-3">
                <div class="card text-white bg-light h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-dark">Bahan Stok Rendah</h6>
                                <h2 class="mb-0 text-dark"><?= $lowStockIngredients ?></h2>
                            </div>
                            <i class="bi bi-exclamation-triangle bg-warning rounded-start-circle p-2 shadow" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="modules/ingredients/" class="text-dark stretched-link">Lihat detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <br>

    <div class="card shadow p-3">
        <div class="row">
            <!-- Recent Sales -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Penjualan Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentSales)): ?>
                            <div class="alert alert-info">Belum ada data penjualan.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Metode</th>
                                            <th>Kasir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($sale['sale_date'])) ?></td>
                                            <td><?= htmlspecialchars($sale['customer_name'] ?: '-') ?></td>
                                            <td>Rp <?= number_format($sale['total_amount'], 2) ?></td>
                                            <td>
                                                <?php 
                                                $method = [
                                                    'cash' => 'Tunai',
                                                    'transfer' => 'Transfer',
                                                    'credit' => 'Kredit'
                                                ];
                                                echo $method[$sale['payment_method']] ?? $sale['payment_method'];
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($sale['username']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="modules/sales/" class="btn btn-sm btn-primary">Lihat Semua Penjualan</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Productions -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Produksi Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProductions)): ?>
                            <div class="alert alert-info">Belum ada data produksi.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Biaya</th>
                                            <th>Operator</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProductions as $production): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($production['production_date'])) ?></td>
                                            <td><?= htmlspecialchars($production['product_name']) ?></td>
                                            <td><?= $production['quantity'] ?></td>
                                            <td>Rp <?= number_format($production['total_ingredient_cost'], 2) ?></td>
                                            <td><?= htmlspecialchars($production['username']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="modules/production/" class="btn btn-sm btn-success">Lihat Semua Produksi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>