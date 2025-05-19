<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if logout request
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $auth->logout();
    header('Location: login.php');
    exit;
}

// Check if already logged in, redirect to home
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check for Telegram user ID in URL
if (isset($_GET['userid']) && !empty($_GET['userid'])) {
    $telegramId = $_GET['userid'];
    $referrerId = isset($_GET['ref']) ? $_GET['ref'] : null;
    
    if ($auth->loginWithTelegramId($telegramId, $referrerId)) {
        header('Location: index.php');
        exit;
    }
}

// Page title
$pageTitle = "Login - " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            max-width: 500px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            background-color: white;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo img {
            width: 80px;
            height: 80px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .telegram-btn {
            background-color: #0088cc;
            border-color: #0088cc;
            width: 100%;
            padding: 12px;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .telegram-btn:hover {
            background-color: #0077b5;
            border-color: #0077b5;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <div class="login-logo">
                <img src="assets/logo.svg" alt="<?= APP_NAME ?>" class="img-fluid">
            </div>
            
            <div class="login-title">
                <h1><?= APP_NAME ?></h1>
                <p class="text-muted">Earn USDT by spinning the wheel, watching ads, and referring friends.</p>
            </div>
            
            <div class="text-center mb-4">
                <a href="https://t.me/<?= TELEGRAM_BOT_USERNAME ?>?start=login" class="btn btn-primary telegram-btn">
                    <i class="fab fa-telegram me-2"></i> Login with Telegram
                </a>
                <p class="text-muted small">You will be redirected back after authentication</p>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
