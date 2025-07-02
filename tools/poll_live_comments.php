<?php
require_once __DIR__ . '/../includes/facebook_helper.php';
require_once __DIR__ . '/../includes/user_handler.php';
require_once __DIR__ . '/../includes/comment_handler.php';

// Set these before running, or make them script arguments
$user_id = 1; //get from your users table

$live_video_id = '4137130903171862'; //← Set This: your_real_facebook_live_video_id  1060802089356012 / 1060036896270200 / 4137130903171862

$accessToken = 'EAAItf6x5tvABOx15mTW7QBe9ZA8qlgD6LLGSHPDBGxAKh0iDpfyInPedeZABJ1SpdpoBbKT8Xi6SD3SYi7g8WQSxWQ3It8TpLZCYmNOKitZCeZCZCbZCfZCXhmO2zH0pg0PkrmPGkbUH196xQwxtcfvWkxu7t22Qc8QrYkGL3RJsyApPlZBQkbnacoPeQiyjlZCYBum0T12kr5'; //← Set This: your_long_lived_page_access_token

if (!$live_video_id || !$accessToken) {
    die("You must set both \$live_video_id and \$accessToken!\n");
}

$seen_comments = [];

echo "Polling comments for Live Video ID: $live_video_id\n";
while (true) {
    $comments = fetchLiveComments($live_video_id, $accessToken);

    if (isset($comments['error'])) {
        echo $comments['error'] . "\n";
    } elseif (is_array($comments)) {
        foreach ($comments as $comment) {
            if (!in_array($comment['id'], $seen_comments)) {
                // Save only if not already seen (optional: check DB as well)
                saveCommentToDb($user_id, $live_video_id, $comment);
                $seen_comments[] = $comment['id'];
                echo "Saved comment: {$comment['id']} - {$comment['message']}\n";
            }
        }
    } else {
        echo "Unexpected response from fetchLiveComments\n";
    }
    sleep(10);// Poll every 10 seconds
}