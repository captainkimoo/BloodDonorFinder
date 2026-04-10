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
