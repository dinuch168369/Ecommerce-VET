<?php
require_once __DIR__ . '/includes/user_handler.php';
session_start();
$user = getLoggedInUser();
if (!$user) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Facebook Live Comments</title>
</head>
<body>
    <h1>Hi, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
    <a href="start.php">Start Reading Live Comments</a><br>
    <a href="logout.php">Logout</a>
</body>
</html>