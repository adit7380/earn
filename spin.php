<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    // Check cooldown status
    if (isset($_GET['check_cooldown'])) {
        $db = Database::getInstance();
        $telegramId = $_SESSION['telegram_id'];
        
        // Get user's last spin time
        $user = $db->fetchOne(
            "SELECT last_spin_time FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
        
        // Count spins in the last cooldown period
        $lastSpinTime = $user['last_spin_time'] ?? null;
        $spinsCount = $db->fetchOne(
            "SELECT COUNT(*) AS count FROM spins WHERE telegram_id = ? AND spun_at > now() - interval '? hours'",
            [$telegramId, SPIN_COOLDOWN_HOURS]
        );
        
        $spinsLeft = SPIN_LIMIT - $spinsCount['count'];
        $cooldownActive = false;
        $remainingTime = 0;
        
        if ($spinsLeft <= 0 && $lastSpinTime) {
            $cooldownEnd = strtotime($lastSpinTime) + (SPIN_COOLDOWN_HOURS * 3600);
            $currentTime = time();
            
            if ($cooldownEnd > $currentTime) {
                $cooldownActive = true;
                $remainingTime = $cooldownEnd - $currentTime;
            }
        }
        
        $response = [
            'success' => true,
            'cooldown_active' => $cooldownActive,
            'remaining_time' => $remainingTime,
            'spins_left' => max(0, $spinsLeft)
        ];
    }
    
    // Handle spin action
    if (isset($_POST['action']) && $_POST['action'] == 'spin') {
        $db = Database::getInstance();
        $telegramId = $_SESSION['telegram_id'];
        
        // Check if user has spins left
        $spinsCount = $db->fetchOne(
            "SELECT COUNT(*) AS count FROM spins WHERE telegram_id = ? AND spun_at > now() - interval '?' || ' hours'",
            [$telegramId, SPIN_COOLDOWN_HOURS]
        );
        
        $spinsLeft = SPIN_LIMIT - $spinsCount['count'];
        
        if ($spinsLeft <= 0) {
            $response = [
                'success' => false,
                'message' => 'You have used all your spins. Please wait for the cooldown to finish.'
            ];
        } else {
            // Generate random reward
            $rewards = json_decode(SPIN_REWARDS, true);
            $reward = $rewards[array_rand($rewards)];
            
            // Update user's coins if they won something
            if ($reward !== 'try again') {
                $db->execute(
                    "UPDATE users SET coins = coins + ? WHERE telegram_id = ?",
                    [$reward, $telegramId]
                );
            }
            
            // Record the spin
            $db->execute(
                "INSERT INTO spins (telegram_id, reward) VALUES (?, ?)",
                [$telegramId, $reward === 'try again' ? 0 : $reward]
            );
            
            // Update last spin time
            $db->execute(
                "UPDATE users SET last_spin_time = NOW() WHERE telegram_id = ?",
                [$telegramId]
            );
            
            // Get updated spin count
            $spinsCount = $db->fetchOne(
                "SELECT COUNT(*) AS count FROM spins WHERE telegram_id = ? AND spun_at > now() - interval '?' || ' hours'",
                [$telegramId, SPIN_COOLDOWN_HOURS]
            );
            
            $spinsLeft = SPIN_LIMIT - $spinsCount['count'];
            
            // Get user's updated coins
            $user = $db->fetchOne(
                "SELECT coins FROM users WHERE telegram_id = ?",
                [$telegramId]
            );
            
            $cooldownActive = $spinsLeft <= 0;
            $cooldownSeconds = $cooldownActive ? SPIN_COOLDOWN_HOURS * 3600 : 0;
            
            $response = [
                'success' => true,
                'reward' => $reward,
                'spins_left' => $spinsLeft,
                'coins' => $user['coins'],
                'cooldown_active' => $cooldownActive,
                'cooldown_seconds' => $cooldownSeconds
            ];
        }
    }
    
    // Get spin history
    if (isset($_GET['history'])) {
        $db = Database::getInstance();
        $telegramId = $_SESSION['telegram_id'];
        
        $history = $db->fetchAll(
            "SELECT reward, to_char(spun_at, 'DD Mon YYYY HH24:MI') AS formatted_time 
             FROM spins 
             WHERE telegram_id = ? 
             ORDER BY spun_at DESC 
             LIMIT 10",
            [$telegramId]
        );
        
        $formattedHistory = [];
        foreach ($history as $item) {
            $formattedHistory[] = [
                'reward' => $item['reward'] == 0 ? 'try again' : $item['reward'],
                'time' => $item['formatted_time']
            ];
        }
        
        $response = [
            'success' => true,
            'history' => $formattedHistory
        ];
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Regular page load
require_once 'includes/header.php';

// Get user's spin info
$db = Database::getInstance();
$telegramId = $_SESSION['telegram_id'];

// Count spins in the last cooldown period
$spinsCount = $db->fetchOne(
    "SELECT COUNT(*) AS count FROM spins WHERE telegram_id = ? AND spun_at > now() - interval '?' || ' hours'",
    [$telegramId, SPIN_COOLDOWN_HOURS]
);

$spinsLeft = SPIN_LIMIT - $spinsCount['count'];
?>

<h1 class="mb-4"><i class="fas fa-sync-alt"></i> Spin the Wheel</h1>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="wheel-container">
                    <img src="assets/wheel.svg" alt="Spinning Wheel" id="wheel" class="wheel img-fluid">
                    <div class="wheel-pointer">
                        <i class="fas fa-caret-down text-danger fa-2x"></i>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button id="spin-btn" class="btn btn-primary btn-lg spin-btn">
                        <i class="fas fa-sync-alt me-2"></i> Spin Now
                    </button>
                    
                    <div id="spin-result" class="alert alert-success spin-result mt-3" style="display: none;">
                        <span id="spin-result-text"></span>
                    </div>
                    
                    <div id="spin-cooldown" class="alert alert-warning spin-cooldown mt-3" style="display: none;">
                        <p>You've used all your spins for now. Please wait for the cooldown to finish.</p>
                        <p>Time remaining: <span id="spin-wait-time">00:00:00</span></p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">You have <span id="spins-left"><?= $spinsLeft ?></span> spins left out of <?= SPIN_LIMIT ?> for this period.</p>
                <p class="text-muted small mb-0">Spins reset every <?= SPIN_COOLDOWN_HOURS ?> hours</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card spin-history">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Spin History</h5>
            </div>
            <div class="list-group list-group-flush" id="spin-history-list">
                <div class="list-group-item text-center">
                    <div class="loading-spinner"></div> Loading...
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-gift"></i> Possible Rewards</h5>
            </div>
            <div class="card-body">
                <p>Spin the wheel to win one of these rewards:</p>
                <ul class="list-group">
                    <?php
                    $rewards = json_decode(SPIN_REWARDS, true);
                    foreach ($rewards as $reward) {
                        if ($reward === 'try again') {
                            echo '<li class="list-group-item">Try Again (No reward)</li>';
                        } else {
                            echo '<li class="list-group-item">' . $reward . ' coins</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Spin Wheel -->
<script src="js/spin.js"></script>

<?php require_once 'includes/footer.php'; ?>
