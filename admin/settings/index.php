<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settingGroups = ['general', 'contact', 'social', 'seo', 'footer'];
    $files = ['site_logo' => 'general', 'site_favicon' => 'general'];

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['update_settings'])) continue;
        $group = 'general';
        foreach ($settingGroups as $g) {
            if (strpos($key, $g . '_') === 0) { $group = $g; break; }
        }

        if (array_key_exists($key, $files)) continue;

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, setting_group = excluded.setting_group");
        $stmt->execute([$key, $value, $group]);
    }

    foreach ($files as $fileKey => $group) {
        if (!empty($_FILES[$fileKey]['name'])) {
            $subdir = ($fileKey === 'site_logo') ? 'logo' : 'favicon';
            $uploaded = uploadFile($_FILES[$fileKey], $subdir, ['jpg','jpeg','png','webp','gif','ico']);
            if ($uploaded) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)
                    ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, setting_group = excluded.setting_group");
                $stmt->execute([$fileKey, $uploaded, $group]);
            }
        }
    }

    $_SESSION['flash_success'] = 'Settings updated successfully.';
    header('Location: index.php');
    exit;
}

$settings = getAllSettings();
$groups = [
    'general' => ['site_name', 'site_description', 'site_logo', 'site_favicon'],
    'contact' => ['contact_email', 'contact_phone', 'contact_address', 'business_hours', 'google_map'],
    'social'  => ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'youtube_url'],
    'seo'     => ['seo_title', 'seo_description', 'seo_keywords'],
    'footer'  => ['footer_text', 'footer_description'],
];

$groupLabels = [
    'general' => 'General Settings',
    'contact' => 'Contact Information',
    'social'  => 'Social Media Links',
    'seo'     => 'SEO Settings',
    'footer'  => 'Footer Settings',
];

$groupIcons = [
    'general' => 'fa-cog',
    'contact' => 'fa-address-card',
    'social'  => 'fa-share-alt',
    'seo'     => 'fa-search',
    'footer'  => 'fa-print',
];

$pageTitle = 'Website Settings';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Website Settings</h1>
    </div>

    <?= flashMessage() ?>

    <form method="post" enctype="multipart/form-data">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <?php $first = true; foreach ($groups as $group => $keys): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= $group ?>" data-bs-toggle="tab" data-bs-target="#settings-<?= $group ?>" type="button" role="tab">
                            <i class="fas <?= $groupIcons[$group] ?>"></i> <?= $groupLabels[$group] ?>
                        </button>
                    </li>
                    <?php $first = false; endforeach; ?>
                </ul>

                <div class="tab-content" id="settingsTabContent">
                    <?php $first = true; foreach ($groups as $group => $keys): ?>
                    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="settings-<?= $group ?>" role="tabpanel">
                        <h5 class="mb-3"><?= $groupLabels[$group] ?></h5>
                        <div class="row">
                            <?php foreach ($keys as $key): ?>
                            <?php
                            $value = $settings[$key] ?? '';
                            $label = ucwords(str_replace('_', ' ', $key));
                            $type = 'text';
                            $isTextarea = false;
                            $isFile = false;
                            $helpText = '';

                            if (in_array($key, ['site_description', 'seo_description', 'footer_description'])) {
                                $isTextarea = true;
                            } elseif ($key === 'seo_keywords') {
                                $isTextarea = true;
                                $helpText = 'Comma-separated keywords';
                            } elseif (in_array($key, ['site_logo', 'site_favicon'])) {
                                $isFile = true;
                                $allowed = $key === 'site_favicon' ? 'jpg, jpeg, png, ico' : 'jpg, jpeg, png, webp, gif';
                                $helpText = 'Allowed: ' . $allowed;
                            } elseif (in_array($key, ['google_map'])) {
                                $isTextarea = true;
                                $helpText = 'Embed Google Maps iframe src URL';
                            } elseif (in_array($key, ['contact_email'])) {
                                $type = 'email';
                            } elseif (in_array($key, ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url', 'youtube_url'])) {
                                $type = 'url';
                                $helpText = 'Full URL including https://';
                            } elseif ($key === 'sort_order') { continue; }
                            ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><?= $label ?></label>
                                <?php if ($isTextarea): ?>
                                <textarea name="<?= $key ?>" class="form-control" rows="3"><?= h($value) ?></textarea>
                                <?php elseif ($isFile): ?>
                                <input type="file" name="<?= $key ?>" class="form-control" accept="image/*">
                                <?php if ($value): ?>
                                <div class="mt-2">
                                    <img src="<?= APP_URL ?>/<?= h($value) ?>" style="max-height:50px" class="rounded">
                                    <small class="text-muted d-block">Leave empty to keep current</small>
                                </div>
                                <?php endif; ?>
                                <?php else: ?>
                                <input type="<?= $type ?>" name="<?= $key ?>" class="form-control" value="<?= h($value) ?>">
                                <?php endif; ?>
                                <?php if ($helpText): ?>
                                <small class="text-muted"><?= $helpText ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $first = false; endforeach; ?>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
