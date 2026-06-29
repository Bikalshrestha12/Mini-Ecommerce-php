<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getDB();
$userId = $_SESSION['user'];
$orderId = $_GET['order_id'] ?? '';

if (empty($orderId)) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.product_id = oi.product_id WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userInfo = $stmt->fetch();

$settings = getAllSettings();
$companyName = $settings['site_name'] ?? APP_NAME;
$companyAddress = $settings['contact_address'] ?? '';
$companyEmail = $settings['contact_email'] ?? '';
$companyPhone = $settings['contact_phone'] ?? '';
$companyLogo = $settings['site_logo'] ?? '';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?= h($orderId) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; padding: 2rem; }
        .invoice-wrap { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }
        .invoice-header { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; padding: 2rem 2.5rem; }
        .invoice-header h2 { margin: 0; font-weight: 700; }
        .invoice-body { padding: 2.5rem; }
        .table th { background: #f1f5f9; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .total-row td { font-weight: 700; font-size: 1.1rem; border-top: 2px solid #dee2e6; }
        .status-badge { padding: 0.35rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-wrap { box-shadow: none; border-radius: 0; }
            .no-print { display: none !important; }
            .invoice-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Invoice</button>
        <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    </div>

    <div class="invoice-wrap">
        <div class="invoice-header d-flex justify-content-between align-items-start">
            <div>
                <?php if ($companyLogo): ?>
                <img src="<?= APP_URL . '/' . h($companyLogo) ?>" alt="" style="height:50px;margin-bottom:0.5rem">
                <?php endif; ?>
                <h2><i class="fas fa-file-invoice"></i> Invoice</h2>
                <p class="mb-0 opacity-75"><?= h($companyName) ?></p>
            </div>
            <div class="text-end">
                <h5 class="mb-1"><?= h($orderId) ?></h5>
                <small>Date: <?= date('d M Y', strtotime($order['order_date'])) ?></small>
            </div>
        </div>

        <div class="invoice-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-muted text-uppercase small">From</h6>
                    <p class="mb-0 fw-bold"><?= h($companyName) ?></p>
                    <?php if ($companyAddress): ?><p class="mb-0 small"><?= nl2br(h($companyAddress)) ?></p><?php endif; ?>
                    <?php if ($companyEmail): ?><p class="mb-0 small"><?= h($companyEmail) ?></p><?php endif; ?>
                    <?php if ($companyPhone): ?><p class="mb-0 small"><?= h($companyPhone) ?></p><?php endif; ?>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h6 class="fw-bold text-muted text-uppercase small">Bill To</h6>
                    <p class="mb-0 fw-bold"><?= h($userInfo['name'] ?? '') ?></p>
                    <p class="mb-0 small"><?= h($userInfo['email'] ?? '') ?></p>
                    <?php if (!empty($userInfo['phone'])): ?><p class="mb-0 small"><?= h($userInfo['phone']) ?></p><?php endif; ?>
                    <?php if (!empty($userInfo['address'])): ?><p class="mb-0 small"><?= nl2br(h($userInfo['address'])) ?></p><?php endif; ?>
                </div>
            </div>

            <?php
            $osClass = 'secondary'; $osIcon = 'clock';
            if ($order['status'] === 'Delivered') { $osClass = 'success'; $osIcon = 'check-circle'; }
            elseif ($order['status'] === 'Shipped') { $osClass = 'info'; $osIcon = 'truck'; }
            elseif ($order['status'] === 'Processing') { $osClass = 'primary'; $osIcon = 'cog'; }
            elseif ($order['status'] === 'Cancelled') { $osClass = 'danger'; $osIcon = 'times-circle'; }
            elseif ($order['status'] === 'Refunded') { $osClass = 'warning text-dark'; $osIcon = 'undo'; }
            ?>
            <div class="mb-4">
                <span class="status-badge bg-<?= $osClass ?>"><i class="fas fa-<?= $osIcon ?>"></i> <?= h($order['status']) ?></span>
                <span class="status-badge bg-<?= $order['payment_status'] === 'Completed' ? 'success' : 'warning text-dark' ?> ms-2">
                    <i class="fas fa-<?= $order['payment_status'] === 'Completed' ? 'check-circle' : 'clock' ?>"></i> <?= h($order['payment_status']) ?>
                </span>
                <span class="text-muted ms-2 small"><i class="fas fa-wallet"></i> <?= h($order['payment_method']) ?></span>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; foreach ($items as $item): $i++; ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= h($item['name']) ?></td>
                        <td class="text-center"><?= (int)$item['quantity'] ?></td>
                        <td class="text-end">$<?= number_format((float)$item['price'], 2) ?></td>
                        <td class="text-end">$<?= number_format((int)$item['quantity'] * (float)$item['price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" class="text-end">Total</td>
                        <td class="text-end text-primary">$<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="text-center text-muted small mt-4 pt-3 border-top">
                <p class="mb-0">Thank you for your purchase!</p>
                <p class="mb-0"><?= h($companyName) ?> &mdash; <?= h($companyEmail) ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
