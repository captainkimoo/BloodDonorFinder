<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$db = new Database(); $conn = $db->getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1. Collect and validate inputs
  $patient_name  = sanitise($_POST['patient_name']  ?? '');
  $blood_type    = sanitise($_POST['blood_type']    ?? '');
  $units_needed  = (int)($_POST['units_needed']    ?? 1);
  $hospital_name = sanitise($_POST['hospital_name'] ?? '');
  $city          = sanitise($_POST['city']          ?? '');
  $governorate   = sanitise($_POST['governorate']   ?? '');
  $urgency       = sanitise($_POST['urgency']       ?? 'normal');
  $contact_phone = sanitise($_POST['contact_phone'] ?? '');
  $notes         = sanitise($_POST['notes']         ?? '');
  $errors = [];
  if (empty($patient_name))  $errors[] = 'Patient name is required.';
  if (empty($blood_type))    $errors[] = 'Blood type is required.';
  if (empty($hospital_name)) $errors[] = 'Hospital name is required.';
  if (empty($city))          $errors[] = 'City is required.';
  if (empty($contact_phone)) $errors[] = 'Contact phone is required.';
  if (empty($errors)) {
    $conn->beginTransaction();
    try {
      // 2. Insert blood request
      $stmt = $conn->prepare('INSERT INTO blood_requests'
        . ' (requester_user_id,patient_name,blood_type,units_needed,'
        . '  hospital_name,city,governorate,urgency,contact_phone,notes)'
        . ' VALUES (:rid,:pn,:bt,:un,:hn,:ci,:go,:ur,:cp,:no)');
      $stmt->execute([':rid'=>getUserId(), ':pn'=>$patient_name,
        ':bt'=>$blood_type, ':un'=>$units_needed, ':hn'=>$hospital_name,
        ':ci'=>$city, ':go'=>$governorate, ':ur'=>$urgency,
        ':cp'=>$contact_phone, ':no'=>$notes]);
      $reqId = $conn->lastInsertId();
 
      // 3. Find compatible donors
      $compatible = getCompatibleTypes($blood_type);
      $placeholders = implode(',', array_fill(0, count($compatible), '?'));
      $params = array_merge($compatible, [$city]);
      $donors = $conn->prepare("SELECT d.donor_id, u.email, p.first_name
        FROM donors d JOIN users u ON u.user_id=d.user_id
        JOIN profiles p ON p.user_id=d.user_id
        WHERE d.blood_type IN ($placeholders) AND d.is_available=TRUE
        AND u.status='active' AND p.city=?");
      $donors->execute($params);
      $matchedDonors = $donors->fetchAll();
 
      // 4. Notify each donor
      foreach ($matchedDonors as $donor) {
        $conn->prepare('INSERT INTO donor_notifications (request_id,donor_id) VALUES (:rid,:did)')
              ->execute([':rid'=>$reqId, ':did'=>$donor['donor_id']]);
        $subject = 'Urgent Blood Request --- '.$blood_type.' needed in '.$city;
        $message = 'Dear '.$donor['first_name'].', A patient urgently needs '
                 . $blood_type.' blood at '.$hospital_name.' in '.$city
                 . ' Contact: '.$contact_phone;
        @mail($donor['email'], $subject, $message, 'From: noreply@bloodfinder.com');
        $conn->prepare('UPDATE donor_notifications SET email_sent=TRUE, sent_at=NOW()'
          . ' WHERE request_id=:rid AND donor_id=:did')
          ->execute([':rid'=>$reqId, ':did'=>$donor['donor_id']]);
      }
      $conn->commit();
      redirect('history.php', 'Request submitted! '.count($matchedDonors).' donors notified.');
    } catch (PDOException $e) {
      $conn->rollBack();
      $errors[] = 'Failed to submit request. Please try again.';
    }
  }
}
?>
<?php
/* ... PHP logic block from Backend Phase 5 Feature 2 stays here unchanged ... */
?>

<?php
$pageTitle = 'Submit Blood Request | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h2 class="page-title mb-4">🏥 Submit Blood Request</h2>

      <!-- Error Messages -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          <ul class="mb-0">
            <?php foreach($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header bg-danger text-white fw-bold">
          Patient & Request Details
        </div>
        <div class="card-body p-4">
          <form method="POST" action="">

            <!-- Patient Name & Blood Type -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="patient_name" class="form-label required-star">
                  Patient Name</label>
                <input type="text" name="patient_name" id="patient_name"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['patient_name']??'') ?>">
              </div>
              <div class="col-md-3">
                <label for="blood_type" class="form-label required-star">
                  Blood Type Needed</label>
                <select name="blood_type" id="blood_type" class="form-select">
                  <option value="">--</option>
                  <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                    <option value="<?=$bt?>"
                      <?= (($_POST['blood_type']??'')===$bt)?'selected':'' ?>>
                      <?=$bt?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label for="units_needed" class="form-label required-star">
                  Units Needed</label>
                <input type="number" name="units_needed" id="units_needed"
                       class="form-control" min="1" max="10"
                       value="<?= (int)($_POST['units_needed']??1) ?>">
              </div>
            </div>

            <!-- Hospital & Urgency -->
            <div class="row g-3 mb-3">
              <div class="col-md-8">
                <label for="hospital_name" class="form-label required-star">
                  Hospital Name</label>
                <input type="text" name="hospital_name" id="hospital_name"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['hospital_name']??'') ?>">
              </div>
              <div class="col-md-4">
                <label for="urgency" class="form-label required-star">Urgency</label>
                <select name="urgency" id="urgency" class="form-select">
                  <option value="normal"  <?= (($_POST['urgency']??'normal')==='normal')  ?'selected':''?>>
                    Normal</option>
                  <option value="urgent"  <?= (($_POST['urgency']??'')==='urgent')  ?'selected':''?>>
                    ⚠ Urgent</option>
                  <option value="critical" <?= (($_POST['urgency']??'')==='critical') ?'selected':''?>>
                    🛑 Critical</option>
                </select>
              </div>
            </div>

            <!-- City / Governorate -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="city" class="form-label required-star">City</label>
                <input type="text" name="city" id="city" class="form-control"
                       value="<?= htmlspecialchars($_POST['city']??'') ?>">
              </div>
              <div class="col-md-6">
                <label for="governorate" class="form-label">Governorate</label>
                <input type="text" name="governorate" id="governorate"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['governorate']??'') ?>">
              </div>
            </div>

            <!-- Contact Phone -->
            <div class="mb-3">
              <label for="contact_phone" class="form-label required-star">
                Contact Phone</label>
              <input type="tel" name="contact_phone" id="contact_phone"
                     class="form-control"
                     value="<?= htmlspecialchars($_POST['contact_phone']??'') ?>"
                     placeholder="Number donors will call">
            </div>

            <!-- Notes -->
            <div class="mb-4">
              <label for="notes" class="form-label">Additional Notes</label>
              <textarea name="notes" id="notes" class="form-control" rows="3"
                        placeholder="Any additional details for the donor..."><?=
                htmlspecialchars($_POST['notes']??'') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
              <i class="bi bi-send"></i> Submit Request &amp; Notify Donors
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>