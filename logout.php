<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
echo "<script>window.location.href = 'index.php';</script>";
exit;
?>
