<?php
session_start();

// 로그인 안 했으면 로그인 페이지로
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 글 저장하기
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. 사용자가 입력한 값 가져오기
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_SESSION['user_id'];
    $date = date('Y-m-d');
    
    // 2. 이미지 업로드 처리
    $image = '';  // 이미지 없으면 빈 값
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // 업로드된 파일이 있으면
        
        // uploads 폴더 없으면 만들기
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        // 파일 이름 만들기 (중복 방지: 시간 + 원래이름)
        $file_name = time() . '_' . $_FILES['image']['name'];
        
        // 파일 저장 경로
        $file_path = 'uploads/' . $file_name;
        
        // 파일 저장하기
        move_uploaded_file($_FILES['image']['tmp_name'], $file_path);
        
        // 저장된 경로 기억
        $image = $file_path;
    }
    
    // 3. 기존 글 목록 불러오기
    $posts = [];
    if (file_exists('posts.json')) {
        $posts = json_decode(file_get_contents('posts.json'), true);
    }
    
    // 4. 새 글 번호 만들기
    $new_id = 1;
    foreach ($posts as $post) {
        if ($post['id'] >= $new_id) {
            $new_id = $post['id'] + 1;
        }
    }
    
    // 5. 새 글 만들기
    $new_post = [
        'id' => $new_id,
        'category' => $category,
        'title' => $title,
        'author' => $author,
        'date' => $date,
        'views' => 0,
        'content' => $content,
        'image' => $image  // 이미지 경로 추가!
    ];
    
    // 6. 새 글을 맨 앞에 추가
    array_unshift($posts, $new_post);
    
    // 7. 파일에 저장
    file_put_contents('posts.json', json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    // 8. 게시판으로 이동
    header('Location: board.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>글쓰기 - Love All</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🎾 Love All</a></h1>
    </header>

    <div class="container">
        <div class="write-form">
            <h2>새 글 작성</h2>
            
            <!-- enctype 추가! 파일 업로드에 필요 -->
            <form method="POST" action="write.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>카테고리</label>
                    <select name="category" required>
                        <option value="">카테고리를 선택하세요</option>
                        <option value="자유">자유</option>
                        <option value="라켓">라켓</option>
                        <option value="코트">코트</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>제목</label>
                    <input type="text" name="title" maxlength="100" 
                           onkeyup="countText(this, 'titleCount', 100)" 
                           placeholder="제목을 입력하세요" required>
                    <span id="titleCount" class="text-count">0 / 100자</span>
                </div>
                
                <div class="form-group">
                    <label>내용</label>
                    <textarea name="content" maxlength="2000" 
                              onkeyup="countText(this, 'contentCount', 2000)"
                              oninput="countText(this, 'contentCount', 2000)"
                              placeholder="내용을 입력하세요" required></textarea>
                    <span id="contentCount" class="text-count">0 / 2000자</span>
                </div>
                
                <!-- 이미지 업로드 추가! -->
                <div class="form-group">
                    <label>📷 이미지 첨부 (선택)</label>
                    <input type="file" name="image" accept="image/*" class="file-input"
                           onchange="previewImage(this, 'imagePreview')">
                    <p class="file-help">jpg, png, gif 파일만 가능합니다.</p>
                    <div id="imagePreview" style="display:none;"></div>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-cancel" onclick="location.href='board.php'">취소</button>
                    <button type="submit" class="btn-submit">저장</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
