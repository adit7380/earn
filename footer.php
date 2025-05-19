    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= APP_NAME ?></h5>
                    <p>Earn USDT by spinning the wheel, watching ads, and referring friends.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= APP_URL ?>" class="text-white">Home</a></li>
                        <li><a href="<?= APP_URL ?>/spin.php" class="text-white">Spin Wheel</a></li>
                        <li><a href="<?= APP_URL ?>/watch.php" class="text-white">Watch Ads</a></li>
                        <li><a href="<?= APP_URL ?>/wallet.php" class="text-white">Wallet</a></li>
                        <li><a href="<?= APP_URL ?>/withdraw.php" class="text-white">Withdraw</a></li>
                        <li><a href="<?= APP_URL ?>/refer.php" class="text-white">Refer Friends</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Connect with Us</h5>
                    <p>Join our Telegram channel: <a href="https://t.me/<?= TELEGRAM_BOT_USERNAME ?>" class="text-white" target="_blank">@<?= TELEGRAM_BOT_USERNAME ?></a></p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Main JS -->
    <script src="<?= APP_URL ?>/js/main.js"></script>
</body>
</html>
