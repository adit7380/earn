<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Get user data
$db = Database::getInstance();
$telegramId = $_SESSION['telegram_id'];

// Generate referral link
$referralLink = APP_URL . '/login.php?ref=' . $telegramId;

// Get referral stats
$referrals = $db->fetchAll(
    "SELECT id, to_char(created_at, 'YYYY-MM-DD') AS joined_date 
     FROM users 
     WHERE referrer_id = ? 
     ORDER BY created_at DESC",
    [$telegramId]
);

$totalReferrals = count($referrals);
$totalEarned = $totalReferrals * REFERRAL_BONUS;
?>

<h1 class="mb-4"><i class="fas fa-user-friends"></i> Refer Friends</h1>

<div class="row">
    <div class="col-lg-8">
        <div class="card referral-section">
            <div class="card-body">
                <h2 class="mb-4">Invite Friends & Earn <?= REFERRAL_BONUS ?> Coins Per Referral</h2>
                
                <p class="lead">Share your referral link with friends and earn <?= REFERRAL_BONUS ?> coins for each friend who joins!</p>
                
                <div class="referral-link">
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?= $referralLink ?>" id="referral-link" readonly>
                        <button class="btn btn-outline-primary copy-btn" data-copy="<?= $referralLink ?>">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p>Or share directly via:</p>
                    
                    <a href="https://t.me/share/url?url=<?= urlencode($referralLink) ?>&text=<?= urlencode('Join ' . APP_NAME . ' and earn USDT by spinning the wheel and watching ads! Use my referral link:') ?>" class="btn btn-primary me-2" target="_blank">
                        <i class="fab fa-telegram"></i> Telegram
                    </a>
                    
                    <a href="https://wa.me/?text=<?= urlencode('Join ' . APP_NAME . ' and earn USDT by spinning the wheel and watching ads! Use my referral link: ' . $referralLink) ?>" class="btn btn-success me-2" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Join ' . APP_NAME . ' and earn USDT by spinning the wheel and watching ads! Use my referral link: ' . $referralLink) ?>" class="btn btn-info me-2" target="_blank">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Your Referrals</h5>
            </div>
            <div class="card-body">
                <?php if ($totalReferrals > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>User ID</th>
                                    <th>Joined Date</th>
                                    <th>Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $index => $referral): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>User-<?= $referral['id'] ?></td>
                                        <td><?= $referral['joined_date'] ?></td>
                                        <td><?= REFERRAL_BONUS ?> coins</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>You haven't referred anyone yet. Share your link to start earning!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card referral-stats">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Referral Stats</h5>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="mb-0"><?= $totalReferrals ?></h2>
                                <p class="text-muted mb-0">Total Referrals</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="mb-0"><?= $totalEarned ?></h2>
                                <p class="text-muted mb-0">Coins Earned</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">How it works</h5>
                        <p class="card-text">For each friend who signs up using your referral link, you'll receive <?= REFERRAL_BONUS ?> coins automatically.</p>
                        <p class="card-text">There's no limit to how many friends you can refer!</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-gift"></i> Referral Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Share your link on social media
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Send to friends in Telegram groups
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Create a tutorial video showing your earnings
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Share screenshots of your withdrawals
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Explain how easy it is to earn USDT
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
