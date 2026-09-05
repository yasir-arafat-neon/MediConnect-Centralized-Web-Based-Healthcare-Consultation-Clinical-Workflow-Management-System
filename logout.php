<?php
/**
 * Common Authentication - Logout Endpoint
 * Location: C:\xampp\htdocs\mediconnect_db\auth\logout.php
 */
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: ../index.php');
exit;
?>