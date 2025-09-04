jQuery(document).ready(function($) {
    // --- Helper Functions ---
    function showMessage(selector, message, type) {
        const $messageDiv = $(selector);
        $messageDiv.removeClass('success error info active').addClass(type).text(message);
        $messageDiv.addClass('active');
        setTimeout(() => {
            $messageDiv.removeClass('active');
            // Reset height to 0 after transition to prevent lingering space
            setTimeout(() => { $messageDiv.css('height', '0'); }, 300);
        }, 5000); // Hide after 5 seconds
    }

    function showLoadingOverlay(text = 'Processing...') {
        $('#wpaigen-loading-text').text(text);
        $('#wpaigen-loading-overlay').css('display', 'flex');
    }

    function hideLoadingOverlay() {
        $('#wpaigen-loading-overlay').css('display', 'none');
    }

    function updateDashboardStats() {
        $('#wpaigen-usage-today').text(wpaigen_ajax_object.usage_today);
        $('#wpaigen-daily-limit').text(wpaigen_ajax_object.is_pro ? 'Unlimited' : wpaigen_ajax_object.daily_limit);
        $('#wpaigen-current-plan').text(wpaigen_ajax_object.is_pro ? 'Pro' : 'Free').toggleClass('pro', wpaigen_ajax_object.is_pro);

        if (wpaigen_ajax_object.is_pro) {
            $('.wpaigen-section-upgrade').addClass('wpaigen-hidden');
            $('.wpaigen-cta-card').addClass('wpaigen-hidden');
        } else {
            $('.wpaigen-section-upgrade').removeClass('wpaigen-hidden');
            $('.wpaigen-cta-card').removeClass('wpaigen-hidden');
        }

        // Update Generate page elements based on plan
        handleFreeVersionLimitations();
    }

    function handleFreeVersionLimitations() {
        const isPro = wpaigen_ajax_object.is_pro;
        const $lengthInput = $('#wpaigen-length');
        const $languageSelect = $('#wpaigen-language');
        const $toneSelect = $('#wpaigen-tone');

        // Word Count
        if (!isPro) {
            $lengthInput.attr({ 'max': 200, 'value': Math.min($lengthInput.val(), 200) }).prop('readonly', true);
            $('.length-info').removeClass('wpaigen-hidden');
        } else {
            $lengthInput.removeAttr('max').removeAttr('readonly');
            $('.length-info').addClass('wpaigen-hidden');
        }

        // Language
        $languageSelect.find('option').each(function() {
            if ($(this).val() !== 'indonesian' && $(this).val() !== 'english') {
                $(this).prop('disabled', !isPro).toggleClass('pro-feature', !isPro);
            }
        });
        $('.language-info').toggleClass('wpaigen-hidden', isPro);


        // Tone
        $toneSelect.find('option').each(function() {
            if ($(this).val() !== 'professional') {
                $(this).prop('disabled', !isPro).toggleClass('pro-feature', !isPro);
            }
        });
        $('.tone-info').toggleClass('wpaigen-hidden', isPro);

        const usageToday = parseInt(wpaigen_ajax_object.usage_today);
        const dailyLimit = parseInt(wpaigen_ajax_object.daily_limit);

        // Update button state based on daily limit for free users
        if (!isPro && usageToday >= dailyLimit) {
            $('#wpaigen-generate-btn').prop('disabled', true).text('Daily Limit Reached');
            showMessage('#wpaigen-generate-message', 'You have reached your daily generation limit. Upgrade to Pro!', 'error');
        } else {
            $('#wpaigen-generate-btn').prop('disabled', false).text('Generate Article');
            $('#wpaigen-generate-message').removeClass('active').css('height', '0'); // Clear any prior limit message
        }
    }


    // --- Dashboard Page Logic ---
    if ($('.wpaigen-dashboard').length) {
        updateDashboardStats(); // Initial load

        // Payment Modal handlers
        const $emailModal = $('#wpaigen-email-modal');
        const $successModal = $('#wpaigen-success-modal');
        const $emailInput = $('#wpaigen-email-input');
        const $emailSubmitBtn = $('#wpaigen-email-submit');
        const $modalMessage = $('#wpaigen-modal-message');

        $('#wpaigen-btn-get-pro').on('click', function() {
            $emailModal.css('display', 'flex');
        });

        $('.wpaigen-modal-close').on('click', function() {
            $(this).closest('.wpaigen-modal').css('display', 'none');
            $modalMessage.removeClass('active').text('').css('height', '0');
        });

        $('.wpaigen-modal-success-close').on('click', function() {
            $(this).closest('.wpaigen-modal').css('display', 'none');
            $modalMessage.removeClass('active').text('').css('height', '0');
        });

        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is($emailModal) || $(event.target).is($successModal)) {
                $emailModal.css('display', 'none');
                $successModal.css('display', 'none');
                $modalMessage.removeClass('active').text('').css('height', '0');
            }
        });

        $emailSubmitBtn.on('click', function() {
            const email = $emailInput.val();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showMessage('#wpaigen-modal-message', 'Please enter a valid email address.', 'error');
                return;
            }

            $emailSubmitBtn.prop('disabled', true).text('Processing...');
            showLoadingOverlay('Initiating Payment...');

            $.ajax({
                url: wpaigen_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpaigen_create_transaction',
                    nonce: wpaigen_ajax_object.nonce,
                    email: email
                },
                success: function(response) {
                    hideLoadingOverlay();
                    if (response.success) {
                        $emailModal.css('display', 'none'); // Close email modal
                        snap.pay(response.data.token, {
                            onSuccess: function(result){
                                // This is triggered on successful payment
                                console.log('Payment successful:', result);
                                // The backend API should handle license activation via webhook
                                // Here, we just display success message to user
                                $('#success-email-display').text(email);
                                $successModal.css('display', 'flex');

                                // Optimistically update plugin state (or trigger a license validate)
                                wpaigen_ajax_object.is_pro = true;
                                wpaigen_ajax_object.daily_limit = -1; // Unlimited
                                updateDashboardStats();
                                // Persist this change locally for immediate UI update
                                // In a real scenario, you'd trigger a validation or expect a webhook
                                // For now, let's just make sure UI reflects PRO
                                $.ajax({
                                    url: wpaigen_ajax_object.ajax_url,
                                    type: 'POST',
                                    data: {
                                        action: 'wpaigen_validate_license', // Re-validate to get true status
                                        nonce: wpaigen_ajax_object.nonce,
                                        license_key: wpaigen_ajax_object.current_license_key // Use current, or expect new key from email
                                    }
                                });
                            },
                            onPending: function(result){
                                console.log('Payment pending:', result);
                                showMessage('.wpaigen-dashboard .wpaigen-message', 'Payment is pending. Please complete the transaction.', 'info');
                            },
                            onError: function(result){
                                console.log('Payment error:', result);
                                showMessage('.wpaigen-dashboard .wpaigen-message', 'Payment failed. Please try again.', 'error');
                            },
                            onClose: function(){
                                console.log('Midtrans Snap popup closed.');
                                showMessage('.wpaigen-dashboard .wpaigen-message', 'Payment process cancelled.', 'info');
                            }
                        });
                    } else {
                        showMessage('#wpaigen-modal-message', response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoadingOverlay();
                    showMessage('#wpaigen-modal-message', 'An error occurred: ' + error, 'error');
                },
                complete: function() {
                    $emailSubmitBtn.prop('disabled', false).text('Proceed to Payment');
                }
            });
        });
    }

    // --- Generate Page Logic ---
    if ($('.wpaigen-generate-page').length) {
        handleFreeVersionLimitations(); // Apply limitations on page load

        $('#wpaigen-generate-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $button = $('#wpaigen-generate-btn');
            const $messageDiv = $('#wpaigen-generate-message');

            const keyword = $('#wpaigen-keyword').val();
            const language = $('#wpaigen-language').val();
            let length = $('#wpaigen-length').val();
            const tone = $('#wpaigen-tone').val();
            const useFeaturedImage = $('#wpaigen-use-featured-image').is(':checked');

            // Client-side validation (basic)
            if (!keyword || !language || !length || !tone) {
                showMessage($messageDiv, 'All fields are required.', 'error');
                return;
            }

            // Client-side limit enforcement for free users
            if (!wpaigen_ajax_object.is_pro) {
                length = Math.min(length, 200);

                const dailyLimit = parseInt(wpaigen_ajax_object.daily_limit);
                const usageToday = parseInt(wpaigen_ajax_object.usage_today);

                if (!['indonesian', 'english'].includes(language)) {
                    showMessage($messageDiv, 'Free version supports only Indonesian and English.', 'error');
                    return;
                }
                if (tone !== 'professional') {
                    showMessage($messageDiv, 'Free version supports only professional tone.', 'error');
                    return;
                }
                if (usageToday >= dailyLimit) {
                    showMessage($messageDiv, 'You have reached your daily generation limit. Please upgrade to Pro!', 'error');
                    return;
                }
            }


            $button.prop('disabled', true).text('Generating...');
            showLoadingOverlay('Generating Article...'); // Initial loading text

            // Simulate progress text changes
            let loadingInterval = setInterval(() => {
                const texts = [
                    'Analyzing Keywords...',
                    'Crafting Content...',
                    'Optimizing SEO...',
                    'Finding Images...',
                    'Finalizing Article...'
                ];
                const currentText = $('#wpaigen-loading-text').text();
                const currentIndex = texts.indexOf(currentText);
                const nextIndex = (currentIndex + 1) % texts.length;
                $('#wpaigen-loading-text').text(texts[nextIndex]);
            }, 3000); // Change text every 3 seconds


            $.ajax({
                url: wpaigen_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpaigen_generate_article',
                    nonce: wpaigen_ajax_object.nonce,
                    keyword: keyword,
                    language: language,
                    length: length,
                    tone: tone,
                    use_featured_image: useFeaturedImage
                },
                success: function(response) {
                    clearInterval(loadingInterval);
                    if (response.success) {
                        showMessage($messageDiv, response.data.message, 'success');

                        wpaigen_ajax_object.usage_today = response.data.usage_today;
                        wpaigen_ajax_object.daily_limit = response.data.quota_remaining === -1 ? 'Unlimited' : response.data.quota_remaining; // For free, this is the remaining, not absolute limit

                        handleFreeVersionLimitations();

                        if (response.data.edit_post_url) {
                            setTimeout(() => {
                                window.location.href = response.data.edit_post_url;
                            }, 1500); // Redirect after message is seen
                        }
                    } else {
                        hideLoadingOverlay();
                        showMessage($messageDiv, response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    clearInterval(loadingInterval);
                    hideLoadingOverlay();
                    showMessage($messageDiv, 'An error occurred during generation: ' + error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Generate Article');
                }
            });
        });
    }

    // --- License Page Logic ---
    if ($('.wpaigen-license-page').length) {
        const $licenseKeyInput = $('#wpaigen-license-key');
        const $validateBtn = $('#wpaigen-validate-license-btn');
        const $licenseMessage = $('#wpaigen-license-message');
        const $currentPlanDisplay = $('#current-plan-display');

        $('#wpaigen-license-form').on('submit', function(e) {
            e.preventDefault();

            const licenseKey = $licenseKeyInput.val();
            if (!licenseKey) {
                showMessage($licenseMessage, 'Please enter a license key.', 'error');
                return;
            }

            $validateBtn.prop('disabled', true).text('Validating...');
            showLoadingOverlay('Validating License...');

            $.ajax({
                url: wpaigen_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpaigen_validate_license',
                    nonce: wpaigen_ajax_object.nonce,
                    license_key: licenseKey
                },
                success: function(response) {
                    hideLoadingOverlay();
                    if (response.success) {
                        showMessage($licenseMessage, response.data.message, 'success');
                        wpaigen_ajax_object.is_pro = (response.data.type === 'pro');
                        wpaigen_ajax_object.daily_limit = response.data.quota === -1 ? 'Unlimited' : response.data.quota; // Quota from API, or -1 for unlimited
                        wpaigen_ajax_object.usage_today = response.data.used; // Update used count
                        wpaigen_ajax_object.current_license_key = licenseKey; // Save validated key
                        $currentPlanDisplay.text(response.data.type.charAt(0).toUpperCase() + response.data.type.slice(1));
                        // Update dashboard/generate pages
                        updateDashboardStats();
                    } else {
                        showMessage($licenseMessage, response.data.message, 'error');
                        wpaigen_ajax_object.is_pro = false; // Set to free if validation fails
                        wpaigen_ajax_object.daily_limit = 2; // Reset to free limit
                        wpaigen_ajax_object.usage_today = 0; // Reset usage
                        wpaigen_ajax_object.current_license_key = ''; // Clear invalid key
                        $currentPlanDisplay.text('Free');
                        updateDashboardStats();
                    }
                },
                error: function(xhr, status, error) {
                    hideLoadingOverlay();
                    showMessage($licenseMessage, 'An error occurred: ' + error, 'error');
                },
                complete: function() {
                    $validateBtn.prop('disabled', false).text('Activate / Validate License');
                }
            });
        });
    }
});
