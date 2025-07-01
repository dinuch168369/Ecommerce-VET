<?php
function db() {
    $config = require __DIR__ . '/../config/config.php';
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    return new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

function getUserByEmail($email) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByFbId($fb_user_id) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE fb_user_id = ?");
    $stmt->execute([$fb_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function registerUser($email, $first_name, $last_name, $password = null, $fb_user_id = null, $fb_access_token = null) {
    $pdo = db();
    $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : '';
    $stmt = $pdo->prepare("INSERT INTO tbl_users (email, first_name, last_name, password, fb_user_id, fb_access_token) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $first_name, $last_name, $hash, $fb_user_id, $fb_access_token]);
    return $pdo->lastInsertId();
}

function verifyUser($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function loginUser($user) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user'] = $user;
}

function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_destroy();
}

function getLoggedInUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}
?>