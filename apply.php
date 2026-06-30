<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getDB();
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
$jobId = $_GET['job_id'] ?? '';

if (!in_array($type, ['admission', 'career'])) {
    header('Location: index.php');
    exit;
}

$editData = null;
$msg = '';
$error = '';

if ($id) {
    if ($type === 'admission') {
        $stmt = $pdo->prepare("SELECT * FROM admission_applications WHERE application_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM career_applications WHERE application_id = ?");
        $stmt->execute([$id]);
        $editData = $stmt->fetch();
    }
    if (!$editData) {
        $error = 'Application not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantName = trim($_POST['applicant_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $errors = [];

    if (empty($applicantName)) $errors[] = 'Applicant name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';

    if ($type === 'admission') {
        $programId = $_POST['program_id'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $previousEducation = trim($_POST['previous_education'] ?? '');

        if (empty($programId)) $errors[] = 'Program is required.';

        $documents = null;
        if (!empty($_FILES['documents']['name'])) {
            $documents = uploadFile($_FILES['documents'], 'admissions', ['jpg','jpeg','png','webp','gif','pdf','doc','docx']);
            if (!$documents) $errors[] = 'Document upload failed. Only jpg, png, pdf allowed (max 5MB).';
        }

        if (empty($errors)) {
            if ($id && $editData) {
                if ($documents) {
                    $stmt = $pdo->prepare("UPDATE admission_applications SET program_id=?, applicant_name=?, email=?, phone=?, address=?, date_of_birth=?, gender=?, previous_education=?, documents=?, updated_at=datetime('now') WHERE application_id=?");
                    $stmt->execute([$programId, $applicantName, $email, $phone, $address, $dateOfBirth ?: null, $gender, $previousEducation, $documents, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admission_applications SET program_id=?, applicant_name=?, email=?, phone=?, address=?, date_of_birth=?, gender=?, previous_education=?, updated_at=datetime('now') WHERE application_id=?");
                    $stmt->execute([$programId, $applicantName, $email, $phone, $address, $dateOfBirth ?: null, $gender, $previousEducation, $id]);
                }
                $msg = 'Application updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO admission_applications (program_id, applicant_name, email, phone, address, date_of_birth, gender, previous_education, documents, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
                $stmt->execute([$programId, $applicantName, $email, $phone, $address, $dateOfBirth ?: null, $gender, $previousEducation, $documents]);
                $msg = 'Application submitted successfully.';
            }
        }
    } else {
        $selectedJobId = $_POST['job_id'] ?? '';
        $coverLetter = trim($_POST['cover_letter'] ?? '');

        if (empty($selectedJobId)) $errors[] = 'Job is required.';

        $resumeFile = null;
        if (!empty($_FILES['resume_file']['name'])) {
            $resumeFile = uploadFile($_FILES['resume_file'], 'resumes', ['jpg','jpeg','png','webp','gif','pdf','doc','docx']);
            if (!$resumeFile) $errors[] = 'Resume upload failed. Only jpg, png, pdf allowed (max 5MB).';
        }

        if (empty($errors)) {
            if ($id && $editData) {
                if ($resumeFile) {
                    $stmt = $pdo->prepare("UPDATE career_applications SET job_id=?, applicant_name=?, email=?, phone=?, cover_letter=?, resume_file=?, updated_at=datetime('now') WHERE application_id=?");
                    $stmt->execute([$selectedJobId, $applicantName, $email, $phone, $coverLetter, $resumeFile, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE career_applications SET job_id=?, applicant_name=?, email=?, phone=?, cover_letter=?, updated_at=datetime('now') WHERE application_id=?");
                    $stmt->execute([$selectedJobId, $applicantName, $email, $phone, $coverLetter, $id]);
                }
                $msg = 'Application updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO career_applications (job_id, applicant_name, email, phone, cover_letter, resume_file, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
                $stmt->execute([$selectedJobId, $applicantName, $email, $phone, $coverLetter, $resumeFile]);
                $msg = 'Application submitted successfully.';
            }
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

$programs = [];
$jobs = [];
if ($type === 'admission') {
    $programs = $pdo->query("SELECT program_id, title FROM admission_programs WHERE is_active = 1 ORDER BY title")->fetchAll();
} else {
    $stmt = $pdo->query("SELECT job_id, title FROM career_jobs WHERE is_active = 1 ORDER BY title");
    $jobs = $stmt->fetchAll();
}

$title = $type === 'admission' ? 'Admission Application' : 'Career Application';

require_once __DIR__ . '/includes/public_header.php';
?>

<style>
.apply-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 4px 24px rgba(0,0,0,.08);
  overflow: hidden;
}
.apply-card .apply-header {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  padding: 1.75rem 2rem;
  position: relative;
  overflow: hidden;
}
.apply-card .apply-header::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.3;
}
.apply-card .apply-body { padding: 2rem; }

.floating-group {
  position: relative;
  margin-bottom: 1.5rem;
}
.floating-group .form-control,
.floating-group .form-select {
  height: 56px;
  border-radius: 0.75rem;
  border: 2px solid #e2e8f0;
  padding: 1.5rem 1rem 0.5rem;
  font-size: 0.95rem;
  transition: all 0.2s;
  background: #fff;
}
.floating-group .form-control:focus,
.floating-group .form-select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.floating-group textarea.form-control {
  height: auto;
  min-height: 100px;
  padding-top: 1.6rem;
}
.floating-group label {
  position: absolute;
  top: 1.05rem;
  left: 1rem;
  font-size: 0.85rem;
  color: #94a3b8;
  transition: all 0.2s ease;
  pointer-events: none;
  background: #fff;
  padding: 0 0.25rem;
}
.floating-group .form-control:focus ~ label,
.floating-group .form-control:not(:placeholder-shown) ~ label,
.floating-group .form-select:focus ~ label,
.floating-group .form-select:not([value=""]):valid ~ label {
  top: 0.35rem;
  left: 0.85rem;
  font-size: 0.7rem;
  color: #6366f1;
}
.floating-group .form-control::placeholder { color: transparent; }

.file-drop-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 1rem;
  padding: 2.5rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f8fafc;
}
.file-drop-zone:hover,
.file-drop-zone.dragover {
  border-color: #6366f1;
  background: #e0e7ff;
}
.file-drop-zone .drop-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: #e0e7ff;
  color: #6366f1;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 0.75rem;
  font-size: 1.25rem;
}
.file-drop-zone .file-name {
  font-size: 0.85rem;
  color: #6366f1;
  font-weight: 500;
  margin-top: 0.5rem;
}

.btn-gradient {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border: none;
  border-radius: 0.75rem;
  padding: 0.75rem 2rem;
  font-weight: 600;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}
.btn-gradient:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99,102,241,.35);
  color: #fff;
}
.btn-gradient:disabled {
  opacity: 0.7;
  transform: none;
}
.spinner-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(255,255,255,0.8);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}
.spinner-overlay.show { display: flex; }
.spinner-overlay .spinner-ring {
  width: 56px; height: 56px;
  border: 4px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.spinner-overlay .spinner-text {
  margin-top: 1rem;
  color: #6366f1;
  font-weight: 600;
}

.alert-premium {
  border-radius: 0.75rem;
  border: none;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.alert-premium.alert-success { background: #dcfce7; color: #166534; }
.alert-premium.alert-danger { background: #fee2e2; color: #991b1b; }

.current-file-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  border-radius: 9999px;
  color: #6366f1;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s;
}
.current-file-link:hover { background: #e0e7ff; color: #4338ca; }
</style>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="apply-card" data-aos="fade-up">
        <div class="apply-header position-relative">
          <div class="d-flex align-items-center gap-3 position-relative">
            <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff">
              <i class="fas fa-<?= $type === 'admission' ? 'graduation-cap' : 'briefcase' ?>"></i>
            </div>
            <div class="text-white">
              <h4 class="fw-bold mb-0"><?= h($title) ?></h4>
              <p class="mb-0 opacity-75 small">Fill in your details below</p>
            </div>
          </div>
        </div>
        <div class="apply-body">
          <?php if ($msg): ?>
          <div class="alert-premium alert-success" data-aos="fade-up">
            <i class="fas fa-check-circle fs-5"></i>
            <span><?= $msg ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
          </div>
          <?php elseif ($error): ?>
          <div class="alert-premium alert-danger" data-aos="fade-up">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <span><?= $error ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" id="applyForm">
            <div class="row g-0">
              <div class="col-md-6 pe-md-2">
                <div class="floating-group">
                  <input type="text" name="applicant_name" class="form-control" placeholder="Full Name" required
                         value="<?= h($editData['applicant_name'] ?? ($_SESSION['name'] ?? '')) ?>">
                  <label>Full Name <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-md-6 ps-md-2">
                <div class="floating-group">
                  <input type="email" name="email" class="form-control" placeholder="Email" required
                         value="<?= h($editData['email'] ?? ($_SESSION['email'] ?? '')) ?>">
                  <label>Email <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-md-6 pe-md-2">
                <div class="floating-group">
                  <input type="text" name="phone" class="form-control" placeholder="Phone" required
                         value="<?= h($editData['phone'] ?? '') ?>">
                  <label>Phone <span class="text-danger">*</span></label>
                </div>
              </div>

              <?php if ($type === 'admission'): ?>
              <div class="col-md-6 ps-md-2">
                <div class="floating-group">
                  <select name="program_id" class="form-select" required>
                    <option value="" disabled <?= empty($editData['program_id']) ? 'selected' : '' ?>></option>
                    <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['program_id'] ?>" <?= ($editData['program_id'] ?? '') == $p['program_id'] ? 'selected' : '' ?>><?= h($p['title']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Program <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-md-6 pe-md-2">
                <div class="floating-group">
                  <input type="text" name="address" class="form-control" placeholder="Address" value="<?= h($editData['address'] ?? '') ?>">
                  <label>Address</label>
                </div>
              </div>
              <div class="col-md-6 ps-md-2">
                <div class="floating-group">
                  <input type="date" name="date_of_birth" class="form-control" placeholder="Date of Birth" value="<?= h($editData['date_of_birth'] ?? '') ?>">
                  <label>Date of Birth</label>
                </div>
              </div>
              <div class="col-md-6 pe-md-2">
                <div class="floating-group">
                  <select name="gender" class="form-select">
                    <option value="" disabled <?= empty($editData['gender']) ? 'selected' : '' ?>></option>
                    <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                    <option value="<?= $g ?>" <?= ($editData['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Gender</label>
                </div>
              </div>
              <div class="col-12">
                <div class="floating-group">
                  <textarea name="previous_education" class="form-control" placeholder="Previous Education" rows="3"><?= h($editData['previous_education'] ?? '') ?></textarea>
                  <label>Previous Education</label>
                </div>
              </div>
              <div class="col-12">
                <label style="font-weight:600;color:#1e293b;font-size:0.9rem;margin-bottom:0.5rem">Documents</label>
                <div class="file-drop-zone" id="fileDropZone">
                  <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                  <p class="mb-1 fw-medium" style="color:#1e293b">Drag & drop files here or click to browse</p>
                  <small class="text-muted">Supports: JPG, PNG, PDF, DOC, DOCX (max 5MB)</small>
                  <div class="file-name" id="fileNameDisplay" style="display:none"></div>
                  <input type="file" name="documents" id="fileInput" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                </div>
                <?php if (!empty($editData['documents'])): ?>
                <div class="mt-2">
                  <a href="<?= APP_URL . '/' . h($editData['documents']) ?>" target="_blank" class="current-file-link">
                    <i class="fas fa-download"></i> Current Document
                  </a>
                </div>
                <?php endif; ?>
              </div>
              <?php else: ?>
              <div class="col-md-6 ps-md-2">
                <div class="floating-group">
                  <select name="job_id" class="form-select" required>
                    <option value="" disabled <?= empty($editData['job_id'] ?? $jobId) ? 'selected' : '' ?>></option>
                    <?php foreach ($jobs as $j): ?>
                    <option value="<?= $j['job_id'] ?>" <?= ($editData['job_id'] ?? $jobId) == $j['job_id'] ? 'selected' : '' ?>><?= h($j['title']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Job Position <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-12">
                <div class="floating-group">
                  <textarea name="cover_letter" class="form-control" placeholder="Cover Letter" rows="5"><?= h($editData['cover_letter'] ?? '') ?></textarea>
                  <label>Cover Letter</label>
                </div>
              </div>
              <div class="col-12">
                <label style="font-weight:600;color:#1e293b;font-size:0.9rem;margin-bottom:0.5rem">Resume / CV</label>
                <div class="file-drop-zone" id="fileDropZone">
                  <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                  <p class="mb-1 fw-medium" style="color:#1e293b">Drag & drop your resume here or click to browse</p>
                  <small class="text-muted">Supports: JPG, PNG, PDF, DOC, DOCX (max 5MB)</small>
                  <div class="file-name" id="fileNameDisplay" style="display:none"></div>
                  <input type="file" name="resume_file" id="fileInput" class="d-none" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                </div>
                <?php if (!empty($editData['resume_file'])): ?>
                <div class="mt-2">
                  <a href="<?= APP_URL . '/' . h($editData['resume_file']) ?>" target="_blank" class="current-file-link">
                    <i class="fas fa-download"></i> Current Resume
                  </a>
                </div>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="submit" class="btn btn-gradient px-5" id="submitBtn">
                <i class="fas fa-paper-plane me-1"></i> <?= $id ? 'Update Application' : 'Submit Application' ?>
              </button>
              <a href="index.php" class="btn" style="background:#f1f5f9;color:#64748b;border-radius:0.75rem;font-weight:500;padding:0.75rem 1.5rem"><i class="fas fa-times me-1"></i> Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="spinner-overlay" id="spinnerOverlay">
  <div class="spinner-ring"></div>
  <div class="spinner-text">Submitting your application...</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const dropZone = document.getElementById('fileDropZone');
  const fileInput = document.getElementById('fileInput');
  const fileNameDisplay = document.getElementById('fileNameDisplay');

  if (dropZone && fileInput) {
    dropZone.addEventListener('click', function() { fileInput.click(); });

    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', function() {
      dropZone.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.classList.remove('dragover');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFileSelect();
      }
    });
    fileInput.addEventListener('change', handleFileSelect);
  }

  function handleFileSelect() {
    if (fileInput.files && fileInput.files[0]) {
      fileNameDisplay.textContent = fileInput.files[0].name;
      fileNameDisplay.style.display = 'block';
    }
  }

  const form = document.getElementById('applyForm');
  const submitBtn = document.getElementById('submitBtn');
  const spinner = document.getElementById('spinnerOverlay');

  if (form) {
    form.addEventListener('submit', function() {
      if (submitBtn) submitBtn.disabled = true;
      if (spinner) spinner.classList.add('show');
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
