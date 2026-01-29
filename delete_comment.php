<?php
session_start();

// 로그인 안 했으면 돌아가기
if (!isset($_SESSION['user_id'])) {
    header('Location: board.php');
    exit;
}

// 삭제할 댓글 번호, 글 번호 가져오기
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

// 댓글 목록 불러오기
$comments = [];
if (file_exists('comments.json')) {
    $comments = json_decode(file_get_contents('comments.json'), true);
}

// 새 댓글 목록 만들기 (삭제할 댓글 빼고)
$new_comments = [];
foreach ($comments as $comment) {
    // 내가 쓴 댓글만 삭제 가능
    if ($comment['id'] == $comment_id && $comment['author'] == $_SESSION['user_id']) {
        // 이 댓글은 삭제 (추가 안 함)
        continue;
    }
    $new_comments[] = $comment;
}

// 파일에 저장
file_put_contents('comments.json', json_encode($new_comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 원래 글로 돌아가기
header('Location: view.php?id=' . $post_id);
exit;
?>
