document.addEventListener('DOMContentLoaded', function() {
    const spinBtn = document.getElementById('spin-btn');
    const wheel = document.getElementById('wheel');
    const spinResult = document.getElementById('spin-result');
    const spinResultText = document.getElementById('spin-result-text');
    const spinWaitTime = document.getElementById('spin-wait-time');
    const spinCooldown = document.getElementById('spin-cooldown');
    let canSpin = true;
    
    // Check if cooldown is active on page load
    checkCooldown();
    
    // Function to check cooldown status
    function checkCooldown() {
        makeAjaxRequest('spin.php?check_cooldown=1')
            .then(response => {
                if (response.cooldown_active) {
                    canSpin = false;
                    spinBtn.disabled = true;
                    spinCooldown.style.display = 'block';
                    
                    // Start the countdown
                    updateCooldownTimer(response.remaining_time);
                } else {
                    canSpin = true;
                    spinBtn.disabled = false;
                    spinCooldown.style.display = 'none';
                }
                
                // Update spins left
                document.getElementById('spins-left').textContent = response.spins_left;
            })
            .catch(error => {
                console.error('Error checking cooldown:', error);
                showToast('Error checking spin status', 'danger');
            });
    }
    
    // Function to update cooldown timer
    function updateCooldownTimer(remainingSeconds) {
        if (remainingSeconds <= 0) {
            checkCooldown();
            return;
        }
        
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;
        
        const timeString = 
            (hours < 10 ? '0' + hours : hours) + ':' +
            (minutes < 10 ? '0' + minutes : minutes) + ':' +
            (seconds < 10 ? '0' + seconds : seconds);
        
        spinWaitTime.textContent = timeString;
        
        setTimeout(() => {
            updateCooldownTimer(remainingSeconds - 1);
        }, 1000);
    }
    
    // Spin the wheel when button is clicked
    if (spinBtn) {
        spinBtn.addEventListener('click', function() {
            if (!canSpin) {
                showToast('Please wait for the cooldown to finish', 'warning');
                return;
            }
            
            spinBtn.disabled = true;
            
            // Hide any previous result
            spinResult.style.display = 'none';
            
            // Make request to get spin result
            makeAjaxRequest('spin.php', 'POST', { action: 'spin' })
                .then(response => {
                    if (response.success) {
                        // Calculate rotation based on the result
                        const rewards = ["try again", 2, 4, 6, 3, 10, 8, 15, 20];
                        const rewardIndex = rewards.indexOf(response.reward);
                        const segmentAngle = 360 / rewards.length;
                        const randomOffset = Math.random() * (segmentAngle * 0.7);
                        const destinationAngle = 360 * 5 + (rewardIndex * segmentAngle) + randomOffset;
                        
                        // Animate wheel
                        wheel.style.transition = 'transform 5s cubic-bezier(0.17, 0.67, 0.83, 0.67)';
                        wheel.style.transform = `rotate(${destinationAngle}deg)`;
                        
                        // Display result after animation completes
                        setTimeout(() => {
                            // Show the result
                            spinResultText.textContent = response.reward === 'try again' ? 
                                'Sorry, try again next time!' : `Congratulations! You won ${response.reward} coins!`;
                            
                            spinResult.className = response.reward === 'try again' ? 
                                'alert alert-warning spin-result' : 'alert alert-success spin-result';
                            
                            spinResult.style.display = 'block';
                            
                            // Update spins left
                            document.getElementById('spins-left').textContent = response.spins_left;
                            
                            // Update coins display in header
                            const coinsDisplay = document.querySelector('.navbar-text i.fas.fa-coins').parentNode;
                            coinsDisplay.innerHTML = `<i class="fas fa-coins text-warning"></i> ${formatNumber(response.coins)} coins`;
                            
                            // Check if cooldown is active
                            if (response.cooldown_active) {
                                canSpin = false;
                                spinCooldown.style.display = 'block';
                                updateCooldownTimer(response.cooldown_seconds);
                            } else {
                                spinBtn.disabled = false;
                            }
                            
                            // Reset wheel rotation for next spin (without animation)
                            setTimeout(() => {
                                wheel.style.transition = 'none';
                                wheel.style.transform = 'rotate(0deg)';
                            }, 1000);
                        }, 5000);
                    } else {
                        showToast(response.message, 'danger');
                        spinBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error spinning wheel:', error);
                    showToast('Error spinning wheel', 'danger');
                    spinBtn.disabled = false;
                });
        });
    }
    
    // Load spin history
    const spinHistoryContainer = document.getElementById('spin-history-list');
    if (spinHistoryContainer) {
        makeAjaxRequest('spin.php?history=1')
            .then(response => {
                if (response.success && response.history.length > 0) {
                    let historyHTML = '';
                    response.history.forEach(item => {
                        const rewardClass = item.reward === 'try again' ? 'text-warning' : 'text-success';
                        const rewardText = item.reward === 'try again' ? 'Try Again' : `${item.reward} coins`;
                        
                        historyHTML += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">${item.time}</small>
                                    <div class="${rewardClass}">${rewardText}</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    spinHistoryContainer.innerHTML = historyHTML;
                } else {
                    spinHistoryContainer.innerHTML = '<div class="list-group-item text-center">No spin history yet</div>';
                }
            })
            .catch(error => {
                console.error('Error loading spin history:', error);
                spinHistoryContainer.innerHTML = '<div class="list-group-item text-center text-danger">Failed to load spin history</div>';
            });
    }
});
