<?php
// This file contains functions for ad integration with the SpinEarnUSDT platform

/**
 * Insert ad code in the header section of your pages
 */
function insert_ad_header() {
    echo '<script src="//solseewuthi.net/sdk.js" data-zone="9350081" data-sdk="show_9350081"></script>';
}

/**
 * Insert reward ad for the spin section
 * Call this function when user has completed a spin and should receive a reward
 */
function insert_reward_ad_spin() {
    ?>
    <script>
    function showRewardAd(callback) {
        show_9350081().then(() => {
            // Give reward to user after ad view
            if (typeof callback === 'function') {
                callback();
            }
        }).catch(e => {
            console.log("Ad error:", e);
            // Still give reward in case of ad error
            if (typeof callback === 'function') {
                callback();
            }
        });
    }
    </script>
    <?php
}

/**
 * Insert reward ad for the watch section
 * Call this function when user clicks to watch an ad
 */
function insert_reward_ad_watch() {
    ?>
    <script>
    function showWatchAd(adId, callback) {
        show_9350081('pop').then(() => {
            // User watched ad till the end or closed it
            if (typeof callback === 'function') {
                callback(adId);
            }
        }).catch(e => {
            console.log("Ad error:", e);
            // Still give reward in case of ad error
            if (typeof callback === 'function') {
                callback(adId);
            }
        });
    }
    </script>
    <?php
}

/**
 * Insert in-app interstitial ad
 * Call this on pages where you want to display non-rewarded ads
 */
function insert_interstitial_ad() {
    ?>
    <script>
    // Show interstitial ad with specific settings
    setTimeout(() => {
        show_9350081({ 
            type: 'inApp', 
            inAppSettings: { 
                frequency: 2, 
                capping: 0.1, 
                interval: 30, 
                timeout: 5, 
                everyPage: false 
            } 
        });
    }, 2000);
    </script>
    <?php
}

/**
 * Insert banner ads
 * @param string $size Banner size ('300x250', '468x60', '160x300', '320x50', '160x600', '728x90')
 */
function insert_banner_ad($size = '300x250') {
    switch ($size) {
        case '300x250':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'d6f6585b4c2596fd0e8732e239518d29\',
                    \'format\' : \'iframe\',
                    \'height\' : 250,
                    \'width\' : 300,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/d6f6585b4c2596fd0e8732e239518d29/invoke.js"></script>';
            break;
        case '468x60':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'0ad0f05a9560f63390da830e31edcdfd\',
                    \'format\' : \'iframe\',
                    \'height\' : 60,
                    \'width\' : 468,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/0ad0f05a9560f63390da830e31edcdfd/invoke.js"></script>';
            break;
        case '160x300':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'d583b363eaf59ab05166f27897a22c03\',
                    \'format\' : \'iframe\',
                    \'height\' : 300,
                    \'width\' : 160,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/d583b363eaf59ab05166f27897a22c03/invoke.js"></script>';
            break;
        case '320x50':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'78d9766c36337e9f612a67af56949d3b\',
                    \'format\' : \'iframe\',
                    \'height\' : 50,
                    \'width\' : 320,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/78d9766c36337e9f612a67af56949d3b/invoke.js"></script>';
            break;
        case '160x600':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'eeaa0f3cc483198053905c795e2f40eb\',
                    \'format\' : \'iframe\',
                    \'height\' : 600,
                    \'width\' : 160,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/eeaa0f3cc483198053905c795e2f40eb/invoke.js"></script>';
            break;
        case '728x90':
            echo '<script type="text/javascript">
                atOptions = {
                    \'key\' : \'59f366253a64a62e491bbd52830a187c\',
                    \'format\' : \'iframe\',
                    \'height\' : 90,
                    \'width\' : 728,
                    \'params\' : {}
                };
                </script>
                <script type="text/javascript" src="//www.highperformanceformat.com/59f366253a64a62e491bbd52830a187c/invoke.js"></script>';
            break;
    }
}

/**
 * Insert popunder ad
 */
function insert_popunder_ad() {
    echo '<script type="text/javascript" src="//pl26684116.profitableratecpm.com/e0/91/0e/e0910e87b8ddbc924c148061bba1235b.js"></script>';
}

/**
 * Insert direct link ad
 */
function insert_direct_link_ad() {
    echo '<a href="https://www.profitableratecpm.com/z05b2fyq3?key=a61d67fba65219539f30f1debe0709b2" class="ad-link">Learn more</a>';
}

/**
 * Insert social bar ad
 */
function insert_social_bar_ad() {
    echo '<script type="text/javascript" src="//pl26684183.profitableratecpm.com/69/e9/12/69e912da24d1a118505f5c39abe2239a.js"></script>';
}

/**
 * Insert native banner ad
 */
function insert_native_banner_ad() {
    echo '<script async="async" data-cfasync="false" src="//pl26684173.profitableratecpm.com/02a4c6d4ab0ae0f42666d93ef73ee137/invoke.js"></script>
    <div id="container-02a4c6d4ab0ae0f42666d93ef73ee137"></div>';
}
?>