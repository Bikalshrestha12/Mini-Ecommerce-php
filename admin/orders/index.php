<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Orders';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $validStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Refunded'];
        if ($order_id && in_array($status, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            $success = 'Order status updated.';
        }
    }

    if ($action === 'update_payment') {
        $order_id = $_POST['order_id'] ?? '';
        $payment_status = trim($_POST['payment_status'] ?? '');
        if ($order_id && $payment_status) {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE order_id = ?");
            $stmt->execute([$payment_status, $order_id]);
            $success = 'Payment status updated.';
        }
    }

    if ($action === 'update_tracking') {
        $order_id = $_POST['order_id'] ?? '';
        $tracking_number = trim($_POST['tracking_number'] ?? '');
        if ($order_id) {
            $stmt = $pdo->prepare("UPDATE orders SET tracking_number = ?, updated_at = NOW() WHERE order_id = ?");
            $stmt->execute([$tracking_number ?: null, $order_id]);
            $success = 'Tracking number updated.';
        }
    }

    if ($action === 'delete') {
        $order_id = $_POST['order_id'] ?? '';
        if ($order_id) {
            $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $success = 'Order deleted.';
        }
    }
}

$orders = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC")->fetchAll();
$orderStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Refunded'];
$paymentStatuses = ['Pending', 'Completed', 'Failed', 'Refunded'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Order Management</h5>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('h', $errors)) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= h($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="ordersTable">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Tracking</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><strong><?= h($o['order_id']) ?></strong></td>
                        <td>
                            <span><?= h($o['user_name'] ?? 'Unknown') ?></span>
                            <small class="d-block text-muted"><?= h($o['user_email'] ?? '') ?></small>
                        </td>
                        <td>$<?= number_format($o['total_amount'], 2) ?></td>
                        <td><?= h($o['payment_method']) ?></td>
                        <td>
                            <span class="badge bg-<?= strtolower($o['payment_status']) === 'completed' ? 'success' : (strtolower($o['payment_status']) === 'pending' ? 'warning text-dark' : (strtolower($o['payment_status']) === 'refunded' ? 'info' : 'danger')) ?>">
                                <?= h($o['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= strtolower($o['status']) === 'delivered' ? 'success' : (strtolower($o['status']) === 'cancelled' || strtolower($o['status']) === 'refunded' ? 'danger' : (strtolower($o['status']) === 'pending' ? 'warning text-dark' : 'primary')) ?>">
                                <?= h($o['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($o['tracking_number']): ?>
                                <span class="small"><?= h($o['tracking_number']) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewOrder(<?= h(json_encode($o['order_id'])) ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= h(json_encode($o['order_id'])) ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
            </div>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="order_id" id="deleteId" value="">
</form>

<script>
function viewOrder(orderId) {
    fetch('get_order.php?id=' + orderId)
        .then(r => r.json())
        .then(data => {
            let o = data.order;
            let items = data.items || [];
            let html = '<div class="row">';

            html += '<div class="col-md-6">';
            html += '<h6 class="fw-bold">Order Information</h6>';
            html += '<table class="table table-bordered table-sm">';
            html += '<tr><th style="width:35%">Order ID</th><td>' + h(o.order_id) + '</td></tr>';
            html += '<tr><th>Customer</th><td>' + h(o.user_name || 'Unknown') + '</td></tr>';
            html += '<tr><th>Email</th><td>' + h(o.user_email || '') + '</td></tr>';
            html += '<tr><th>Date</th><td>' + h(o.order_date) + '</td></tr>';
            html += '<tr><th>Total</th><td><strong>$' + parseFloat(o.total_amount).toFixed(2) + '</strong></td></tr>';
            html += '<tr><th>Payment Method</th><td>' + h(o.payment_method) + '</td></tr>';
            html += '</table>';
            html += '</div>';

            html += '<div class="col-md-6">';
            html += '<h6 class="fw-bold">Status & Tracking</h6>';
            html += '<table class="table table-bordered table-sm">';
            html += '<tr><th style="width:35%">Order Status</th><td>';
            html += '<form method="post" class="d-inline">';
            html += '<input type="hidden" name="action" value="update_status">';
            html += '<input type="hidden" name="order_id" value="' + h(o.order_id) + '">';
            html += '<select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">';
            <?php foreach ($orderStatuses as $s): ?>
                html += '<option value="<?= $s ?>" ' + (o.status === '<?= $s ?>' ? 'selected' : '') + '><?= $s ?></option>';
            <?php endforeach; ?>
            html += '</select></form></td></tr>';

            html += '<tr><th>Payment Status</th><td>';
            html += '<form method="post" class="d-inline">';
            html += '<input type="hidden" name="action" value="update_payment">';
            html += '<input type="hidden" name="order_id" value="' + h(o.order_id) + '">';
            html += '<select name="payment_status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">';
            <?php foreach ($paymentStatuses as $ps): ?>
                html += '<option value="<?= $ps ?>" ' + (o.payment_status === '<?= $ps ?>' ? 'selected' : '') + '><?= $ps ?></option>';
            <?php endforeach; ?>
            html += '</select></form></td></tr>';

            html += '<tr><th>Tracking #</th><td>';
            html += '<form method="post" class="d-flex gap-1">';
            html += '<input type="hidden" name="action" value="update_tracking">';
            html += '<input type="hidden" name="order_id" value="' + h(o.order_id) + '">';
            html += '<input type="text" name="tracking_number" class="form-control form-control-sm" style="width:160px" value="' + h(o.tracking_number || '') + '" placeholder="Tracking #">';
            html += '<button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>';
            html += '</form></td></tr>';

            if (o.shipping_address) {
                html += '<tr><th>Shipping Address</th><td>' + h(o.shipping_address) + '</td></tr>';
            }

            html += '</table>';
            html += '</div>';

            html += '</div>';

            html += '<h6 class="fw-bold mt-3">Order Items</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
            html += '<thead class="table-light"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>';
            let total = 0;
            items.forEach(item => {
                let subtotal = parseFloat(item.price) * parseInt(item.quantity);
                total += subtotal;
                html += '<tr><td>' + h(item.product_name || 'Product #' + item.product_id) + '</td><td>$' + parseFloat(item.price).toFixed(2) + '</td><td>' + item.quantity + '</td><td>$' + subtotal.toFixed(2) + '</td></tr>';
            });
            html += '<tr class="table-info"><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold">$' + total.toFixed(2) + '</td></tr>';
            html += '</tbody></table></div>';

            document.getElementById('orderDetailContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
        });
}

function confirmDelete(orderId) {
    Swal.fire({
        title: 'Delete order ' + orderId + '?',
        text: 'This cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deleteId').value = orderId;
            document.getElementById('deleteForm').submit();
        }
    });
}

function h(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
