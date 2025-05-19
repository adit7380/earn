<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get the incoming webhook data
$update = json_decode(file_get_contents('php://input'), true);

// Log the update for debugging
file_put_contents('bot_log.txt', date('Y-m-d H:i:s') . ': ' . json_encode($update) . "\n", FILE_APPEND);

// Function to send a message to a user
function sendMessage($chatId, $text, $replyMarkup = null) {
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
    ];
    
    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents('https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage', false, $context);
}

// Check if this is a message update
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $userId = $message['from']['id'];
    
    // Check if it's a command
    if (isset($text) && strpos($text, '/') === 0) {
        $command = explode(' ', $text)[0];
        
        // Handle /start command
        if ($command === '/start') {
            $db = Database::getInstance();
            
            // Check if this is a referral
            $params = explode(' ', $text);
            $referrerId = null;
            
            if (count($params) > 1) {
                if ($params[1] === 'login') {
                    // Generate login link
                    $loginUrl = APP_URL . '/login.php?userid=' . $userId;
                    
                    sendMessage($chatId, "Welcome to " . APP_NAME . "! Click the button below to log in.", json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => '🔐 Login Now', 'url' => $loginUrl]
                            ]
                        ]
                    ]));
                    
                    exit;
                } else {
                    // This is a referral
                    $referrerId = $params[1];
                }
            }
            
            // Check if user exists
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE telegram_id = ?",
                [$userId]
            );
            
            if (!$user) {
                // Create new user
                $db->execute(
                    "INSERT INTO users (telegram_id, referrer_id) VALUES (?, ?)",
                    [$userId, $referrerId]
                );
                
                // Give referral bonus if referrerId is valid
                if ($referrerId && $referrerId != $userId) {
                    $db->execute(
                        "UPDATE users SET coins = coins + ? WHERE telegram_id = ?",
                        [REFERRAL_BONUS, $referrerId]
                    );
                }
            }
            
            // Generate login link
            $loginUrl = APP_URL . '/login.php?userid=' . $userId;
            
            $welcomeMessage = "Welcome to " . APP_NAME . "! 🎮\n\n";
            $welcomeMessage .= "Earn USDT by spinning the wheel, watching ads, and referring friends.\n\n";
            $welcomeMessage .= "Click the button below to start earning now!";
            
            sendMessage($chatId, $welcomeMessage, json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '🚀 Start Earning Now', 'url' => $loginUrl]
                    ]
                ]
            ]));
        }
        // Handle /balance command
        else if ($command === '/balance') {
            $db = Database::getInstance();
            
            // Get user data
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE telegram_id = ?",
                [$userId]
            );
            
            if (!$user) {
                sendMessage($chatId, "You don't have an account yet. Please use /start to create one.");
                exit;
            }
            
            // Calculate USDT balance
            $usdtBalance = $user['coins'] / CONVERSION_RATE;
            $formattedUsdtBalance = number_format($usdtBalance, 2);
            
            $balanceMessage = "💰 <b>Your Balance</b>\n\n";
            $balanceMessage .= "🪙 Coins: " . number_format($user['coins']) . "\n";
            $balanceMessage .= "💵 USDT: " . $formattedUsdtBalance . "\n\n";
            $balanceMessage .= "Login to withdraw your earnings:";
            
            $loginUrl = APP_URL . '/login.php?userid=' . $userId;
            
            sendMessage($chatId, $balanceMessage, json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '💰 Manage Wallet', 'url' => $loginUrl]
                    ]
                ]
            ]));
        }
        // Handle /referral command
        else if ($command === '/referral') {
            $db = Database::getInstance();
            
            // Get user data
            $user = $db->fetchOne(
                "SELECT * FROM users WHERE telegram_id = ?",
                [$userId]
            );
            
            if (!$user) {
                sendMessage($chatId, "You don't have an account yet. Please use /start to create one.");
                exit;
            }
            
            // Generate referral link
            $referralLink = APP_URL . '/login.php?ref=' . $userId;
            
            // Get referral stats
            $referrals = $db->fetchOne(
                "SELECT COUNT(*) as count FROM users WHERE referrer_id = ?",
                [$userId]
            );
            
            $referralMessage = "🔗 <b>Your Referral Link</b>\n\n";
            $referralMessage .= $referralLink . "\n\n";
            $referralMessage .= "👥 Total Referrals: " . $referrals['count'] . "\n";
            $referralMessage .= "💰 Earned: " . ($referrals['count'] * REFERRAL_BONUS) . " coins\n\n";
            $referralMessage .= "Share this link with your friends and earn " . REFERRAL_BONUS . " coins for each friend who joins!";
            
            sendMessage($chatId, $referralMessage);
        }
        // Handle /help command
        else if ($command === '/help') {
            $helpMessage = "🤖 <b>" . APP_NAME . " Bot Commands</b>\n\n";
            $helpMessage .= "/start - Create an account and start earning\n";
            $helpMessage .= "/balance - Check your coin and USDT balance\n";
            $helpMessage .= "/referral - Get your referral link\n";
            $helpMessage .= "/help - Show this help message\n\n";
            $helpMessage .= "Visit our website to spin the wheel, watch ads, and withdraw your earnings:";
            
            $loginUrl = APP_URL . '/login.php?userid=' . $userId;
            
            sendMessage($chatId, $helpMessage, json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 Go to Website', 'url' => $loginUrl]
                    ]
                ]
            ]));
        }
    }
}

// Return 200 OK
http_response_code(200);
?>
