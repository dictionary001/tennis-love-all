<?php
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (strlen($user_id) < 4) {
        $error = '아이디는 4자 이상이어야 합니다.';
    } elseif ($password !== $password_confirm) {
        $error = '비밀번호가 일치하지 않습니다.';
    } elseif (strlen($password) < 4) {
        $error = '비밀번호는 4자 이상이어야 합니다.';
    } else {
        $users = [];
        if (file_exists('users.json')) {
            $users = json_decode(file_get_contents('users.json'), true);
        }

        $exists = false;
        foreach ($users as $user) {
            if ($user['id'] === $user_id) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $error = '이미 존재하는 아이디입니다.';
        } else {
            $users[] = [
                'id' => $user_id,
                'password' => $password
            ];

            file_put_contents('users.json', json_encode($users));
            $success = '회원가입이 완료되었습니다! 로그인해주세요.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - Love All</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🎾 Love All</a></h1>
    </header>

    <div class="form-box">
        <h2>회원가입</h2>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label>아이디</label>
                <input type="text" name="user_id" placeholder="4자 이상 입력하세요" required>
            </div>
            
            <div class="form-group">
                <label>비밀번호</label>
                <input type="password" name="password" placeholder="4자 이상 입력하세요" required>
            </div>
            
            <div class="form-group">
                <label>비밀번호 확인</label>
                <input type="password" name="password_confirm" placeholder="비밀번호를 다시 입력하세요" required>
                <?php if ($error): ?>
                    <p class="error-msg"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success-msg"><?php echo $success; ?></p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">회원가입</button>
        </form>
        
        <p class="form-link">
            이미 계정이 있으신가요? <a href="login.php">로그인</a>
        </p>
    </div>
</body>
</html>
