<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    $users = [];
    if (file_exists('users.json')) {
        $users = json_decode(file_get_contents('users.json'), true);
    }

    $login_success = false;
    foreach ($users as $user) {
        if ($user['id'] === $user_id && $user['password'] === $password) {
            $login_success = true;
            break;
        }
    }

    if ($login_success) {
        $_SESSION['user_id'] = $user_id;
        header('Location: board.php');
        exit;
    } else {
        $error = '아이디 또는 비밀번호가 틀렸습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - Love All</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🎾 Love All</a></h1>
    </header>

    <div class="form-box">
        <h2>로그인</h2>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>아이디</label>
                <input type="text" name="user_id" placeholder="아이디를 입력하세요" required>
            </div>
            
            <div class="form-group">
                <label>비밀번호</label>
                <input type="password" name="password" placeholder="비밀번호를 입력하세요" required>
                <?php if ($error): ?>
                    <p class="error-msg"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">로그인</button>
        </form>
        
        <p class="form-link">
            계정이 없으신가요? <a href="register.php">회원가입</a>
        </p>
    </div>
</body>
</html>
