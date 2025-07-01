<?php
require_once __DIR__ . '/includes/facebook_helper.php';
require_once __DIR__ . '/includes/user_handler.php';

session_start();

$fb = getFacebookClient();
$helper = $fb->getRedirectLoginHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}

if (!$accessToken) {
    echo 'No OAuth data could be obtained from the callback request.';
    exit;
}
setAccessTokenToSession($accessToken);

// Get Facebook user profile
$user = getFacebookUserProfile($accessToken);
if (!$user) {
    echo "Could not get Facebook user";
    exit;
}

$fb_user_id = $user->getId();
$email = $user->getEmail();
$first_name = $user->getFirstName();
$last_name = $user->getLastName();

$db_user = getUserByFbId($fb_user_id);
if (!$db_user) {
    // Register new user
    $new_id = registerUser($email, $first_name, $last_name, null, $fb_user_id, $accessToken);
    $db_user = getUserByFbId($fb_user_id);
}

loginUser($db_user);
header('Location: dashboard.php');
exit;
?>