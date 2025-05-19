<?php
require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    // Handle ad view submission
    if (isset($_POST['action']) && $_POST['action'] == 'watch' && isset($_POST['ad_id'])) {
        $db = Database::getInstance();
        $telegramId = $_SESSION['telegram_id'];
        $adId = $_POST['ad_id'];
        
        // Get ad details
        $ad = $db->fetchOne(
            "SELECT * FROM ads WHERE id = ?",
            [$adId]
        );
        
        if (!$ad) {
            $response = [
                'success' => false,
                'message' => 'Ad not found'
            ];
        } else {
            // Record ad view
            $db->execute(
                "INSERT INTO ad_views (telegram_id, ad_id) VALUES (?, ?)",
                [$telegramId, $adId]
            );
            
            // Add reward to user
            $db->execute(
                "UPDATE users SET coins = coins + ? WHERE telegram_id = ?",
                [$ad['reward'], $telegramId]
            );
            
            // Get user's updated coins
            $user = $db->fetchOne(
                "SELECT coins FROM users WHERE telegram_id = ?",
                [$telegramId]
            );
            
            $response = [
                'success' => true,
                'reward' => $ad['reward'],
                'coins' => $user['coins']
            ];
        }
    }
    
    // Get ad view history
    if (isset($_GET['history'])) {
        $db = Database::getInstance();
        $telegramId = $_SESSION['telegram_id'];
        
        $history = $db->fetchAll(
            "SELECT a.type, a.reward, to_char(av.viewed_at, 'DD Mon YYYY HH24:MI') AS formatted_time 
             FROM ad_views av
             JOIN ads a ON av.ad_id = a.id 
             WHERE av.telegram_id = ? 
             ORDER BY av.viewed_at DESC 
             LIMIT 10",
            [$telegramId]
        );
        
        $formattedHistory = [];
        foreach ($history as $item) {
            $formattedHistory[] = [
                'type' => ucfirst($item['type']),
                'reward' => $item['reward'],
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

// Get available ads
$db = Database::getInstance();
$ads = $db->fetchAll("SELECT * FROM ads ORDER BY reward ASC");
?>

<h1 class="mb-4"><i class="fas fa-tv"></i> Watch Ads</h1>

<div class="row">
    <div class="col-lg-8">
        <?php if (empty($ads)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No ads available at the moment. Please check back later.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($ads as $ad): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card ad-card" data-ad-id="<?= $ad['id'] ?>" data-duration="<?= $ad['duration'] ?>">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <?= ucfirst($ad['type']) ?> Ad
                                    <span class="badge bg-warning text-dark float-end">+<?= $ad['reward'] ?> coins</span>
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <p>Watch this ad for <?= $ad['duration'] ?> seconds to earn <?= $ad['reward'] ?> coins.</p>
                                
                                <div class="ad-timer" style="display: none;"><?= $ad['duration'] ?></div>
                                
                                <iframe src="about:blank" class="ad-frame" style="width: 100%; height: 200px; border: none; display: none;"></iframe>
                                
                                <button class="btn btn-primary watch-ad-btn">
                                    <i class="fas fa-play-circle me-2"></i> Watch Now
                                </button>
                            </div>
                            <div class="card-footer text-muted small">
                                <i class="fas fa-clock"></i> Duration: <?= $ad['duration'] ?> seconds
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> How It Works</h5>
            </div>
            <div class="card-body">
                <p>Watch ads to earn coins! We offer three types of ads:</p>
                <ul>
                    <li><strong>Quick Ads:</strong> 10 seconds, earn <?= QUICK_AD_REWARD ?> coins</li>
                    <li><strong>Premium Ads:</strong> 20 seconds, earn <?= PREMIUM_AD_REWARD ?> coins</li>
                    <li><strong>Direct Link Ads:</strong> 5 seconds, earn <?= DIRECT_LINK_REWARD ?> coins</li>
                </ul>
                <p>There's no limit to how many ads you can watch, but you must wait for the timer to complete.</p>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Views</h5>
            </div>
            <div class="list-group list-group-flush" id="ad-view-history">
                <div class="list-group-item text-center">
                    <div class="loading-spinner"></div> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Watch Ads -->
<script src="js/watch.js"></script>

<?php require_once 'includes/footer.php'; ?>
