<?php
// start user session
function startUserSession($pdo)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
    INSERT INTO user_sessions(
    user_id,
    session_start
    )
    VALUES (
    :user_id,
    NOW()
    )
    ");

    $stmt->execute([
        'user_id' => $user_id
    ]);

    return $pdo->lastInsertId();
}


// end user session

function endUserSession($pdo)
{
    if (!isset($_SESSION['session_id'])) {
        return false;
    }

    $session_id = $_SESSION['session_id'];

    $stmt = $pdo->prepare("
        UPDATE user_sessions
        SET
            session_end = NOW(),
            session_duration = TIMESTAMPDIFF(
                SECOND,
                session_start,
                NOW()
            )
        WHERE session_id = :session_id
    ");

    return $stmt->execute([
        'session_id' => $session_id
    ]);
}
// check if user is already logged in
function hasActiveUserSession($pdo,$user_id) {

$stmt= $pdo->prepare("
SELECT session_id
FROM user_sessions
WHERE user_id = :user_id
AND session_end IS NULL
LIMIT 1
");

$stmt->execute([
    'user_id' => $user_id
]);

return (bool) $stmt->fetchColumn();
}

function checkSessionTimeout() {
    global $pdo;
    // timeout variable
    // 10 second for testing
    // 1800s = 30 mins
    $timeout = 1800;

    if(isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];

        if($inactive>=$timeout) {
            logActivity(
                $pdo,
                $_SESSION['user_id'],
                $_SESSION['user_email'] ?? null,
                'logout-session-timeout',
                'success'
            );
        }
        // end database session
        endUserSession($pdo);

        // destroy php session
        session_unset();
        session_destroy();

        // redirect
        header(
            'Location: ' . BASE_URL . '/index.php?timeout=1'
        );
    }

    // update last activity
    $_SESSION['last_activity'] = time();
}


?>