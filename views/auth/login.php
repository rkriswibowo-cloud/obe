<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem OBE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/obe/public/assets/css/app.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: var(--background);
        }
        .login-card {
            background-color: var(--surface);
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: var(--background);
            border: 1px solid var(--border);
            color: white;
            border-radius: var(--radius-sm);
            box-sizing: border-box;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 10px;
            border-radius: var(--radius-sm);
            margin-bottom: 15px;
            border: 1px solid var(--danger);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2 style="margin-bottom: 20px; color: var(--primary);">Login OBE</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert-error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="/obe/public/login/submit" method="POST">
                <input type="text" name="username" placeholder="Username (admin)" required>
                <input type="password" name="password" placeholder="Password (password)" required>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
