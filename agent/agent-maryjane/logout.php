<?php
// Start PHP session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the remember me cookie if it exists
if (isset($_COOKIE['agent_remember'])) {
    setcookie('agent_remember', '', time() - 3600, '/');
}

// Redirect to login page
header("Location: ../agent-login.php");
exit();
?> 