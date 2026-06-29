<?php
require_once __DIR__ . '/../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getDB();

$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = 'Completed'")->fetchColumn();
$careerApps    = $pdo->query("SELECT COUNT(*) FROM career_applications")->fetchColumn();
$admissionApps = $pdo->query("SELECT COUNT(*) FROM admission_applications")->fetchColumn();
$contactMsgs   = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

$recentOrders = $pdo->query("SELECT o.*, u.name AS user_name FROM orders o JOIN users u ON u.user_id = o.user_id ORDER BY o.order_date DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
include_once __DIR__ . '/partials/admin_header.php';
include_once __DIR__ . '/partials/admin_sidebar.php';
?>

<style>
.admin-stat-card {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  transition: all 0.3s ease;
}
.admin-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,.1);
}
.admin-stat-card .stat-bg-icon {
  position: absolute;
  right: -0.5rem;
  bottom: -0.5rem;
  font-size: 4rem;
  opacity: 0.06;
}
.admin-stat-card .stat-title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}
.admin-stat-card .stat-number {
  font-size: 1.75rem;
  font-weight: 800;
  margin-bottom: 0.25rem;
}
.admin-stat-card .stat-footer-link {
  font-size: 0.75rem;
  font-weight: 500;
  opacity: 0.8;
  transition: opacity 0.2s;
  color: inherit;
  text-decoration: none;
}
.admin-stat-card .stat-footer-link:hover { opacity: 1; }
.table-admin th {
  font-weight: 600;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  border-bottom: 2px solid #f1f5f9;
  padding: 0.9rem 1.25rem !important;
}
.table-admin td {
  padding: 0.9rem 1.25rem !important;
  vertical-align: middle;
}
.badge-soft {
  padding: 0.3em 0.8em;
  border-radius: 9999px;
  font-weight: 500;
  font-size: 0.75rem;
}
.badge-soft-success { background: #dcfce7; color: #166534; }
.badge-soft-warning { background: #fef3c7; color: #92400e; }
.badge-soft-secondary { background: #f1f5f9; color: #475569; }
.badge-soft-danger { background: #fee2e2; color: #991b1b; }
.badge-soft-info { background: #dbeafe; color: #1e40af; }
.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: #fff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  text-decoration: none;
  color: #1e293b;
  font-weight: 500;
  transition: all 0.3s;
  border: 1px solid #f1f5f9;
}
.quick-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,.08);
  border-color: #e0e7ff;
  color: #6366f1;
}
.quick-action-btn .qa-icon-sm {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}
.chart-placeholder {
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-radius: 0.75rem;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 0.9rem;
  border: 2px dashed #e2e8f0;
}
.card-premium {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  overflow: hidden;
}
.card-premium .card-head {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card-premium .card-head h5 {
  font-weight: 700;
  font-size: 1rem;
  margin: 0;
  color: #1e293b;
}
.card-premium .card-body-custom { padding: 0; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1e293b"><i class="fas fa-tachometer-alt me-2" style="color:#6366f1"></i>Dashboard</h4>
    <p class="text-muted mb-0 small">Welcome back, <?= h($_SESSION['name'] ?? 'Admin') ?></p>
  </div>
  <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #6366f1">
      <i class="fas fa-users stat-bg-icon"></i>
      <div class="stat-title" style="color:#6366f1">Users</div>
      <div class="stat-number" style="color:#1e293b"><?= $totalUsers ?></div>
      <a href="<?= APP_URL ?>/admin/users/" class="stat-footer-link" style="color:#6366f1">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #22c55e">
      <i class="fas fa-box stat-bg-icon"></i>
      <div class="stat-title" style="color:#22c55e">Products</div>
      <div class="stat-number" style="color:#1e293b"><?= $totalProducts ?></div>
      <a href="<?= APP_URL ?>/admin/products/" class="stat-footer-link" style="color:#22c55e">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #f59e0b">
      <i class="fas fa-project-diagram stat-bg-icon"></i>
      <div class="stat-title" style="color:#f59e0b">Projects</div>
      <div class="stat-number" style="color:#1e293b"><?= $totalProjects ?></div>
      <a href="<?= APP_URL ?>/admin/projects/" class="stat-footer-link" style="color:#f59e0b">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #ef4444">
      <i class="fas fa-shopping-cart stat-bg-icon"></i>
      <div class="stat-title" style="color:#ef4444">Orders</div>
      <div class="stat-number" style="color:#1e293b"><?= $totalOrders ?></div>
      <a href="<?= APP_URL ?>/admin/orders/" class="stat-footer-link" style="color:#ef4444">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #3b82f6">
      <i class="fas fa-dollar-sign stat-bg-icon"></i>
      <div class="stat-title" style="color:#3b82f6">Revenue</div>
      <div class="stat-number" style="color:#1e293b">$<?= number_format($totalRevenue, 2) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #8b5cf6">
      <i class="fas fa-briefcase stat-bg-icon"></i>
      <div class="stat-title" style="color:#8b5cf6">Career Apps</div>
      <div class="stat-number" style="color:#1e293b"><?= $careerApps ?></div>
      <a href="<?= APP_URL ?>/admin/careers/" class="stat-footer-link" style="color:#8b5cf6">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #14b8a6">
      <i class="fas fa-graduation-cap stat-bg-icon"></i>
      <div class="stat-title" style="color:#14b8a6">Admission Apps</div>
      <div class="stat-number" style="color:#1e293b"><?= $admissionApps ?></div>
      <a href="<?= APP_URL ?>/admin/admissions/" class="stat-footer-link" style="color:#14b8a6">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card" style="border-left:4px solid #f97316">
      <i class="fas fa-envelope stat-bg-icon"></i>
      <div class="stat-title" style="color:#f97316">Messages</div>
      <div class="stat-number" style="color:#1e293b"><?= $contactMsgs ?></div>
      <a href="<?= APP_URL ?>/admin/contact/" class="stat-footer-link" style="color:#f97316">View Details <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-premium">
      <div class="card-head">
        <h5><i class="fas fa-clock me-2" style="color:#6366f1"></i>Recent Orders</h5>
        <a href="<?= APP_URL ?>/admin/orders/" class="btn btn-sm" style="background:#e0e7ff;color:#6366f1;border-radius:0.5rem;font-weight:600">View All</a>
      </div>
      <div class="card-body-custom">
        <div class="table-responsive">
          <table class="table table-admin mb-0">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentOrders)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No orders yet</td></tr>
              <?php else: ?>
              <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td><strong><?= h($o['order_id']) ?></strong></td>
                <td><?= h($o['user_name']) ?></td>
                <td class="fw-bold" style="color:#1e293b">$<?= number_format($o['total_amount'], 2) ?></td>
                <td>
                  <?php
                  $pBadge = $o['payment_status'] === 'Completed' ? 'success' : ($o['payment_status'] === 'Pending' ? 'warning' : 'secondary');
                  ?>
                  <span class="badge-soft badge-soft-<?= $pBadge ?>"><?= h($o['payment_status']) ?></span>
                </td>
                <td>
                  <?php
                  $sBadge = $o['status'] === 'Delivered' ? 'success' : ($o['status'] === 'Cancelled' ? 'danger' : 'info');
                  ?>
                  <span class="badge-soft badge-soft-<?= $sBadge ?>"><?= h($o['status']) ?></span>
                </td>
                <td class="small text-muted"><?= date('M j, Y g:i A', strtotime($o['order_date'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card-premium mb-3">
      <div class="card-head">
        <h5><i class="fas fa-chart-line me-2" style="color:#6366f1"></i>Revenue Overview</h5>
      </div>
      <div class="p-3">
        <div class="chart-placeholder">
          <div class="text-center">
            <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
            <span>Chart placeholder</span>
          </div>
        </div>
      </div>
    </div>
    <div class="card-premium">
      <div class="card-head">
        <h5><i class="fas fa-bolt me-2" style="color:#6366f1"></i>Quick Actions</h5>
      </div>
      <div class="p-3" style="display:flex;flex-direction:column;gap:0.5rem">
        <a href="<?= APP_URL ?>/admin/users/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#e0e7ff;color:#6366f1"><i class="fas fa-user-plus"></i></div>
          Manage Users
        </a>
        <a href="<?= APP_URL ?>/admin/products/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#dcfce7;color:#22c55e"><i class="fas fa-plus-circle"></i></div>
          Add Product
        </a>
        <a href="<?= APP_URL ?>/admin/orders/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#dbeafe;color:#3b82f6"><i class="fas fa-truck"></i></div>
          View Orders
        </a>
        <a href="<?= APP_URL ?>/admin/contact/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#fef3c7;color:#f59e0b"><i class="fas fa-envelope"></i></div>
          Contact Messages
        </a>
        <a href="<?= APP_URL ?>/admin/cms/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#f1f5f9;color:#64748b"><i class="fas fa-file-alt"></i></div>
          Manage CMS
        </a>
        <a href="<?= APP_URL ?>/admin/settings/" class="quick-action-btn">
          <div class="qa-icon-sm" style="background:#e2e8f0;color:#1e293b"><i class="fas fa-cog"></i></div>
          Site Settings
        </a>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/partials/admin_footer.php'; ?>
