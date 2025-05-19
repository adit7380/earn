<?php
// Database configuration for InfinityFree
define('DB_HOST', 'sql107.infinityfree.com'); // Replace with your InfinityFree MySQL host
define('DB_USER', 'if0_39016652'); // Replace with your InfinityFree MySQL username
define('DB_PASS', '017171273apj'); // Replace with your InfinityFree MySQL password
define('DB_NAME', 'if0_39016652_spinearnusdt'); // Replace with your InfinityFree database name
define('DB_PORT', '3306'); // MySQL port for InfinityFree

// Telegram Bot configuration
define('TELEGRAM_BOT_TOKEN', '8072939664:AAHSo0bix_AkbkuMGohefUcVoaXKdrRvTyY');
define('TELEGRAM_BOT_USERNAME', 'SpinEarnbox_bot');

// App configuration
define('APP_NAME', 'Spin & Earn USDT');
define('APP_URL', 'https://' . $_SERVER['HTTP_HOST']);
define('CONVERSION_RATE', 50000); // 50,000 coins = 1 USDT
define('MIN_WITHDRAWAL', 10); // Minimum 10 USDT withdrawal
define('REFERRAL_BONUS', 100); // 100 coins per referral
define('SPIN_LIMIT', 100); // 100 spins per cooldown period
define('SPIN_COOLDOWN_HOURS', 6); // 6 hours cooldown

// Spin wheel rewards
define('SPIN_REWARDS', json_encode(["try again", 2, 4, 6, 3, 10, 8, 15, 20]));

// Ad rewards configuration
define('QUICK_AD_REWARD', 5); // 5 coins
define('PREMIUM_AD_REWARD', 10); // 10 coins
define('DIRECT_LINK_REWARD', 20); // 20 coins

// Session configuration
session_start();

// Error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Time zone
date_default_timezone_set('UTC');
?>