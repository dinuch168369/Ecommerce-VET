<?php
    require_once __DIR__ . '/facebook_helper.php';
    require_once __DIR__ . '/user_handler.php';

//fetches comments from a Facebook Live Video using a Page access token    
function fetchLiveComments($liveVideoId, $accessToken) {
    $fb = getFacebookClient();
    try {
        $response = $fb->get(               
            "/$liveVideoId/comments?order=reverse_chronological&live_filter=no_filter",
            $accessToken
        );
        return $response->getGraphEdge()->asArray();
    } catch(Exception $e) {
        return ['error' => 'Error: ' . $e->getMessage()];
    }
}

//save comment to the database, updating if it already exists
function saveCommentToDb($user_id, $video_id, $comment) {
    $pdo = db();

    // Handle DateTime or string for created_time
    $createdTime = null;
    if (isset($comment['created_time'])) {
        if ($comment['created_time'] instanceof DateTime) {
            $createdTime = $comment['created_time']->format('Y-m-d H:i:s');
        } else {
            $createdTime = date('Y-m-d H:i:s', strtotime($comment['created_time']));
        }
    }

    $stmt = $pdo->prepare('INSERT INTO comments (user_id, video_id, comment_id, message, from_name, created_time) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE message=VALUES(message), from_name=VALUES(from_name), created_time=VALUES(created_time)');
    $stmt->execute([
        $user_id,
        $video_id,
        $comment['id'],
        $comment['message'] ?? '',
        $comment['from']['name'] ?? '',
        $createdTime
    ]);
}

//store all fetched comments
function storeComments($user_id, $video_id, $comments) {
    foreach ($comments as $comment) {
        saveCommentToDb($user_id, $video_id, $comment);
    }
}
?>