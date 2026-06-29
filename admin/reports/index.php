<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

$reportType = $_GET['report'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$reportData = null;
$reportTitle = '';

if ($reportType === 'sales') {
    $reportTitle = 'Sales Report';
    $sql = "SELECT o.order_id, o.user_id, u.name AS customer_name, o.total_amount, o.payment_method, o.payment_status, o.status, o.order_date
            FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE 1=1";
    $params = [];
    if ($dateFrom) { $sql .= " AND o.order_date >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
    if ($dateTo)   { $sql .= " AND o.order_date <= ?"; $params[] = $dateTo . ' 23:59:59'; }
    $sql .= " ORDER BY o.order_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportData = $stmt->fetchAll();
} elseif ($reportType === 'users') {
    $reportTitle = 'User Report';
    $stmt = $pdo->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id ORDER BY u.created_at DESC");
    $reportData = $stmt->fetchAll();
} elseif ($reportType === 'products') {
    $reportTitle = 'Product Report';
    $stmt = $pdo->query("SELECT p.*, pc.name AS category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.category_id ORDER BY p.product_id DESC");
    $reportData = $stmt->fetchAll();
} elseif ($reportType === 'careers') {
    $reportTitle = 'Career Applications Report';
    $stmt = $pdo->query("SELECT ca.*, cj.title AS job_title FROM career_applications ca LEFT JOIN career_jobs cj ON ca.job_id = cj.job_id ORDER BY ca.created_at DESC");
    $reportData = $stmt->fetchAll();
} elseif ($reportType === 'admissions') {
    $reportTitle = 'Admission Applications Report';
    $stmt = $pdo->query("SELECT aa.*, ap.title AS program_title FROM admission_applications aa LEFT JOIN admission_programs ap ON aa.program_id = ap.program_id ORDER BY aa.created_at DESC");
    $reportData = $stmt->fetchAll();
}

function exportCSV(array $data, array $columns, string $filename): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $columns);
    foreach ($data as $row) {
        $line = [];
        foreach ($columns as $col) {
            $key = strtolower(str_replace(' ', '_', $col));
            $line[] = $row[$key] ?? $row[$col] ?? '';
        }
        fputcsv($output, $line);
    }
    fclose($output);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv' && $reportData) {
    $cols = [];
    $filename = $reportType . '_report_' . date('Y-m-d');
    switch ($reportType) {
        case 'sales':      $cols = ['order_id', 'customer_name', 'total_amount', 'payment_method', 'payment_status', 'status', 'order_date']; $filename = 'sales_report'; break;
        case 'users':      $cols = ['user_id', 'name', 'email', 'role_name', 'is_active', 'created_at']; $filename = 'users_report'; break;
        case 'products':   $cols = ['product_id', 'name', 'category_name', 'price', 'stock', 'is_active', 'created_at']; $filename = 'products_report'; break;
        case 'careers':    $cols = ['application_id', 'applicant_name', 'email', 'job_title', 'status', 'created_at']; $filename = 'career_applications_report'; break;
        case 'admissions': $cols = ['application_id', 'applicant_name', 'email', 'program_title', 'status', 'created_at']; $filename = 'admissions_report'; break;
    }
    exportCSV($reportData, $cols, $filename);
}

