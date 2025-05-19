<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

// Redirect to login if not logged in
if (!$auth->isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}

// Get user data if logged in
$user = null;
if ($auth->isLoggedIn()) {
    $user = $auth->getUserData();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= APP_URL ?>/assets/logo.svg" type="image/svg+xml">
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= APP_URL ?>">
                <img src="<?= APP_URL ?>/assets/logo.svg" alt="<?= APP_NAME ?>" width="30" height="30" class="me-2">
                <?= APP_NAME ?>
            </a>
            
            <?php if ($auth->isLoggedIn()): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="<?= APP_URL ?>">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'spin.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/spin.php">
                                <i class="fas fa-sync-alt"></i> Spin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'watch.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/watch.php">
                                <i class="fas fa-tv"></i> Watch Ads
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'wallet.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/wallet.php">
                                <i class="fas fa-wallet"></i> Wallet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'withdraw.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/withdraw.php">
                                <i class="fas fa-money-bill-wave"></i> Withdraw
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'refer.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/refer.php">
                                <i class="fas fa-user-friends"></i> Refer
                            </a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center">
                        <div class="navbar-text me-3 text-white">
                            <i class="fas fa-coins text-warning"></i> 
                            <?= number_format($user['coins']) ?> coins
                        </div>
                        <div class="navbar-text me-3 text-white">
                            <i class="fas fa-dollar-sign text-success"></i> 
                            <?= $auth->getUsdtBalance($_SESSION['telegram_id']) ?> USDT
                        </div>
                        <a href="<?= APP_URL ?>/login.php?logout=1" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="container my-4">
