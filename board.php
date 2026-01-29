<?php
session_start();

// posts.json 파일에서 글 목록 불러오기
$posts = [];
if (file_exists('posts.json')) {
    $posts = json_decode(file_get_contents('posts.json'), true);
}

// 카테고리 필터
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';

// 검색 값 받기
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'title';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 페이지 번호 받기
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$page = (int)$page;
if ($page < 1) {
    $page = 1;
}

// 한 페이지에 보여줄 글 수
$per_page = 10;

// 필터링된 글 목록 만들기
$filtered_posts = [];

foreach ($posts as $post) {
    // 카테고리 필터링
    if ($cat != '' && $post['category'] != $cat) {
        continue;
    }
    
    // 검색 필터링
    if ($keyword != '') {
        // 제목으로 검색
        if ($search_type == 'title') {
            if (strpos($post['title'], $keyword) === false) {
                continue;
            }
        }
        // 작성자로 검색
        if ($search_type == 'author') {
            if (strpos($post['author'], $keyword) === false) {
                continue;
            }
        }
    }
    
    // 필터 통과한 글만 추가
    $filtered_posts[] = $post;
}

// 전체 글 수
$total_posts = count($filtered_posts);

// 전체 페이지 수 계산
$total_pages = ceil($total_posts / $per_page);
if ($total_pages < 1) {
    $total_pages = 1;
}

// 페이지가 범위 벗어나면 조정
if ($page > $total_pages) {
    $page = $total_pages;
}

// 현재 페이지에 보여줄 글만 자르기
$start = ($page - 1) * $per_page;
$current_posts = array_slice($filtered_posts, $start, $per_page);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Love All - 커뮤니티</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">-------Love All</a></h1>
    </header>

    <div class="nav-bar">
        <div class="user-info">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span><?php echo $_SESSION['user_id']; ?></span>님 환영합니다
                <a href="logout.php">로그아웃</a>
            <?php else: ?>
                <a href="login.php">로그인</a>
                <a href="register.php">회원가입</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- 카테고리 탭 -->
        <div class="category-tabs">
            <a href="board.php" class="<?php echo $cat == '' ? 'active' : ''; ?>">전체</a>
            <a href="board.php?cat=자유" class="<?php echo $cat == '자유' ? 'active' : ''; ?>">자유</a>
            <a href="board.php?cat=라켓" class="<?php echo $cat == '라켓' ? 'active' : ''; ?>">라켓</a>
            <a href="board.php?cat=코트" class="<?php echo $cat == '코트' ? 'active' : ''; ?>">코트</a>
        </div>

        <!-- 검색 폼 -->
        <form class="search-box" method="GET" action="board.php">
            <select name="search_type">
                <option value="title" <?php echo $search_type == 'title' ? 'selected' : ''; ?>>제목</option>
                <option value="author" <?php echo $search_type == 'author' ? 'selected' : ''; ?>>작성자</option>
            </select>
            <input type="text" name="keyword" placeholder="검색어를 입력하세요" value="<?php echo $keyword; ?>">
            <button type="submit">검색</button>
        </form>

        <!-- 검색 결과 표시 -->
        <?php if ($keyword != ''): ?>
            <p style="margin-bottom: 20px; color: #666;">
                🔍 "<?php echo $keyword; ?>" 검색 결과 (<?php echo $total_posts; ?>건)
            </p>
        <?php endif; ?>

        <!-- 글쓰기 버튼 -->
        <div class="top-bar">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="write.php" class="write-btn">✏️ 새 글 작성</a>
            <?php endif; ?>
        </div>

        <!-- 게시판 테이블 -->
        <table class="board-table">
            <thead>
                <tr>
                    <th style="width: 60px;">번호</th>
                    <th style="width: 80px;">카테고리</th>
                    <th>제목</th>
                    <th style="width: 100px;">작성자</th>
                    <th style="width: 100px;">작성일</th>
                    <th style="width: 70px;">조회</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($current_posts) > 0): ?>
                    <?php $row_num = $total_posts - $start;  
    ?>
                    <?php foreach ($current_posts as $post): ?>
                        <?php
                            // 카테고리별 색상 클래스
                            $catClass = '';
                            if ($post['category'] == '자유') {
                                $catClass = 'free';
                            }
                            if ($post['category'] == '라켓') {
                                $catClass = 'racket';
                            }
                            if ($post['category'] == '코트') {
                                $catClass = 'court';
                            }
                        ?>
                        <tr>
                            <td><?php echo $row_num--; ?></td>
                            <td><span class="category-label <?php echo $catClass; ?>"><?php echo $post['category']; ?></span></td>
                            <td><a href="view.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></td>
                            <td><?php echo $post['author']; ?></td>
                            <td><?php echo $post['date']; ?></td>
                            <td><?php echo $post['views']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                            <?php if ($keyword != ''): ?>
                                검색 결과가 없습니다.
                            <?php else: ?>
                                게시글이 없습니다.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <div class="pagination">
            
            <!-- 이전 버튼 -->
            <?php if ($page > 1): ?>
                <a href="board.php?page=<?php echo $page - 1; ?>">◀ 이전</a>
            <?php endif; ?>
            
            <?php
                // 시작 페이지 계산 (5개씩 보여주기)
                $start_page = floor(($page - 1) / 5) * 5 + 1;
                $end_page = $start_page + 4;
                
                // 끝 페이지가 총 페이지 넘으면 조정
                if ($end_page > $total_pages) {
                    $end_page = $total_pages;
                }
            ?>
            
            <!-- 맨 처음으로 -->
            <?php if ($start_page > 1): ?>
                <a href="board.php?page=1">1</a>
                <span style="margin: 0 5px;">...</span>
            <?php endif; ?>
            
            <!-- 페이지 번호들 -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($page == $i): ?>
                    <a href="board.php?page=<?php echo $i; ?>" class="active">
                        <?php echo $i; ?>
                    </a>
                <?php else: ?>
                    <a href="board.php?page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- 맨 끝으로 -->
            <?php if ($end_page < $total_pages): ?>
                <span style="margin: 0 5px;">...</span>
                <a href="board.php?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
            <?php endif; ?>
            
            <!-- 다음 버튼 -->
            <?php if ($page < $total_pages): ?>
                <a href="board.php?page=<?php echo $page + 1; ?>">다음 ▶</a>
            <?php endif; ?>
            
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
