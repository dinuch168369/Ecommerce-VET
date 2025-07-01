<?php 
   require_once __DIR__ . '/includes/user_handler.php';
    logoutUser();
    header('Location: index.php');
    exit;
?>