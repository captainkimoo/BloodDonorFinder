<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database(); $conn = $db->getConnection();

// Toggle user status (admin cannot deactivate themselves)
if (isset($_GET['toggle']) && isset($_GET['uid'])) {
    $targetId = (int)$_GET['uid'];
    if ($targetId !== getUserId()) {
        $curr = $conn->prepare('SELECT status FROM users WHERE user_id=:id');
        $curr->execute([':id'=>$targetId]);
        $newStatus = ($curr->fetchColumn() === 'active') ? 'inactive' : 'active';
        $conn->prepare('UPDATE users SET status=:s WHERE user_id=:id')
             ->execute([':s'=>$newStatus, ':id'=>$targetId]);
        redirect('users.php', 'User status updated.');
    }
}

// Search users (optional query string ?q=name)
$search = sanitise($_GET['q'] ?? '');

$sql = 'SELECT u.user_id, u.email, u.user_type, u.status, u.registration_date,'
     . ' p.first_name, p.last_name, p.city, d.blood_type, d.is_available'
     . ' FROM users u'
     . ' JOIN profiles p ON p.user_id = u.user_id'
     . ' LEFT JOIN donors d ON d.user_id = u.user_id';

if ($search) {
    $sql .= ' WHERE u.email LIKE :q OR p.first_name LIKE :q OR p.last_name LIKE :q';
}
$sql .= ' ORDER BY u.registration_date DESC';

$stmt = $conn->prepare($sql);
if ($search) $stmt->execute([':q' => '%'.$search.'%']);
else         $stmt->execute();

$users = $stmt->fetchAll();

?>