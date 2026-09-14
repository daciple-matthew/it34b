<?php

require '../../config/config.php';
require '../../config/functions.php';


requireRole('admin');

// logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_email'], 'view_activity_logs', 'success');

// Activity Logs Query Query #3

$stmt = $pdo->query("
    SELECT * FROM activity_logs ORDER BY activity_log_created_at DESC
");


$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);






?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>

<body>
    <h1>Welcome Admin</h1>
    <a href="../../auth/signout.php">Sign Out</a>
    <table border="1">
        <thead>
            <tr>

                <th>Record ID</th>
                <th>User ID</th>
                <th>User Email</th>
                <th>Action</th>
                <th>Status</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= htmlspecialchars($activity['activity_log_id']); ?></td>
                    <td><?= htmlspecialchars($activity['user_id']); ?></td>
                    <td><?= htmlspecialchars($activity['user_email']); ?></td>
                    <td><?= htmlspecialchars($activity['activity_log_action']); ?></td>
                    <td><?= htmlspecialchars($activity['activity_log_status']); ?></td>
                    <td><?= htmlspecialchars($activity['activity_log_ip_address']); ?></td>
                    <td><?= htmlspecialchars($activity['activity_log_user_agent']); ?></td>
                    <td><?= htmlspecialchars($activity['activity_log_created_at']); ?></td>
                </tr>
            <?php endforeach; ?>

    </table>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/3.0.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/3.0.4/js/dataTables.bootstrap5.min.js"></script>
</html>



In addition to the above code, the following Javascript library files are loaded for use in this example:

https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.8/js/bootstrap.bundle.min.js
https://cdn.datatables.net/3.0.4/js/dataTables.min.js
https://cdn.datatables.net/3.0.4/js/dataTables.bootstrap5.min.js