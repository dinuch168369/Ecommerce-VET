<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getFacebookClient() {
    $config = require __DIR__ . '/../config/config.php';
    return new \Facebook\Facebook([
        'app_id' => $config['app_id'],
        'app_secret' => $config['app_secret'],
        'default_graph_version' => $config['default_graph_version'],
    ]);
}

function getLoginUrl() {
    $fb = getFacebookClient();
    $helper = $fb->getRedirectLoginHelper();
    $permissions = [
        'email', 
        'public_profile', 
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_metadata',
        'pages_manage_posts',
        'pages_read_user_content',
    ];

    $config = require __DIR__ . '/../config/config.php';
    return $helper->getLoginUrl($config['callback_url'], $permissions);
}

function getAccessTokenFromSession() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['fb_access_token'] ?? null;
}

function setAccessTokenToSession($accessToken) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['fb_access_token'] = (string)$accessToken;
}

function getFacebookUserProfile($accessToken) {
    $fb = getFacebookClient();
    try {
        $response = $fb->get('/me?fields=id,first_name,last_name,email', $accessToken);
        return $response->getGraphUser();
    } catch (Exception $e) {
        return null;
    }
}

// NEW: Convert user token to page token
function getPageAccessToken($userAccessToken, $pageId) {
    $url = "https://graph.facebook.com/v23.0/me/accounts?access_token=$userAccessToken";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    foreach ($data['data'] as $page) {
        if ($page['id'] === $pageId) {
            return $page['access_token'];
        }
    }
    return null;
}

// NEW: Create live video using app (to avoid permission error)
function createLiveVideo($pageId, $pageToken, $title, $desc) {
    $fb = getFacebookClient();
    try {
        $response = $fb->post(
            "/$pageId/live_videos",
            [
                'title' => $title,
                'description' => $desc,
                'status' => 'LIVE_NOW'
            ],
            $pageToken
        );
        return $response->getDecodedBody();
    } catch(Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// function createLiveVideo($pageId, $pageAccessToken, $title = 'API Live', $description = '') {
//     $url = "https://graph.facebook.com/v23.0/{$pageId}/live_videos";

//     $params = [
//         'access_token' => $pageAccessToken,
//         'title' => $title,
//         'description' => $description,
//         'status' => 'LIVE_NOW'
//     ];

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
//     $response = curl_exec($ch);
//     curl_close($ch);

//     return json_decode($response, true);
// }
?>