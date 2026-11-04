<?php

require '../../config/config.php';

requireRole('admin');

// Determine which section to display
$section = $_GET['section'] ?? 'activity-logs';

// determine crud operation
$action = $_GET['action'] ?? '';

// Fetch activity logs
$activities = [];

if ($section !== 'session') {
    $stmt = $pdo->query("
        SELECT *
        FROM activity_logs
        ORDER BY activity_log_created_at DESC
    ");

    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user sessions
$sessions = [];

if ($section === 'session') {
    $stmt = $pdo->query("
        SELECT 
        us.session_id,
        us.user_id,
        u.user_username,
        u.user_email,
        u.user_role,
        us.session_start,
        us.session_end,
        us.session_duration

        FROM user_sessions us
        INNER JOIN users u
        ON us.user_id = u.user_id
        ORDER BY us.session_start DESC
    ");

    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($action === 'export') {

    if ($section === 'activity-logs') {

        $stmt = $pdo->query("
            SELECT *
            FROM activity_logs
            ORDER BY activity_log_created_at DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        logActivity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            'export_activity_logs',
            'success'
        );

        exportCsv(
            'activity_logs',
            [
                'Record ID', 'User ID', 'User Email', 'Action',
                'Status', 'IP Address', 'User Agent', 'Date & Time',
            ],
            $rows
        );
    }

}

if ($section === 'user-sessions') {

        $stmt = $pdo->query("
            SELECT
                us.session_id,
                us.user_id,
                u.user_username,
                u.user_email,
                u.user_role,
                us.session_start,
                us.session_end,
                us.session_duration
            FROM user_sessions us
            INNER JOIN users u ON us.user_id = u.user_id
            ORDER BY us.session_start DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['session_end'] = $r['session_end'] ?: 'Active';
            if ($r['session_duration'] !== null) {
                $s       = (int) $r['session_duration'];
                $hours   = floor($s / 3600);
                $minutes = floor(($s % 3600) / 60);
                $seconds = $s % 60;
                $r['session_duration'] = sprintf(
                    '%02d hr %02d min %02d sec',
                    $hours, $minutes, $seconds
                );
            } else {
                $r['session_duration'] = 'Active';
            }
        }
        unset($r);

        logActivity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            'export_user_sessions',
            'success'
        );

        exportCsv(
            'user_sessions',
            [
                'Session ID', 'User ID', 'Username', 'Email',
                'Role', 'Session Start', 'Session End', 'Duration',
            ],
            $rows
        );
    }


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../app/admin/admin.css?v=2">

    <title>Admin Panel</title>
</head>

<body>

<div class="admin-container">

    <!-- Header -->
    <div class="admin-header">

        <div>
            <h1>Welcome Admin</h1> 

              <form method="POST" action="../../auth/signout.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>" >
        <button type="submit"> Sign Out </button>
    </form>

            <?php if ($section === 'session'): ?>
                <h2>User Sessions</h2>
                <a href="index.php?section=user-sessions&amp;action=export">Export CSV</a>

            <?php else: ?>
                <h2>Activity Logs</h2>
                <a href="index.php?section=activity-logs&amp;action=export">Export CSV</a>
            <?php endif; ?>
        </div>

        <nav>
            <a href="index.php">Activity Logs</a>
            <a href="index.php?section=session">User Sessions</a>
        </nav>


    </div>

    <!-- Activity Logs -->
    <?php if ($section !== 'session'): ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>User ID</th>
                        <th>User Email</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($activities)): ?>

                        <tr>
                            <td colspan="6" class="no-data">
                                No activity logs found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($activities as $activity): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) $activity['activity_log_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) ($activity['user_id'] ?? 'N/A')) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) ($activity['user_email'] ?? 'N/A')) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $activity['activity_log_action']) ?>
                                </td>

                                <td>
                                    <?php
                                    $status = strtolower(
                                        (string) ($activity['activity_log_status'] ?? '')
                                    );
                                    ?>

                                    <span class="status <?= htmlspecialchars($status) ?>">
                                        <?= htmlspecialchars(
                                            (string) ($activity['activity_log_status'] ?? 'Unknown')
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $activity['activity_log_created_at']) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    <!-- User Sessions -->
    <?php else: ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Session Start</th>
                        <th>Session End</th>
                        <th>Duration</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($sessions)): ?>

                        <tr>
                            <td colspan="8" class="no-data">
                                No user sessions found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($sessions as $session): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars((string) $session['session_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $session['user_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) ($session['user_username'] ?? 'N/A')) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) ($session['user_email'] ?? 'N/A')) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) ($session['user_role'] ?? 'N/A')) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars((string) $session['session_start']) ?>
                                </td>

                                <td>
                                    <?= !empty($session['session_end'])
                                        ? htmlspecialchars((string) $session['session_end'])
                                        : 'Active'
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    if ($session['session_duration'] !== null) {

                                        $duration = max(
                                            0,
                                            (int) $session['session_duration']
                                        );

                                        $hours = intdiv($duration, 3600);
                                        $minutes = intdiv($duration % 3600, 60);
                                        $seconds = $duration % 60;

                                        echo sprintf(
                                            '%02d hr %02d min %02d sec',
                                            $hours,
                                            $minutes,
                                            $seconds
                                        );

                                    } else {
                                        echo 'Active';
                                    }
                                    ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>
</html>