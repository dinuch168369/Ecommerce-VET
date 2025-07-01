<?php
require_once __DIR__ . '/includes/user_handler.php';
require_once __DIR__ . '/includes/facebook_helper.php';
require_once __DIR__ . '/includes/comment_handler.php';

session_start();
$user = getLoggedInUser();
if (!$user) {
    header('Location: index.php');
    exit;
}

$message = "";
$createdStream = null;
$pageId = '507886533079954'; // Set this to your actual Page ID

// Check for Live Video Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userToken = $user['fb_access_token'] ?? getAccessTokenFromSession();
    $pageToken = getPageAccessToken($userToken, $pageId);

    if (!$pageToken) {
        $message = "Failed to retrieve Page Access Token.";
    } elseif ($action === 'create') {
        $title = $_POST['title'] ?? 'Live from API';
        $desc = $_POST['description'] ?? '';
        $createdStream = createLiveVideo($pageId, $pageToken, $title, $desc);
        if (isset($createdStream['id'])) {
            $message = "Live video created successfully (ID: {$createdStream['id']})";
        } else {
            $message = "Failed to create Live Video: " . json_encode($createdStream);
        }
    } elseif ($action === 'fetch') {
        $live_video_id = $_POST['live_video_id'] ?? '';
        if ($live_video_id) {
            $comments = fetchLiveComments($live_video_id, $pageToken);
            if (is_array($comments) && isset($comments['error'])) {
        // $comments is an array and has 'error'
        $message = "Error: " . (is_array($comments['error']) && isset($comments['error']['message']) ? $comments['error']['message'] : $comments['error']);
    } elseif (is_string($comments)) {
        // $comments is a string error message
        $message = "Error: " . $comments;
    } else {
        // $comments is an array of comments
        storeComments($user['id'], $live_video_id, $comments);
        $message = "Fetched and stored " . count($comments) . " comments.";
    }
        } else {
            $message = "Please provide a Live Video ID.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fetch Facebook Live Comments</title>
</head>
<body>
    <h1>Facebook Live Tool</h1>

    <!-- Create Live Video -->
    <form method="post">
        <h3>Create New Live Video</h3>
        <input type="hidden" name="action" value="create">
        <label>Title:</label><br>
        <input type="text" name="title" required><br><br>
        <label>Description:</label><br>
        <input type="text" name="description"><br><br>
        <button type="submit">Create Live Video</button>
    </form>

    <?php
    if ($createdStream && isset($createdStream['stream_url'])) {
        echo "<p><strong>Stream URL:</strong> {$createdStream['stream_url']}</p>";
        echo "<p><strong>Secure URL:</strong> {$createdStream['secure_stream_url']}</p>";
    }
    ?>
    <hr>

    <!-- Fetch Comments -->
    <form method="post">
        <h3>Fetch Comments</h3>
        <input type="hidden" name="action" value="fetch">
        <label for="live_video_id">Live Video ID:</label><br>
        <input type="text" name="live_video_id" id="live_video_id" required><br><br>
        <button type="submit">Fetch Comments</button>
    </form>

    <?php if ($message) echo "<p><strong>$message</strong></p>"; ?>

    <br>
    <a href="dashboard.php">← Back to Dashboard</a>
</body>
</html>