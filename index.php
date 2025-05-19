<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1>Welcome to <?= APP_NAME ?></h1>
        <p class="lead">Earn USDT by spinning the wheel, watching ads, and referring friends.</p>
        <div class="mt-4">
            <a href="spin.php" class="btn btn-light btn-lg me-2">
                <i class="fas fa-sync-alt"></i> Spin Now
            </a>
            <a href="watch.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-tv"></i> Watch Ads
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body">
                <i class="fas fa-sync-alt"></i>
                <h3>Spin Wheel</h3>
                <p>Spin the wheel every 6 hours to win up to 20 coins per spin. You get 100 spins per cooldown period.</p>
                <a href="spin.php" class="btn btn-primary">Spin Now</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body">
                <i class="fas fa-tv"></i>
                <h3>Watch Ads</h3>
                <p>Watch ads to earn coins. Quick ads (10s) earn 5 coins, Premium ads (20s) earn 10 coins, and Direct Link ads (5s) earn 20 coins.</p>
                <a href="watch.php" class="btn btn-primary">Watch Ads</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body">
                <i class="fas fa-user-friends"></i>
                <h3>Refer Friends</h3>
                <p>Invite your friends and earn 100 coins for each friend who joins using your referral link.</p>
                <a href="refer.php" class="btn btn-primary">Get Referral Link</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-dollar-sign"></i> Earn USDT</h4>
            </div>
            <div class="card-body">
                <p>Convert your earned coins to USDT at the rate of <?= number_format(CONVERSION_RATE) ?> coins = 1 USDT.</p>
                <p>Minimum withdrawal amount: <?= MIN_WITHDRAWAL ?> USDT</p>
                <p>Your current USDT balance: <strong><?= $auth->getUsdtBalance($_SESSION['telegram_id']) ?> USDT</strong></p>
                <a href="wallet.php" class="btn btn-success">Go to Wallet</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-chart-line"></i> Your Stats</h4>
            </div>
            <div class="card-body">
                <?php
                $db = Database::getInstance();
                
                // Get total spins
                $totalSpins = $db->fetchOne(
                    "SELECT COUNT(*) as count FROM spins WHERE telegram_id = ?",
                    [$_SESSION['telegram_id']]
                );
                
                // Get total ad views
                $totalAdViews = $db->fetchOne(
                    "SELECT COUNT(*) as count FROM ad_views WHERE telegram_id = ?",
                    [$_SESSION['telegram_id']]
                );
                
                // Get total referrals
                $totalReferrals = $db->fetchOne(
                    "SELECT COUNT(*) as count FROM users WHERE referrer_id = ?",
                    [$_SESSION['telegram_id']]
                );
                ?>
                
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h3><?= number_format($totalSpins['count']) ?></h3>
                        <p>Total Spins</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3><?= number_format($totalAdViews['count']) ?></h3>
                        <p>Ads Watched</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h3><?= number_format($totalReferrals['count']) ?></h3>
                        <p>Referrals</p>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="refer.php" class="btn btn-outline-primary">
                        <i class="fas fa-share-alt"></i> Share Referral Link
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-info-circle"></i> How It Works</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                        <h5>Step 1</h5>
                        <p>Log in with your Telegram account</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-coins fa-3x text-warning mb-3"></i>
                        <h5>Step 2</h5>
                        <p>Earn coins by spinning the wheel and watching ads</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-exchange-alt fa-3x text-success mb-3"></i>
                        <h5>Step 3</h5>
                        <p>Convert your coins to USDT (<?= number_format(CONVERSION_RATE) ?> coins = 1 USDT)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <i class="fas fa-wallet fa-3x text-primary mb-3"></i>
                        <h5>Step 4</h5>
                        <p>Withdraw your USDT to your wallet (min. <?= MIN_WITHDRAWAL ?> USDT)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast container for notifications -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11"></div>

<?php require_once 'includes/footer.php'; ?>
