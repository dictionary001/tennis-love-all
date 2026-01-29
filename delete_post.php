<?php
session_start();

// 로그인 안 했으면 돌아가기
if (!isset($_SESSION['user_id'])) {
    header('Location: board.php');
    exit;
}

// 삭제할 글 번호 가져오기
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 글 목록 불러오기
$posts = [];
if (file_exists('posts.json')) {
    $posts = json_decode(file_get_contents('posts.json'), true);
}

// 새 글 목록 만들기 (삭제할 글 빼고)
$new_posts = [];
foreach ($posts as $post) {
    // 내가 쓴 글만 삭제 가능
    if ($post['id'] == $id && $post['author'] == $_SESSION['user_id']) {
        // 이 글은 삭제 (추가 안 함)
        continue;
    }
    $new_posts[] = $post;
}

// 파일에 저장
file_put_contents('posts.json', json_encode($new_posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 게시판으로 이동
header('Location: board.php');
exit;
?>
