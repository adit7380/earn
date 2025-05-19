<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Get user data
$db = Database::getInstance();
$telegramId = $_SESSION['telegram_id'];

$user = $db->fetchOne(
    "SELECT * FROM users WHERE telegram_id = ?",
    [$telegramId]
);

// Calculate USDT balance
$usdtBalance = $user['coins'] / CONVERSION_RATE;
$formattedUsdtBalance = number_format($usdtBalance, 2);

// Get recent transactions (spins and ad views)
$transactions = $db->fetchAll(
    "SELECT 'spin' AS type, s.reward AS amount, to_char(s.spun_at, 'YYYY-MM-DD HH24:MI') AS time
     FROM spins s
     WHERE s.telegram_id = ? AND s.reward > 0
     UNION ALL
     SELECT 'ad_view' AS type, a.reward AS amount, to_char(av.viewed_at, 'YYYY-MM-DD HH24:MI') AS time
     FROM ad_views av
     JOIN ads a ON av.ad_id = a.id
     WHERE av.telegram_id = ?
     ORDER BY time DESC
     LIMIT 10",
    [$telegramId, $telegramId]
);

// Get recent withdrawals
$withdrawals = $db->fetchAll(
    "SELECT usdt_amount, status, to_char(requested_at, 'YYYY-MM-DD HH24:MI') AS time
     FROM withdrawals
     WHERE telegram_id = ?
     ORDER BY requested_at DESC
     LIMIT 5",
    [$telegramId]
);
?>

<h1 class="mb-4"><i class="fas fa-wallet"></i> Your Wallet</h1>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card balance-card">
            <div class="card-body">
                <div class="balance-label">Coin Balance</div>
                <div class="balance-value text-primary">
                    <i class="fas fa-coins"></i> <?= number_format($user['coins']) ?>
                </div>
                
                <hr>
                
                <div class="balance-label">USDT Balance</div>
                <div class="balance-value text-success">
                    <i class="fas fa-dollar-sign"></i> <?= $formattedUsdtBalance ?>
                </div>
                
                <div class="conversion-info mt-4">
                    <p class="mb-2"><i class="fas fa-info-circle"></i> Conversion Rate:</p>
                    <h5><?= number_format(CONVERSION_RATE) ?> Coins = 1 USDT</h5>
                </div>
                
                <div class="mt-4">
                    <a href="withdraw.php" class="btn btn-success btn-lg <?= $usdtBalance < MIN_WITHDRAWAL ? 'disabled' : '' ?>">
                        <i class="fas fa-money-bill-wave me-2"></i> Withdraw USDT
                    </a>
                    
                    <?php if ($usdtBalance < MIN_WITHDRAWAL): ?>
                        <div class="text-danger mt-2">
                            <small>Minimum withdrawal amount is <?= MIN_WITHDRAWAL ?> USDT</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Earning Progress</h5>
            </div>
            <div class="card-body">
                <p>Progress to next withdrawal (<?= MIN_WITHDRAWAL ?> USDT):</p>
                
                <?php
                $progressPercentage = min(100, ($usdtBalance / MIN_WITHDRAWAL) * 100);
                ?>
                
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?= $progressPercentage ?>%;" 
                         aria-valuenow="<?= $progressPercentage ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <?= number_format($progressPercentage, 1) ?>%
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>0 USDT</span>
                    <span><?= $formattedUsdtBalance ?> USDT</span>
                    <span><?= MIN_WITHDRAWAL ?> USDT</span>
                </div>
                
                <div class="mt-4">
                    <h5>How to earn more:</h5>
                    <ul>
                        <li><a href="spin.php">Spin the wheel</a> - Up to 20 coins per spin</li>
                        <li><a href="watch.php">Watch ads</a> - 5-20 coins per ad</li>
                        <li><a href="refer.php">Refer friends</a> - <?= REFERRAL_BONUS ?> coins per referral</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Transactions</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($transactions)): ?>
                    <div class="list-group-item text-center">No transactions yet</div>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div>
                                    <?php if ($transaction['type'] == 'spin'): ?>
                                        <i class="fas fa-sync-alt text-primary"></i> Spin Reward
                                    <?php else: ?>
                                        <i class="fas fa-tv text-primary"></i> Ad View
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><?= $transaction['time'] ?></small>
                            </div>
                            <span class="badge bg-success rounded-pill">+<?= $transaction['amount'] ?> coins</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Recent Withdrawals</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($withdrawals)): ?>
                    <div class="list-group-item text-center">No withdrawals yet</div>
                <?php else: ?>
                    <?php foreach ($withdrawals as $withdrawal): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div>
                                    <i class="fas fa-money-bill-wave"></i> USDT Withdrawal
                                </div>
                                <small class="text-muted"><?= $withdrawal['time'] ?></small>
                            </div>
                            <div>
                                <span class="badge bg-primary rounded-pill"><?= $withdrawal['usdt_amount'] ?> USDT</span>
                                <?php if ($withdrawal['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($withdrawal['status'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
