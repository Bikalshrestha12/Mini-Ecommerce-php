<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['flash_success'] = 'Message marked as read.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['mark_unread'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['flash_success'] = 'Message marked as unread.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['send_reply'])) {
        $message_id = (int)$_POST['message_id'];
        $reply = $_POST['reply'] ?? '';
        if (!empty($reply)) {
            $stmt = $pdo->prepare("UPDATE contact_messages SET reply = ?, replied_at = datetime('now') WHERE message_id = ?");
            $stmt->execute([$reply, $message_id]);
            $_SESSION['flash_success'] = 'Reply sent successfully.';
        }
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_message'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['flash_success'] = 'Message deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

$pageTitle = 'Contact Messages';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Contact Messages</h1>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="contactTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Replied</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr class="<?= !$msg['is_read'] ? 'table-warning' : '' ?>">
                            <td><?= $msg['message_id'] ?></td>
                            <td><strong><?= h($msg['name']) ?></strong></td>
                            <td><a href="mailto:<?= h($msg['email']) ?>"><?= h($msg['email']) ?></a></td>
                            <td><?= h($msg['subject']) ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <span class="status-badge <?= $msg['is_read'] ? 'status-read' : 'status-unread' ?>">
                                    <?= $msg['is_read'] ? 'Read' : 'Unread' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($msg['replied_at']): ?>
                                <span class="text-success"><i class="fas fa-check-circle"></i> Yes</span>
                                <small class="d-block text-muted"><?= date('M d, Y', strtotime($msg['replied_at'])) ?></small>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info view-message text-white"
                                    data-id="<?= $msg['message_id'] ?>"
                                    data-name="<?= h($msg['name'], ENT_QUOTES) ?>"
                                    data-email="<?= h($msg['email'], ENT_QUOTES) ?>"
                                    data-phone="<?= h($msg['phone'], ENT_QUOTES) ?>"
                                    data-subject="<?= h($msg['subject'], ENT_QUOTES) ?>"
                                    data-message="<?= h($msg['message'], ENT_QUOTES) ?>"
                                    data-created="<?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?>"
                                    data-is_read="<?= $msg['is_read'] ?>"
                                    data-reply="<?= h($msg['reply'], ENT_QUOTES) ?>"
                                    data-replied_at="<?= $msg['replied_at'] ? date('M d, Y h:i A', strtotime($msg['replied_at'])) : '' ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-message" data-id="<?= $msg['message_id'] ?>">
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
</div>

<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Name:</strong> <span id="view_name"></span></div>
                    <div class="col-md-4"><strong>Email:</strong> <span id="view_email"></span></div>
                    <div class="col-md-4"><strong>Phone:</strong> <span id="view_phone"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Subject:</strong> <span id="view_subject"></span></div>
                    <div class="col-md-6"><strong>Date:</strong> <span id="view_date"></span></div>
                </div>
                <div class="mb-3">
                    <strong>Message:</strong>
                    <div class="bg-light p-3 rounded mt-1" id="view_message"></div>
                </div>
                <div class="mb-3" id="reply_section" style="display:none">
                    <strong>Your Reply:</strong>
                    <div class="bg-success bg-opacity-10 p-3 rounded mt-1" id="view_reply"></div>
                    <small class="text-muted" id="view_replied_at"></small>
                </div>
                <hr>
                <form method="post" id="replyForm">
                    <input type="hidden" name="message_id" id="reply_message_id">
                    <div class="mb-3">
                        <label class="form-label"><strong>Send Reply</strong></label>
                        <textarea name="reply" class="form-control" rows="4" placeholder="Type your reply here..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="send_reply" class="btn btn-primary">
                            <i class="fas fa-reply"></i> Send Reply
                        </button>
                        <button type="submit" name="mark_read" class="btn btn-outline-info" id="markReadBtn">
                            <i class="fas fa-check"></i> Mark as Read
                        </button>
                        <button type="submit" name="mark_unread" class="btn btn-outline-warning" id="markUnreadBtn" style="display:none">
                            <i class="fas fa-envelope"></i> Mark as Unread
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="message_id" id="delete_id">
    <input type="hidden" name="delete_message">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#contactTable').DataTable({
        order: [[4, 'desc']],
        pageLength: 25
    });

    $(document).on('click', '.view-message', function() {
        const d = $(this).data();
        $('#view_name').text(d.name);
        $('#view_email').html('<a href="mailto:' + d.email + '">' + d.email + '</a>');
        $('#view_phone').text(d.phone || '—');
        $('#view_subject').text(d.subject || '—');
        $('#view_date').text(d.created);
        $('#view_message').text(d.message);
        $('#reply_message_id').val(d.id);

        if (d.reply) {
            $('#reply_section').show();
            $('#view_reply').text(d.reply);
            $('#view_replied_at').text('Replied on: ' + d.replied_at);
        } else {
            $('#reply_section').hide();
        }

        if (d.is_read == 1) {
            $('#markReadBtn').hide();
            $('#markUnreadBtn').show();
        } else {
            $('#markReadBtn').show();
            $('#markUnreadBtn').hide();
        }

        $('#viewMessageModal').modal('show');
    });

    $(document).on('click', '.delete-message', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Message?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $('#delete_id').val(id);
                $('#deleteForm').submit();
            }
        });
    });
});
</script>
JS;
?>
<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