$pageTitle = 'Reports';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$reportCards = [
    'sales'     => ['icon' => 'fa-money-bill-wave', 'color' => '#6366f1', 'title' => 'Sales Report', 'desc' => 'View orders with date range filtering'],
    'users'     => ['icon' => 'fa-users',            'color' => '#22c55e', 'title' => 'User Report', 'desc' => 'View all registered users'],
    'products'  => ['icon' => 'fa-box',              'color' => '#f59e0b', 'title' => 'Product Report', 'desc' => 'View all products and inventory'],
    'careers'   => ['icon' => 'fa-briefcase',         'color' => '#3b82f6', 'title' => 'Career Report', 'desc' => 'View job applications'],
    'admissions'=> ['icon' => 'fa-graduation-cap',    'color' => '#ef4444', 'title' => 'Admission Report', 'desc' => 'View admission applications'],
];
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Reports Dashboard</h1>
    </div>

    <?= flashMessage() ?>

    <?php if (!$reportType): ?>
    <div class="row g-4">
        <?php foreach ($reportCards as $key => $card): ?>
        <div class="col-md-4">
            <div class="card report-card" onclick="window.location='?report=<?= $key ?>'">
                <div class="card-body text-center py-4">
                    <div class="display-4 mb-3" style="color: <?= $card['color'] ?>">
                        <i class="fas <?= $card['icon'] ?>"></i>
                    </div>
                    <h5 class="card-title"><?= $card['title'] ?></h5>
                    <p class="card-text text-muted small"><?= $card['desc'] ?></p>
                    <span class="btn btn-sm" style="background:<?= $card['color'] ?>;color:#fff">
                        <i class="fas fa-arrow-right"></i> View Report
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= h($reportTitle) ?></h5>
            <div class="d-flex gap-2">
                <a href="?report=<?= $reportType ?>&export=csv<?= $dateFrom ? '&date_from=' . urlencode($dateFrom) : '' ?><?= $dateTo ? '&date_to=' . urlencode($dateTo) : '' ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn btn-info btn-sm text-white">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($reportType === 'sales'): ?>
            <form method="get" class="row g-3 mb-4">
                <input type="hidden" name="report" value="sales">
                <div class="col-auto">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= h($dateFrom) ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= h($dateTo) ?>">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </form>
            <?php endif; ?>

            <?php if ($reportData && count($reportData) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="reportTable">
                    <thead class="table-light">
                        <tr>
                            <?php if ($reportType === 'sales'): ?>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment Method</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Date</th>
                            <?php elseif ($reportType === 'users'): ?>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Active</th>
                            <th>Registered</th>
                            <?php elseif ($reportType === 'products'): ?>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Created</th>
                            <?php elseif ($reportType === 'careers'): ?>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Job Title</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <?php elseif ($reportType === 'admissions'): ?>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                        <tr>
                            <?php if ($reportType === 'sales'): ?>
                            <td><?= h($row['order_id']) ?></td>
                            <td><?= h($row['customer_name'] ?? 'N/A') ?></td>
                            <td>$<?= number_format($row['total_amount'], 2) ?></td>
                            <td><?= h($row['payment_method']) ?></td>
                            <td><span class="status-badge <?= $row['payment_status'] === 'Completed' ? 'status-active' : 'status-inactive' ?>"><?= h($row['payment_status']) ?></span></td>
                            <td><?= h($row['status']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['order_date'])) ?></td>
                            <?php elseif ($reportType === 'users'): ?>
                            <td><?= h($row['user_id']) ?></td>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td><?= h($row['role_name'] ?? 'User') ?></td>
                            <td><span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $row['is_active'] ? 'Yes' : 'No' ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <?php elseif ($reportType === 'products'): ?>
                            <td><?= $row['product_id'] ?></td>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['category_name'] ?? $row['category']) ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td><?= (int)$row['stock'] ?></td>
                            <td><?= $row['is_featured'] ? 'Yes' : 'No' ?></td>
                            <td><span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <?php elseif ($reportType === 'careers'): ?>
                            <td><?= $row['application_id'] ?></td>
                            <td><?= h($row['applicant_name']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td><?= h($row['job_title'] ?? 'N/A') ?></td>
                            <td><span class="status-badge <?= $row['status'] === 'Pending' ? 'status-unread' : 'status-active' ?>"><?= h($row['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <?php elseif ($reportType === 'admissions'): ?>
                            <td><?= $row['application_id'] ?></td>
                            <td><?= h($row['applicant_name']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td><?= h($row['program_title'] ?? 'N/A') ?></td>
                            <td><span class="status-badge <?= $row['status'] === 'Pending' ? 'status-unread' : 'status-active' ?>"><?= h($row['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No data found for this report.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    if ($('#reportTable').length) {
        $('#reportTable').DataTable({
            pageLength: 50,
            order: []
        });
    }
});
</script>
JS;
?>
<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
