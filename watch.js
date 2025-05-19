document.addEventListener('DOMContentLoaded', function() {
    // Watch ads functionality
    const adCards = document.querySelectorAll('.ad-card');
    
    adCards.forEach(card => {
        const watchBtn = card.querySelector('.watch-ad-btn');
        const adTimer = card.querySelector('.ad-timer');
        const adFrame = card.querySelector('.ad-frame');
        const adId = card.getAttribute('data-ad-id');
        const adDuration = parseInt(card.getAttribute('data-duration'), 10);
        let countdownInterval;
        
        if (watchBtn) {
            watchBtn.addEventListener('click', function() {
                // Disable the button
                watchBtn.disabled = true;
                
                // Show the ad frame
                if (adFrame) {
                    adFrame.style.display = 'block';
                }
                
                // Start the timer
                let secondsLeft = adDuration;
                
                // Update timer display
                adTimer.textContent = secondsLeft;
                adTimer.style.display = 'block';
                
                // Start countdown
                countdownInterval = setInterval(() => {
                    secondsLeft--;
                    adTimer.textContent = secondsLeft;
                    
                    if (secondsLeft <= 0) {
                        // Clear the interval
                        clearInterval(countdownInterval);
                        
                        // Submit the view
                        submitAdView(adId, card);
                    }
                }, 1000);
            });
        }
    });
    
    // Function to submit ad view
    function submitAdView(adId, card) {
        const watchBtn = card.querySelector('.watch-ad-btn');
        const adTimer = card.querySelector('.ad-timer');
        const adFrame = card.querySelector('.ad-frame');
        
        makeAjaxRequest('watch.php', 'POST', {
            action: 'watch',
            ad_id: adId
        })
        .then(response => {
            if (response.success) {
                // Show success message
                showToast(`Congratulations! You earned ${response.reward} coins`, 'success');
                
                // Update user's coin balance in navbar
                const coinsDisplay = document.querySelector('.navbar-text i.fas.fa-coins').parentNode;
                coinsDisplay.innerHTML = `<i class="fas fa-coins text-warning"></i> ${formatNumber(response.coins)} coins`;
                
                // Reset the ad card
                if (adFrame) {
                    adFrame.style.display = 'none';
                }
                adTimer.style.display = 'none';
                watchBtn.disabled = false;
            } else {
                showToast(response.message, 'danger');
                
                // Reset the ad card
                if (adFrame) {
                    adFrame.style.display = 'none';
                }
                adTimer.style.display = 'none';
                watchBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error submitting ad view:', error);
            showToast('Error submitting ad view. Please try again.', 'danger');
            
            // Reset the ad card
            if (adFrame) {
                adFrame.style.display = 'none';
            }
            adTimer.style.display = 'none';
            watchBtn.disabled = false;
        });
    }
    
    // Load ad view history
    const viewHistoryContainer = document.getElementById('ad-view-history');
    if (viewHistoryContainer) {
        makeAjaxRequest('watch.php?history=1')
            .then(response => {
                if (response.success && response.history.length > 0) {
                    let historyHTML = '';
                    response.history.forEach(item => {
                        historyHTML += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">${item.time}</small>
                                    <div>${item.type} Ad - Earned ${item.reward} coins</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    viewHistoryContainer.innerHTML = historyHTML;
                } else {
                    viewHistoryContainer.innerHTML = '<div class="list-group-item text-center">No ad viewing history yet</div>';
                }
            })
            .catch(error => {
                console.error('Error loading ad view history:', error);
                viewHistoryContainer.innerHTML = '<div class="list-group-item text-center text-danger">Failed to load ad view history</div>';
            });
    }
});
