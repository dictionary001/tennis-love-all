<?php
session_start();

// 1. 글 번호 받기
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. 글 목록 불러오기
$posts = [];
if (file_exists('posts.json')) {
    $posts = json_decode(file_get_contents('posts.json'), true);
}

// 3. 해당 글 찾기
$post = null;
$post_index = -1;

for ($i = 0; $i < count($posts); $i++) {
    if ($posts[$i]['id'] == $id) {
        $post = $posts[$i];
        $post_index = $i;
        break;
    }
}

// 4. 글 없으면 게시판으로
if ($post == null) {
    header('Location: board.php');
    exit;
}

// 5. 조회수 증가
$posts[$post_index]['views'] = $posts[$post_index]['views'] + 1;
$post['views'] = $posts[$post_index]['views'];
file_put_contents('posts.json', json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 6. 댓글 저장하기
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $comment_content = $_POST['comment'];
    $comment_author = $_SESSION['user_id'];
    $comment_date = date('Y-m-d');
    
    // 댓글 불러오기
    $comments = [];
    if (file_exists('comments.json')) {
        $comments = json_decode(file_get_contents('comments.json'), true);
    }
    
    // 새 댓글 번호 만들기
    $new_id = 1;
    foreach ($comments as $c) {
        if ($c['id'] >= $new_id) {
            $new_id = $c['id'] + 1;
        }
    }
    
    // 새 댓글 만들기
    $new_comment = [
        'id' => $new_id,
        'post_id' => $id,
        'author' => $comment_author,
        'content' => $comment_content,
        'date' => $comment_date
    ];
    
    // 댓글 추가하고 저장
    $comments[] = $new_comment;
    file_put_contents('comments.json', json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    // 새로고침 (댓글 보이게)
    header('Location: view.php?id=' . $id);
    exit;
}

// 7. 댓글 불러오기
$comments = [];
if (file_exists('comments.json')) {
    $all_comments = json_decode(file_get_contents('comments.json'), true);
    
    foreach ($all_comments as $c) {
        if ($c['post_id'] == $id) {
            $comments[] = $c;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['title']; ?> - Love All</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🎾 Love All</a></h1>
    </header>

    <div class="container">
        <!-- 글 내용 -->
        <div class="post-view">
            <h2><?php echo $post['title']; ?></h2>
            
            <div class="post-info">
                <span>📁 <?php echo $post['category']; ?></span>
                <span>👤 <?php echo $post['author']; ?></span>
                <span>📅 <?php echo $post['date']; ?></span>
                <span>👁 <?php echo $post['views']; ?></span>
            </div>
            
            <div class="post-content">
                <?php echo nl2br($post['content']); ?>
                
                <!-- 이미지가 있으면 보여주기 -->
                <?php if (!empty($post['image'])): ?>
                    <div class="post-image">
                        <img src="<?php echo $post['image']; ?>" alt="첨부 이미지">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="post-buttons">
                <a href="board.php">← 목록으로</a>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author']): ?>
                    <a href="edit.php?id=<?php echo $post['id']; ?>">✏️ 수정</a>
                    <button onclick="showDeleteModal('delete_post.php?id=<?php echo $post['id']; ?>')" 
                            class="btn-delete">🗑️ 삭제</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 댓글 영역 -->
        <div class="comment-section">
            <h3>💬 댓글 (<?php echo count($comments); ?>개)</h3>
            
            <div class="comment-list">
                <?php if (count($comments) == 0): ?>
                    <p class="no-comment">아직 댓글이 없습니다. 첫 댓글을 남겨보세요!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">👤 <?php echo $comment['author']; ?></span>
                            <span class="comment-date"><?php echo $comment['date']; ?></span>
                            
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['author']): ?>
                                <button onclick="showDeleteModal('delete_comment.php?comment_id=<?php echo $comment['id']; ?>&post_id=<?php echo $id; ?>')"
                                        class="comment-delete">삭제</button>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br($comment['content']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <form class="comment-form" method="POST" action="view.php?id=<?php echo $id; ?>">
                <textarea name="comment" maxlength="200" 
                          onkeyup="countText(this, 'commentCount', 200)" 
                          placeholder="댓글을 입력하세요" required></textarea>
                <span id="commentCount" class="text-count">0 / 200자</span>
                <button type="submit">댓글 작성</button>
            </form>
            <?php else: ?>
            <p class="login-notice">댓글을 작성하려면 <a href="login.php">로그인</a>하세요.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 삭제 확인 모달 -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>🎾 정말 삭제하시겠습니까?</h3>
            <p>삭제하면 복구할 수 없습니다.</p>
            <div class="modal-buttons">
                <button onclick="confirmDelete()" class="btn-confirm">삭제</button>
                <button onclick="closeDeleteModal()" class="btn-cancel">취소</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
