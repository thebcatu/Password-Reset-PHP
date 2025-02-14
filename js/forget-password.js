document.addEventListener('DOMContentLoaded', () => {
    const emailSection = document.getElementById('forget-email-section');
    const otpSection = document.getElementById('forget-otp-section');
    const emailInput = document.getElementById('forget-email');
    const submitEmailBtn = document.getElementById('forget-submit-email');
    const displayEmail = document.getElementById('forget-display-email');
    const changeEmailBtn = document.getElementById('forget-change-email');
    const otpInputs = document.querySelectorAll('.forget-otp-input');
    const verifyOtpBtn = document.getElementById('forget-verify-otp');
    const resendOtpBtn = document.getElementById('forget-resend-otp');
    const countdownEl = document.getElementById('forget-countdown');
    const statusMessage = document.getElementById('forget-status-message');

    let countdownInterval;
    let timeLeft = 60;

    function showStatusMessage(message, isError = false) {
        statusMessage.textContent = message;
        statusMessage.className = isError ? 'error' : 'success';
        setTimeout(() => {
            statusMessage.textContent = '';
            statusMessage.className = '';
        }, 5000);
    }

    function startCountdown() {
        clearInterval(countdownInterval);
        timeLeft = 60;
        resendOtpBtn.disabled = true;
        
        countdownInterval = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                resendOtpBtn.disabled = false;
            }
        }, 1000);
    }

    function switchToOTPSection(email) {
        emailSection.classList.remove('active');
        otpSection.classList.add('active');
        displayEmail.textContent = email;
        startCountdown();
    }

    function switchToEmailSection() {
        otpSection.classList.remove('active');
        emailSection.classList.add('active');
        clearInterval(countdownInterval);
        otpInputs.forEach(input => input.value = '');
        resendOtpBtn.disabled = true;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    otpInputs.forEach((input, index) => {
        input.addEventListener('keyup', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
                return;
            }
            
            if (input.value) {
                input.value = input.value.slice(-1);
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').slice(0, 6);
            
            if (/^\d+$/.test(pastedData)) {
                pastedData.split('').forEach((digit, i) => {
                    if (i < otpInputs.length) {
                        otpInputs[i].value = digit;
                    }
                });
                otpInputs[Math.min(pastedData.length, otpInputs.length) - 1].focus();
            }
        });
    });

    submitEmailBtn.addEventListener('click', async () => {
        const email = emailInput.value.trim();
        
        if (!isValidEmail(email)) {
            showStatusMessage('Please enter a valid email address', true);
            return;
        }

        submitEmailBtn.disabled = true;
        
        try {
            const response = await fetch('forgetpassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=request_otp&email=${encodeURIComponent(email)}`
            });

            const data = await response.json();
            
            if (data.success) {
                showStatusMessage('OTP sent successfully');
                switchToOTPSection(email);
            } else {
                showStatusMessage(data.message || 'Failed to send OTP', true);
            }
        } catch (error) {
            showStatusMessage('An error occurred. Please try again.', true);
        } finally {
            submitEmailBtn.disabled = false;
        }
    });

    changeEmailBtn.addEventListener('click', () => {
        switchToEmailSection();
    });

    verifyOtpBtn.addEventListener('click', async () => {
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');
        
        if (otpValue.length !== 6 || !/^\d+$/.test(otpValue)) {
            showStatusMessage('Please enter a valid 6-digit OTP', true);
            return;
        }

        verifyOtpBtn.disabled = true;
        
        try {
            const response = await fetch('forgetpassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verify_otp&otp=${encodeURIComponent(otpValue)}`
            });

            const data = await response.json();
            
            if (data.success) {
                showStatusMessage('OTP verified successfully');
                setTimeout(() => {
                    window.location.href = 'resetpassword.php';
                }, 1500);
            } else {
                showStatusMessage(data.message || 'Invalid OTP', true);
                otpInputs.forEach(input => input.value = '');
                otpInputs[0].focus();
            }
        } catch (error) {
            showStatusMessage('An error occurred. Please try again.', true);
        } finally {
            verifyOtpBtn.disabled = false;
        }
    });

    resendOtpBtn.addEventListener('click', async () => {
        const email = displayEmail.textContent;
        
        resendOtpBtn.disabled = true;
        
        try {
            const response = await fetch('forgetpassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=request_otp&email=${encodeURIComponent(email)}`
            });

            const data = await response.json();
            
            if (data.success) {
                showStatusMessage('OTP resent successfully');
                startCountdown();
                otpInputs.forEach(input => input.value = '');
                otpInputs[0].focus();
            } else {
                showStatusMessage(data.message || 'Failed to resend OTP', true);
                resendOtpBtn.disabled = false;
            }
        } catch (error) {
            showStatusMessage('An error occurred. Please try again.', true);
            resendOtpBtn.disabled = false;
        }
    });

    emailInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !submitEmailBtn.disabled) {
            submitEmailBtn.click();
        }
    });

    emailInput.focus();
});