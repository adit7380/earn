<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// Handle withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $db = Database::getInstance();
    $telegramId = $_SESSION['telegram_id'];
    $walletAddress = trim($_POST['wallet_address']);
    $amountUsdt = floatval($_POST['amount']);
    
    // Validate input
    if (empty($walletAddress)) {
        $error = 'Please enter a valid wallet address';
    } else if ($amountUsdt < MIN_WITHDRAWAL) {
        $error = 'Minimum withdrawal amount is ' . MIN_WITHDRAWAL . ' USDT';
    } else {
        // Get user data
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
        
        // Calculate USDT balance
        $usdtBalance = $user['coins'] / CONVERSION_RATE;
        
        if ($amountUsdt > $usdtBalance) {
            $error = 'Insufficient balance';
        } else {
            // Calculate coins to deduct
            $coinsToDeduct = $amountUsdt * CONVERSION_RATE;
            
            // Begin transaction
            $db->getConnection()->beginTransaction();
            
            try {
                // Deduct coins from user
                $db->execute(
                    "UPDATE users SET coins = coins - ? WHERE telegram_id = ?",
                    [$coinsToDeduct, $telegramId]
                );
                
                // Create withdrawal request
                $db->execute(
                    "INSERT INTO withdrawals (telegram_id, usdt_amount, wallet_address) VALUES (?, ?, ?)",
                    [$telegramId, $amountUsdt, $walletAddress]
                );
                
                // Commit transaction
                $db->getConnection()->commit();
                
                $success = 'Withdrawal request submitted successfully! It will be processed within 24-48 hours.';
            } catch (Exception $e) {
                // Rollback transaction
                $db->getConnection()->rollBack();
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}

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

// Get withdrawal history
$withdrawals = $db->fetchAll(
    "SELECT id, usdt_amount, wallet_address, status, DATE_FORMAT(requested_at, '%Y-%m-%d %H:%i') AS time
     FROM withdrawals
     WHERE telegram_id = ?
     ORDER BY requested_at DESC",
    [$telegramId]
);
?>

<h1 class="mb-4"><i class="fas fa-money-bill-wave"></i> Withdraw USDT</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-wallet"></i> Your Balance</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0">Coin Balance:</h6>
                        <h3 class="mb-0"><i class="fas fa-coins text-warning"></i> <?= number_format($user['coins']) ?> coins</h3>
                    </div>
                    <div>
                        <h6 class="mb-0">USDT Balance:</h6>
                        <h3 class="mb-0"><i class="fas fa-dollar-sign text-success"></i> <?= $formattedUsdtBalance ?> USDT</h3>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Conversion Rate: <?= number_format(CONVERSION_RATE) ?> coins = 1 USDT
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-money-check-alt"></i> Withdraw Funds</h5>
            </div>
            <div class="card-body">
                <?php if ($usdtBalance < MIN_WITHDRAWAL): ?>
                    <div class="alert alert-warning min-withdrawal-notice">
                        <i class="fas fa-exclamation-triangle"></i> You need at least <?= MIN_WITHDRAWAL ?> USDT to withdraw.
                        <div class="mt-2">
                            <a href="watch.php" class="btn btn-sm btn-outline-primary">Watch Ads</a>
                            <a href="spin.php" class="btn btn-sm btn-outline-primary ms-2">Spin Wheel</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" class="withdraw-form needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (USDT)</label>
                            <input type="number" class="form-control" id="amount" name="amount" min="<?= MIN_WITHDRAWAL ?>" max="<?= $usdtBalance ?>" step="0.01" required placeholder="Minimum <?= MIN_WITHDRAWAL ?> USDT">
                            <div class="form-text">Available: <?= $formattedUsdtBalance ?> USDT</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="wallet_address" class="form-label">USDT Wallet Address (TRC20)</label>
                            <input type="text" class="form-control" id="wallet_address" name="wallet_address" required placeholder="Enter your TRC20 wallet address">
                            <div class="form-text">Make sure to enter the correct TRC20 wallet address</div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I confirm that the wallet address is correct and I understand that wrong address submissions cannot be recovered.
                            </label>
                        </div>
                        
                        <button type="submit" name="withdraw" class="btn btn-success">
                            <i class="fas fa-money-bill-wave me-2"></i> Request Withdrawal
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card withdraw-history">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Withdrawal History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($withdrawals)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>You haven't made any withdrawals yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Wallet Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($withdrawals as $withdrawal): ?>
                                    <tr>
                                        <td><?= $withdrawal['time'] ?></td>
                                        <td><?= $withdrawal['usdt_amount'] ?> USDT</td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                <?= $withdrawal['wallet_address'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($withdrawal['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($withdrawal['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Withdrawal Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Minimum withdrawal amount: <?= MIN_WITHDRAWAL ?> USDT
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Withdrawals are processed within 24-48 hours
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> USDT is sent via TRC20 network (Tron)
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> No withdrawal fees charged by our platform
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i> Always double-check your wallet address
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
