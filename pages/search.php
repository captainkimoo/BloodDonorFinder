<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$db = new Database(); $conn = $db->getConnection();
$donors = [];
if ($_SERVER['REQUEST_METHOD']=== 'POST' || isset($_GET['blood_type'])) {
  $blood_type  = sanitise($_POST['blood_type']  ?? $_GET['blood_type']  ?? '');
  $city        = sanitise($_POST['city']        ?? $_GET['city']        ?? '');
  $governorate = sanitise($_POST['governorate'] ?? $_GET['governorate'] ?? '');
 
  $compatible = getCompatibleTypes($blood_type);
  if (empty($compatible)) $compatible = [$blood_type];
 
  $placeholders = implode(',', array_fill(0, count($compatible), '?'));
  $params = array_merge($compatible, [$city]);
 
  $sql = "SELECT d.*, p.first_name, p.last_name, p.phone, p.city,
          p.governorate, u.email
          FROM donors d
          JOIN profiles p ON p.user_id = d.user_id
          JOIN users u ON u.user_id = d.user_id
          WHERE d.blood_type IN ($placeholders)
          AND d.is_available = TRUE
          AND u.status = 'active'
          AND p.city = ?";
 
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);
  $donors = $stmt->fetchAll();
}
?>
<?php
/* ... PHP logic block from Backend Phase 5 Feature 1 stays here unchanged ... */
?>

<?php
$pageTitle = 'Find Donors | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <h2 class="page-title mb-4">🔍 Find Blood Donors</h2>

  <!-- Search Form -->
  <div class="card mb-4">
    <div class="card-header bg-danger text-white fw-bold">
      Search by Blood Type &amp; Location
    </div>
    <div class="card-body">
      <form method="GET" action="">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="blood_type" class="form-label required-star">
              Blood Type Needed</label>
            <select name="blood_type" id="blood_type" class="form-select" required>
              <option value="">-- Select --</option>
              <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                <option value="<?=$bt?>"
                  <?= (($_GET['blood_type']??'')===$bt)?'selected':'' ?>>
                  <?=$bt?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="city" class="form-label required-star">City</label>
            <input type="text" name="city" id="city" class="form-control"
                   value="<?= htmlspecialchars($_GET['city']??'') ?>"
                   placeholder="e.g. Cairo">
          </div>
          <div class="col-md-3">
            <label for="governorate" class="form-label">Governorate (optional)</label>
            <input type="text" name="governorate" id="governorate"
                   class="form-control"
                   value="<?= htmlspecialchars($_GET['governorate']??'') ?>">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-search"></i> Search Donors</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Search Results -->
  <?php if (isset($_GET['blood_type'])): ?>
    <p class="text-muted mb-3">
      Found <strong><?= count($donors) ?></strong> compatible donor(s)
      in <strong><?= htmlspecialchars($_GET['city']??'') ?></strong>.
    </p>

    <?php if (!empty($donors)): ?>
      <div class="row g-3">
        <?php foreach($donors as $d): ?>
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="fw-bold mb-0">
                    <?= htmlspecialchars($d['first_name'].' '.$d['last_name']) ?>
                  </h5>
                  <span class="badge-blood"><?= $d['blood_type'] ?></span>
                </div>
                <p class="text-muted mb-1">
                  <i class="bi bi-geo-alt"></i>
                  <?= htmlspecialchars($d['city']) ?>
                  <?= $d['governorate'] ? ', '.htmlspecialchars($d['governorate']) : '' ?>
                </p>
                <p class="mb-2">
                  <i class="bi bi-telephone"></i>
                  <?= htmlspecialchars($d['phone']) ?>
                </p>
                <span class="badge badge-active">Available</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="text-center py-5">
        <div style="font-size:3rem">😔</div>
        <h4 class="mt-2">No donors found</h4>
        <p class="text-muted">No compatible donors are currently available in this city.</p>
        <a href="request.php" class="btn btn-primary">Submit a Blood Request</a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>