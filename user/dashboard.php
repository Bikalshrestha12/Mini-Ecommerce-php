<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getDB();
$userId = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT created_at, avatar, confirm_status FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(total_amount), 0) AS spent FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT o.*, GROUP_CONCAT(p.name ORDER BY oi.item_id SEPARATOR '||') AS product_names, GROUP_CONCAT(oi.quantity ORDER BY oi.item_id SEPARATOR '||') AS quantities, GROUP_CONCAT(oi.price ORDER BY oi.item_id SEPARATOR '||') AS item_prices FROM orders o JOIN order_items oi ON oi.order_id = o.order_id JOIN products p ON p.product_id = oi.product_id WHERE o.user_id = ? GROUP BY o.order_id ORDER BY o.order_date DESC LIMIT 5");
$stmt->execute([$userId]);
$recentOrders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
:root {
  --dash-primary: #6366f1;
  --dash-primary-light: #e0e7ff;
  --dash-success: #22c55e;
  --dash-warning: #f59e0b;
  --dash-danger: #ef4444;
}
body { background: #f8fafc; }
.dash-header {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
  border-radius: 1.25rem;
  padding: 2rem 2.5rem;
  position: relative;
  overflow: hidden;
}
.dash-header::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.4;
}
.dash-header .avatar-lg {
  width: 60px; height: 60px; border-radius: 50%;
  object-fit: cover; border: 3px solid rgba(255,255,255,0.3);
}
.dash-header .avatar-placeholder {
  width: 60px; height: 60px; border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; font-weight: 700; color: #fff;
  border: 3px solid rgba(255,255,255,0.3);
}
.stat-card-premium {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
  transition: all 0.3s ease;
}
.stat-card-premium:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,.1);
}
.stat-card-premium .stat-border {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  border-radius: 1rem 1rem 0 0;
}
.stat-card-premium .stat-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem;
}
.dash-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
  overflow: hidden;
  transition: all 0.3s ease;
}
.dash-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.dash-card .card-header-custom {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.table-dash th {
  font-weight: 600;
  color: #64748b;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 1rem 1.25rem !important;
  border-bottom: 2px solid #f1f5f9;
}
.table-dash td { padding: 1rem 1.25rem !important; vertical-align: middle; }
.badge-soft {
  padding: 0.35em 0.85em;
  border-radius: 9999px;
  font-weight: 500;
  font-size: 0.75rem;
}
.badge-soft-success { background: #dcfce7; color: #166534; }
.badge-soft-info { background: #dbeafe; color: #1e40af; }
.badge-soft-primary { background: #e0e7ff; color: #4338ca; }
.badge-soft-danger { background: #fee2e2; color: #991b1b; }
.badge-soft-warning { background: #fef3c7; color: #92400e; }
.badge-soft-secondary { background: #f1f5f9; color: #475569; }
.quick-action-card {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
  transition: all 0.3s ease;
  cursor: pointer;
}
.quick-action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,.1);
}
.quick-action-card .qa-icon {
  width: 56px; height: 56px; border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.4rem;
}
</style>

<div class="container-fluid px-4 py-4">
  <div class="dash-header mb-4 position-relative" data-aos="fade-up">
    <div class="d-flex align-items-center gap-3 position-relative">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= imgUrl($user['avatar']) ?>" alt="" class="avatar-lg">
      <?php else: ?>
        <div class="avatar-placeholder"><?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?></div>
      <?php endif; ?>
      <div class="text-white">
        <h3 class="fw-bold mb-0">Welcome back, <?= h($_SESSION['name'] ?? 'User') ?>!</h3>
        <p class="mb-0 opacity-75">Here's what's happening with your account today.</p>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
      <div class="stat-card-premium">
        <div class="stat-border" style="background:linear-gradient(90deg,#6366f1,#8b5cf6)"></div>
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#e0e7ff;color:#6366f1"><i class="fas fa-shopping-bag"></i></div>
          <div>
            <div class="fs-3 fw-bold" style="color:#1e293b"><?= (int)$stats['total'] ?></div>
            <div class="text-muted small">Total Orders</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
      <div class="stat-card-premium">
        <div class="stat-border" style="background:linear-gradient(90deg,#22c55e,#16a34a)"></div>
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#dcfce7;color:#22c55e"><i class="fas fa-dollar-sign"></i></div>
          <div>
            <div class="fs-3 fw-bold" style="color:#1e293b">$<?= number_format((float)$stats['spent'], 2) ?></div>
            <div class="text-muted small">Total Spent</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
      <div class="stat-card-premium">
        <div class="stat-border" style="background:linear-gradient(90deg,#3b82f6,#2563eb)"></div>
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#dbeafe;color:#3b82f6"><i class="fas fa-calendar-check"></i></div>
          <div>
            <div class="fs-3 fw-bold" style="color:#1e293b"><?= $user ? date('M Y', strtotime($user['created_at'])) : '-' ?></div>
            <div class="text-muted small">Member Since</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
      <div class="stat-card-premium">
        <div class="stat-border" style="background:linear-gradient(90deg,#f59e0b,#d97706)"></div>
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#fef3c7;color:#f59e0b"><i class="fas fa-shield-alt"></i></div>
          <div>
            <div class="fs-3 fw-bold" style="color:#1e293b"><?= $user['confirm_status'] ? 'Verified' : 'Unverified' ?></div>
            <div class="text-muted small">Account Status</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
      <a href="<?= APP_URL ?>/product/products.php" class="text-decoration-none">
        <div class="quick-action-card">
          <div class="qa-icon" style="background:#e0e7ff;color:#6366f1"><i class="fas fa-store"></i></div>
          <h6 class="fw-bold mb-1" style="color:#1e293b">Browse Products</h6>
          <p class="text-muted small mb-0">Explore our latest collection</p>
        </div>
      </a>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
      <a href="<?= APP_URL ?>/user/orders.php" class="text-decoration-none">
        <div class="quick-action-card">
          <div class="qa-icon" style="background:#dcfce7;color:#22c55e"><i class="fas fa-box"></i></div>
          <h6 class="fw-bold mb-1" style="color:#1e293b">My Orders</h6>
          <p class="text-muted small mb-0">Track and manage orders</p>
        </div>
      </a>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
      <a href="<?= APP_URL ?>/user/profile.php" class="text-decoration-none">
        <div class="quick-action-card">
          <div class="qa-icon" style="background:#dbeafe;color:#3b82f6"><i class="fas fa-user-circle"></i></div>
          <h6 class="fw-bold mb-1" style="color:#1e293b">My Profile</h6>
          <p class="text-muted small mb-0">Update account details</p>
        </div>
      </a>
    </div>
  </div>

  <?php if (!empty($recentOrders)): ?>
  <div class="dash-card" data-aos="fade-up">
    <div class="card-header-custom">
      <h6 class="fw-bold mb-0" style="color:#1e293b"><i class="fas fa-clock me-2" style="color:#6366f1"></i>Recent Orders</h6>
      <a href="<?= APP_URL ?>/user/orders.php" class="btn btn-sm" style="background:#e0e7ff;color:#6366f1;border-radius:0.5rem;font-weight:600">View All</a>
    </div>
    <div class="table-responsive">
      <table class="table table-dash mb-0">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order):
            $names = explode('||', $order['product_names']);
          ?>
          <tr>
            <td><code style="background:#f1f5f9;padding:0.25rem 0.6rem;border-radius:0.375rem;color:#6366f1"><?= h($order['order_id']) ?></code></td>
            <td style="color:#64748b"><?= date('d M Y', strtotime($order['order_date'])) ?></td>
            <td style="color:#475569"><?= implode(', ', array_map('h', $names)) ?></td>
            <td class="fw-bold" style="color:#1e293b">$<?= number_format($order['total_amount'], 2) ?></td>
            <td>
              <?php
              $sClass = 'secondary';
              if ($order['status'] === 'Delivered') $sClass = 'success';
              elseif ($order['status'] === 'Shipped') $sClass = 'info';
              elseif ($order['status'] === 'Processing') $sClass = 'primary';
              elseif ($order['status'] === 'Cancelled') $sClass = 'danger';
              elseif ($order['status'] === 'Refunded') $sClass = 'warning';
              ?>
              <span class="badge-soft badge-soft-<?= $sClass ?>"><?= h($order['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="dash-card" data-aos="fade-up">
    <div class="card-body text-center py-5">
      <div style="width:80px;height:80px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
        <i class="fas fa-box-open fa-2x" style="color:#94a3b8"></i>
      </div>
      <h5 style="color:#1e293b">No orders yet</h5>
      <p class="text-muted">Start shopping to see your orders here.</p>
      <a href="<?= APP_URL ?>/product/products.php" class="btn" style="background:#6366f1;color:#fff;border-radius:0.5rem;padding:0.5rem 1.5rem"><i class="fas fa-store me-1"></i> Browse Products</a>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
