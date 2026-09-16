
<?php

require '../../config/config.php';
require '../../config/functions.php';

requireRole('admin');

// Get activity logs
$stmt = $pdo->query("
    SELECT *
    FROM activity_logs
    ORDER BY activity_log_created_at DESC
");

$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Panel</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-container">

        <!-- Header -->
        <div class="admin-header">

            <div>
                <h1>Welcome Admin</h1>
                <p>Activity Logs</p>
            </div>

            <a href="../../auth/signout.php" class="signout-btn">
                Sign Out
            </a>

        </div>


        <!-- Activity Logs Table -->
        <div class="table-container">

            <table class="activity-table">

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

                    <?php if (empty($activities)): ?>

                        <tr>
                            <td colspan="8" class="no-data">
                                No activity logs found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($activities as $activity): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($activity['activity_log_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['user_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['user_email']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['activity_log_action']) ?>
                                </td>

                                <td>
                                    <?php
                                    $status = strtolower($activity['activity_log_status']);
                                    ?>

                                    <span class="status <?= htmlspecialchars($status) ?>">
                                        <?= htmlspecialchars($activity['activity_log_status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['activity_log_ip_address']) ?>
                                </td>

                                <td class="user-agent">
                                    <?= htmlspecialchars($activity['activity_log_user_agent']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['activity_log_created_at']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</body>

</html>

