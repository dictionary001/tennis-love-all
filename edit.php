<?php
session_start();

// 1. 로그인 안 했으면 로그인 페이지로
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. 수정할 글 번호 받기
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. 글 목록 불러오기
$posts = [];
if (file_exists('posts.json')) {
    $posts = json_decode(file_get_contents('posts.json'), true);
}

// 4. 해당 글 찾기
$post = null;
$post_index = -1;

for ($i = 0; $i < count($posts); $i++) {
    if ($posts[$i]['id'] == $id) {
        $post = $posts[$i];
        $post_index = $i;
        break;
    }
}

// 5. 글 없으면 게시판으로
if ($post == null) {
    header('Location: board.php');
    exit;
}

// 6. 내 글이 아니면 게시판으로
if ($post['author'] != $_SESSION['user_id']) {
    header('Location: board.php');
    exit;
}

// 7. 수정 저장하기 (POST 요청일 때만)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 입력값 받기
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // 이미지 처리
    $image = $post['image'];  // 기존 이미지 유지
    
    // 이미지 삭제 체크했으면
    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        // 기존 이미지 파일 삭제
        if (!empty($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }
        $image = '';  // 이미지 경로 비우기
    }
    
    // 새 이미지 업로드했으면
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        
        // uploads 폴더 없으면 만들기
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // 기존 이미지 있으면 삭제
        if (!empty($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }
        
        // 파일 이름 만들기
        $file_name = time() . '_' . $_FILES['image']['name'];
        $file_path = 'uploads/' . $file_name;
        
        // 파일 저장
        move_uploaded_file($_FILES['image']['tmp_name'], $file_path);
        
        // 새 이미지 경로로 변경
        $image = $file_path;
    }
    
    // 글 수정하기
    $posts[$post_index]['category'] = $category;
    $posts[$post_index]['title'] = $title;
    $posts[$post_index]['content'] = $content;
    $posts[$post_index]['image'] = $image;
    
    // 파일에 저장
    file_put_contents('posts.json', json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    // 글 보기 페이지로 이동
    header('Location: view.php?id=' . $id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>글 수정 - Love All</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🎾 Love All</a></h1>
    </header>

    <div class="container">
        <div class="write-form">
            <h2>글 수정</h2>
            
            <form method="POST" action="edit.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label>카테고리</label>
                    <select name="category" required>
                        <option value="자유" <?php echo $post['category'] == '자유' ? 'selected' : ''; ?>>자유</option>
                        <option value="라켓" <?php echo $post['category'] == '라켓' ? 'selected' : ''; ?>>라켓</option>
                        <option value="코트" <?php echo $post['category'] == '코트' ? 'selected' : ''; ?>>코트</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>제목</label>
                    <input type="text" name="title" value="<?php echo $post['title']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>내용</label>
                    <textarea name="content" placeholder="내용을 입력하세요" required
                            onkeyup="countText(this, 'contentCounter', 2000)"></textarea>
                    <div id="contentCounter" class="text-counter">0 / 2000자</div>
                </div>
                
                <div class="form-group">
                    <label>📷 이미지</label>
                    
                    <?php if (!empty($post['image'])): ?>
                        <div style="margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%); border-radius: 12px; border: 1px solid #ddd;">
                            <p style="color: #333; margin-bottom: 15px; font-weight: bold; font-size: 14px;">📌 현재 첨부된 이미지</p>
                            
                            <div style="background: white; padding: 10px; border-radius: 8px; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <img src="<?php echo $post['image']; ?>" style="max-width: 250px; border-radius: 5px; display: block;">
                            </div>
                            
                            <div style="margin-top: 15px; padding: 12px 15px; background: #fff5f5; border: 1px solid #ffcccc; border-radius: 8px;">
                                <label style="display: flex; align-items: center; cursor: pointer; user-select: none;">
                                    <input type="checkbox" name="delete_image" value="1" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                    <span style="color: #e74c3c; font-weight: 500;">🗑️ 이 이미지 삭제하기</span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div style="padding: 15px; background: #f8f9fa; border: 2px dashed #ccc; border-radius: 8px; text-align: center;">
                        <p style="margin-bottom: 10px; color: #666; font-size: 14px;">📤 새 이미지 업로드</p>
                        <input type="file" name="image" accept="image/*" class="file-input">
                        <p style="margin-top: 10px; color: #999; font-size: 12px;">새 이미지를 선택하면 기존 이미지가 교체됩니다.</p>
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-cancel" onclick="location.href='view.php?id=<?php echo $id; ?>'">취소</button>
                    <button type="submit" class="btn-submit">수정 완료</button>
                </div>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
