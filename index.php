<?php
require_once __DIR__ . '/includes/user_handler.php';
require_once __DIR__ . '/includes/facebook_helper.php';

session_start();
$user = getLoggedInUser();

if ($user) {
    header('Location: dashboard.php');
    exit;
}
    
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Email/password login
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $u = verifyUser($email, $password);
    if ($u) {
        loginUser($u);
        header('Location: dashboard.php');
        exit;
    } else {
        $message = "Invalid login credentials.";
    }
}

$loginUrl = getLoginUrl();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Facebook Live Comments</title>
</head>
<body>
    <h1>Login</h1>
    <?php if ($message) echo "<p style='color:red'>$message</p>"; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required /><br>
        <input type="password" name="password" placeholder="Password" required /><br>
        <button type="submit">Login</button>
    </form>
    <br>
    <a href="<?php echo htmlspecialchars($loginUrl); ?>">Login with Facebook</a>
</body>
</html>