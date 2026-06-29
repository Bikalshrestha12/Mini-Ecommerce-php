<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getDB();
$userId = $_SESSION['user'];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$totalOrders = (int)$stmt->fetchColumn();
$totalPages = max(1, ceil($totalOrders / $limit));

$stmt = $pdo->prepare("SELECT o.*, GROUP_CONCAT(p.name ORDER BY oi.item_id SEPARATOR '||') AS product_names, GROUP_CONCAT(oi.quantity ORDER BY oi.item_id SEPARATOR '||') AS quantities, GROUP_CONCAT(oi.price ORDER BY oi.item_id SEPARATOR '||') AS item_prices, GROUP_CONCAT(p.image ORDER BY oi.item_id SEPARATOR '||') AS images FROM orders o JOIN order_items oi ON oi.order_id = o.order_id JOIN products p ON p.product_id = oi.product_id WHERE o.user_id = ? GROUP BY o.order_id ORDER BY o.order_date DESC LIMIT $limit OFFSET $offset");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$orderStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Refunded'];
$statusIcons = ['Pending' => 'clock', 'Processing' => 'cog', 'Shipped' => 'truck', 'Delivered' => 'check-circle', 'Cancelled' => 'times-circle', 'Refunded' => 'undo'];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.orders-header-bg {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 1.25rem;
  padding: 2rem 2.5rem;
  position: relative;
  overflow: hidden;
}
.orders-header-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.3;
}
.order-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  overflow: hidden;
  transition: all 0.3s ease;
}
.order-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.order-card .order-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
}
.order-card .order-body { padding: 1.25rem 1.5rem; }
.order-card .order-footer {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
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
.status-tracker {
  display: flex;
  align-items: center;
  gap: 0;
  margin-bottom: 1rem;
}
.status-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  position: relative;
}
.status-step .step-dot {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem;
  z-index: 2;
  transition: all 0.3s;
}
.status-step .step-label {
  font-size: 0.65rem;
  margin-top: 0.35rem;
  white-space: nowrap;
  font-weight: 500;
}
.status-step .step-line {
  position: absolute;
  top: 16px; left: 50%; width: 100%; height: 3px;
  z-index: 1;
}
.status-step:last-child .step-line { display: none; }
.btn-soft-primary {
  background: #e0e7ff;
  color: #6366f1;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  padding: 0.4rem 1rem;
  transition: all 0.2s;
}
.btn-soft-primary:hover { background: #c7d2fe; color: #4338ca; }
.empty-orders {
  background: #fff;
  border-radius: 1rem;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.empty-orders .empty-icon {
  width: 100px; height: 100px; border-radius: 50%;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.5rem;
}
.pagination-custom .page-link {
  border: none;
  border-radius: 0.5rem !important;
  margin: 0 0.15rem;
  color: #64748b;
  font-weight: 500;
  padding: 0.5rem 0.9rem;
}
.pagination-custom .page-item.active .page-link {
  background: #6366f1;
  color: #fff;
}
.pagination-custom .page-item.disabled .page-link {
  opacity: 0.5;
}
</style>

<div class="container-fluid px-4 py-4">
  <div class="orders-header-bg mb-4" data-aos="fade-up">
    <div class="d-flex align-items-center gap-3 position-relative">
      <div style="width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff">
        <i class="fas fa-box"></i>
      </div>
      <div class="text-white">
        <h3 class="fw-bold mb-1">My Orders</h3>
        <p class="mb-0 opacity-75">Track and manage your purchases</p>
      </div>
    </div>
  </div>

  <?php if (empty($orders)): ?>
  <div class="empty-orders" data-aos="fade-up">
    <div class="empty-icon"><i class="fas fa-box-open fa-3x" style="color:#94a3b8"></i></div>
    <h5 style="color:#1e293b">No orders yet</h5>
    <p class="text-muted mb-4">Your order history will appear here after your first purchase.</p>
    <a href="<?= APP_URL ?>/product/products.php" class="btn" style="background:#6366f1;color:#fff;border-radius:0.5rem;padding:0.6rem 2rem;font-weight:600"><i class="fas fa-store me-1"></i> Start Shopping</a>
  </div>
  <?php else: ?>
  <?php foreach ($orders as $order):
    $names = explode('||', $order['product_names']);
    $quantities = explode('||', $order['quantities']);
    $prices = explode('||', $order['item_prices']);
    $images = explode('||', $order['images'] ?? '');
    $currentIdx = array_search($order['status'], $orderStatuses);
    if ($currentIdx === false) $currentIdx = 0;
  ?>
  <div class="order-card mb-4" data-aos="fade-up">
    <div class="order-header">
      <div>
        <strong style="color:#6366f1"><?= h($order['order_id']) ?></strong>
        <span class="text-muted ms-2 small"><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></span>
      </div>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <?php
        $psClass = 'secondary';
        if ($order['payment_status'] === 'Completed') $psClass = 'success';
        elseif ($order['payment_status'] === 'Pending') $psClass = 'warning';
        elseif ($order['payment_status'] === 'Failed') $psClass = 'danger';
        elseif ($order['payment_status'] === 'Refunded') $psClass = 'info';
        $osClass = 'secondary';
        if ($order['status'] === 'Delivered') $osClass = 'success';
        elseif ($order['status'] === 'Shipped') $osClass = 'info';
        elseif ($order['status'] === 'Processing') $osClass = 'primary';
        elseif ($order['status'] === 'Cancelled') $osClass = 'danger';
        elseif ($order['status'] === 'Refunded') $osClass = 'warning';
        ?>
        <span class="badge-soft badge-soft-<?= $psClass ?>"><i class="fas fa-<?= $order['payment_status'] === 'Completed' ? 'check-circle' : 'clock' ?> me-1"></i><?= h($order['payment_status']) ?></span>
        <span class="badge-soft badge-soft-<?= $osClass ?>"><?= h($order['status']) ?></span>
        <a href="<?= APP_URL ?>/user/invoice.php?order_id=<?= urlencode($order['order_id']) ?>" class="btn-soft-primary" target="_blank">
          <i class="fas fa-file-invoice me-1"></i> Invoice
        </a>
      </div>
    </div>
    <div class="order-body">
      <div class="status-tracker">
        <?php foreach ($orderStatuses as $i => $s): ?>
        <div class="status-step">
          <?php if ($i < count($orderStatuses) - 1): ?>
          <div class="step-line" style="background:<?= $i < $currentIdx ? '#6366f1' : ($i === $currentIdx ? 'linear-gradient(90deg, #6366f1 50%, #e2e8f0 50%)' : '#e2e8f0') ?>"></div>
          <?php endif; ?>
          <div class="step-dot" style="background:<?= $i <= $currentIdx ? '#6366f1' : '#f1f5f9' ?>;color:<?= $i <= $currentIdx ? '#fff' : '#94a3b8' ?>">
            <i class="fas fa-<?= $statusIcons[$s] ?>"></i>
          </div>
          <div class="step-label" style="color:<?= $i <= $currentIdx ? '#6366f1' : '#94a3b8' ?>"><?= $s ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <hr style="opacity:0.5;margin:1rem 0">
      <?php foreach ($names as $k => $name): ?>
      <div class="d-flex align-items-center gap-3 mb-2">
        <img src="<?= APP_URL ?>/assets/images/<?= h($images[$k] ?? 'placeholder.jpg') ?>" alt="" class="rounded" style="width:52px;height:52px;object-fit:cover" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
        <div class="flex-grow-1">
          <strong style="color:#1e293b"><?= h($name) ?></strong>
          <div class="text-muted small">Qty: <?= (int)($quantities[$k] ?? 0) ?> &times; $<?= number_format((float)($prices[$k] ?? 0), 2) ?></div>
        </div>
        <span class="fw-bold" style="color:#1e293b">$<?= number_format((int)($quantities[$k] ?? 0) * (float)($prices[$k] ?? 0), 2) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="order-footer">
      <span class="text-muted small"><?= count($names) ?> item(s) &middot; <?= h($order['payment_method']) ?></span>
      <h5 class="mb-0 fw-bold" style="color:#6366f1">$<?= number_format($order['total_amount'], 2) ?></h5>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
  <nav data-aos="fade-up">
    <ul class="pagination pagination-custom justify-content-center">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
