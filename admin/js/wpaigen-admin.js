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
            $('#wpaigen-generate-message').removeClass('active').css('wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwheight', '0'); // Clear any prior limit message
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

    function initGoogleTrends() {
        loadGoogleTrends();

        $(document).on('click', '.wpaigen-refresh-btn', function() {
            loadGoogleTrends();
        });

        $(document).on('click', '.wpaigen-keyword-item', function() {
            const keywordText = $(this).find('.keyword-text').clone().children().remove().end().text().trim();
            $('#wpaigen-keyword').val(keywordText).focus();
            $('#wpaigen-keyword').addClass('highlight');
            setTimeout(() => {
                $('#wpaigen-keyword').removeClass('highlight');
            }, 1000);
        });
    }

    function loadGoogleTrends() {
        const $trendsCard = $('.wpaigen-trends-card');
        const $refreshBtn = $('.wpaigen-refresh-btn');
        const $keywordsList = $('.wpaigen-keywords-list');

        console.log('Loading Google Trends...', {
            trendsCard: $trendsCard.length,
            keywordsList: $keywordsList.length,
            ajaxUrl: wpaigen_ajax_object?.ajax_url
        });

        if ($trendsCard.length === 0) {
            console.log('Trends card not found');
            return;
        }

        $refreshBtn.addClass('loading');
        $keywordsList.html(`
            <div class="wpaigen-trends-loading">
                <div class="spinner"></div>
                Loading trending keywords...
            </div>
        `);

        $.ajax({
            url: wpaigen_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'wpaigen_get_google_trends',
                nonce: wpaigen_ajax_object.nonce
            },
            beforeSend: function() {
                console.log('Sending AJAX request...', {
                    action: 'wpaigen_get_google_trends',
                    nonce: wpaigen_ajax_object.nonce ? 'exists' : 'missing'
                });
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success && response.data.trends) {
                    renderTrendingKeywords(response.data.trends);
                } else {
                    showError('Failed to load trending keywords: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', { xhr, status, error });
                showError('Network error: Unable to fetch trending keywords');
            },
            complete: function() {
                $refreshBtn.removeClass('loading');
            }
        });
    }

    function renderTrendingKeywords(trends) {
        const $keywordsList = $('.wpaigen-keywords-list');

        if (!trends || trends.length === 0) {
            showError('No trending keywords available at the moment.');
            return;
        }

        const keywordsHtml = trends.map(keyword => `
            <div class="wpaigen-keyword-item">
                <span class="keyword-text">
                    <span class="trending-indicator"></span>
                    ${keyword.title}
                </span>
                <div class="keyword-stats">
                    <span class="wpaigen-keyword-traffic">${formatNumber(keyword.traffic)}</span>
                    <span class="wpaigen-keyword-articles">• ${keyword.articleCount}</span>
                </div>
            </div>
        `).join('');

        $keywordsList.html(keywordsHtml);
    }

    function showError(message) {
        const $keywordsList = $('.wpaigen-keywords-list');
        $keywordsList.html(`
            <div class="wpaigen-trends-error">
                <span class="dashicons dashicons-warning"></span>
                ${message}
            </div>
        `);
    }

    function formatNumber(num) {
        const number = parseInt(num);
        if (number >= 1000000) {
            return (number / 1000000).toFixed(1) + 'M';
        } else if (number >= 1000) {
            return (number / 1000).toFixed(1) + 'K';
        }
        return number.toString();
    }

    // --- Scheduling Functions ---
    function initScheduling() {
        // Schedule modal controls
        $('#wpaigen-schedule-btn').on('click', showScheduleModal);
        $('#wpaigen-close-schedule-modal, #wpaigen-cancel-schedule').on('click', hideScheduleModal);
        $('#wpaigen-schedule-form').on('submit', handleScheduleSubmit);

        // Set minimum datetime to current time
        const now = new Date();
        const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 16);
        $('#wpaigen-schedule-date').attr('min', localDateTime);
    }

    function showScheduleModal() {
        const keyword = $('#wpaigen-keyword').val();
        const language = $('#wpaigen-language option:selected').text();
        const length = $('#wpaigen-length').val();
        const tone = $('#wpaigen-tone option:selected').text();
        const useFeaturedImage = $('#wpaigen-use-featured-image').is(':checked');

        // Validate form first
        if (!keyword) {
            showMessage('#wpaigen-generate-message', 'Please enter a keyword first.', 'error');
            return;
        }

        // Auto-detect browser timezone
        detectBrowserTimezone();

        // Update preview
        $('#preview-keyword').text(keyword);
        $('#preview-language').text(language);
        $('#preview-length').text(length + ' words');
        $('#preview-tone').text(tone);
        $('#preview-featured-image').text(useFeaturedImage ? 'Yes' : 'No');

        // Show modal
        $('#wpaigen-schedule-modal').css('display', 'flex');
    }

    function hideScheduleModal() {
        $('#wpaigen-schedule-modal').css('display', 'none');
        $('#wpaigen-schedule-form')[0].reset();
        $('#wpaigen-schedule-message').removeClass('active');
    }

    function detectBrowserTimezone() {
        // Get browser timezone using Intl.DateTimeFormat
        let browserTimezone = 'UTC';
        try {
            browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        } catch (e) {
            // Silent fallback
        }

        // Update browser timezone display
        $('#browser-timezone').text(browserTimezone || 'Unknown');

        // Try to match detected timezone with available options
        const $timezoneSelect = $('#wpaigen-schedule-timezone');
        const $options = $timezoneSelect.find('option');

        // Check for exact match first
        let $matchedOption = $options.filter(function() {
            return $(this).val() === browserTimezone;
        });

        // If no exact match, try to match by region
        if ($matchedOption.length === 0) {
            const region = browserTimezone.split('/')[0]; // e.g., 'Asia' from 'Asia/Jakarta'
            $matchedOption = $options.filter(function() {
                return $(this).val().startsWith(region + '/');
            });
        }

        // Set the matched option if found, otherwise use browser timezone as fallback
        if ($matchedOption.length > 0) {
            $timezoneSelect.val($matchedOption.val());
        } else {
            // Add browser timezone as new option if it's not in the list
            $timezoneSelect.append(`<option value="${browserTimezone}" selected>${browserTimezone}</option>`);
            $timezoneSelect.val(browserTimezone);
        }
    }

    function handleScheduleSubmit(e) {
        e.preventDefault();

        const formData = {
            action: 'wpaigen_schedule_article',
            nonce: wpaigen_ajax_object.nonce,
            keyword: $('#wpaigen-keyword').val(),
            language: $('#wpaigen-language').val(),
            length: $('#wpaigen-length').val(),
            tone: $('#wpaigen-tone').val(),
            use_featured_image: $('#wpaigen-use-featured-image').is(':checked').toString(),
            scheduled_date: $('#wpaigen-schedule-date').val(),
            timezone: $('#wpaigen-schedule-timezone').val()
        };

        showLoadingOverlay('Scheduling article...');

        $.post(wpaigen_ajax_object.ajax_url, formData, function(response) {
            hideLoadingOverlay();

            if (response.success) {
                showMessage('#wpaigen-schedule-message', response.data.message, 'success');

                // Update usage stats
                wpaigen_ajax_object.usage_today = response.data.usage_today;
                updateDashboardStats();

                // Hide modal after success
                setTimeout(() => {
                    hideScheduleModal();
                }, 2000);
            } else {
                showMessage('#wpaigen-schedule-message', response.data.message, 'error');
            }
        }).fail(function() {
            hideLoadingOverlay();
            showMessage('#wpaigen-schedule-message', 'An error occurred. Please try again.', 'error');
        });
    }

    // --- Schedule Page Functions ---
    function initSchedulePage() {
        loadScheduledPosts();
        updateStats();

        // Filter change
        $('#wpaigen-status-filter').on('change', loadScheduledPosts);

        // Refresh button
        $('#wpaigen-refresh-schedules').on('click', function() {
            loadScheduledPosts();
            updateStats();
        });

        // Load more button
        $('#wpaigen-load-more').on('click', loadMoreScheduledPosts);

        // Modal close
        $('#wpaigen-close-schedule-modal').on('click', function() {
            $('#wpaigen-schedule-modal').css('display', 'none');
        });
    }

    let currentPage = 0;
    let currentStatus = 'all';
    let isLoading = false;

    function loadScheduledPosts() {
        currentStatus = $('#wpaigen-status-filter').val();
        currentPage = 0;

        showScheduleLoading(true);

        const data = {
            action: 'wpaigen_get_scheduled_posts',
            nonce: wpaigen_ajax_object.nonce,
            status: currentStatus,
            limit: 50,
            offset: 0
        };

        $.get(wpaigen_ajax_object.ajax_url, data, function(response) {
            if (response.success) {
                renderScheduledPosts(response.data.posts, response.data.total);
                updateStats();
            }
        }).always(function() {
            showScheduleLoading(false);
        });
    }

    function loadMoreScheduledPosts() {
        if (isLoading) return;

        isLoading = true;
        currentPage += 50;

        const data = {
            action: 'wpaigen_get_scheduled_posts',
            nonce: wpaigen_ajax_object.nonce,
            status: currentStatus,
            limit: 50,
            offset: currentPage
        };

        $('#wpaigen-load-more').text('Loading...');

        $.get(wpaigen_ajax_object.ajax_url, data, function(response) {
            if (response.success && response.data.posts.length > 0) {
                appendScheduledPosts(response.data.posts);
            } else {
                $('#wpaigen-load-more').hide();
            }
        }).always(function() {
            isLoading = false;
            $('#wpaigen-load-more').text('Load More');
        });
    }

    function renderScheduledPosts(posts, total) {
        const $tbody = $('#wpaigen-schedule-tbody');

        if (posts.length === 0) {
            $tbody.html('<tr><td colspan="7" class="text-center">No scheduled articles found.</td></tr>');
            $('#wpaigen-pagination').hide();
            return;
        }

        let html = '';
        posts.forEach(post => {
            html += renderPostRow(post);
        });

        $tbody.html(html);

        // Show load more button if there are more posts
        if (total > posts.length) {
            $('#wpaigen-pagination').show();
        } else {
            $('#wpaigen-pagination').hide();
        }
    }

    function appendScheduledPosts(posts) {
        const $tbody = $('#wpaigen-schedule-tbody');

        posts.forEach(post => {
            $tbody.append(renderPostRow(post));
        });
    }

    function renderPostRow(post) {
        const statusClass = getStatusClass(post.status);
        const statusText = getStatusText(post.status);
        const actions = getActionButtons(post);

        return `
            <tr>
                <td>${escapeHtml(post.keyword)}</td>
                <td>${capitalizeFirst(post.language)}</td>
                <td>${post.length} words</td>
                <td>${capitalizeFirst(post.tone)}</td>
                <td>${formatDateTime(post.scheduled_date)}</td>
                <td><span class="wpaigen-status ${statusClass}">${statusText}</span></td>
                <td>${actions}</td>
            </tr>
        `;
    }

    function getStatusClass(status) {
        const classes = {
            'pending': 'status-pending',
            'processing': 'status-processing',
            'published': 'status-published',
            'failed': 'status-failed',
            'cancelled': 'status-cancelled'
        };
        return classes[status] || '';
    }

    function getStatusText(status) {
        const texts = {
            'pending': 'In Queue',
            'processing': 'Processing',
            'published': 'Published',
            'failed': 'Failed',
            'cancelled': 'Cancelled'
        };
        return texts[status] || status;
    }

    function getActionButtons(post) {
        let actions = '';

        if (post.status === 'published' && post.post_id) {
            actions += `<a href="${wpaigen_ajax_object.admin_url}post.php?post=${post.post_id}&action=edit" class="button button-small">Edit</a> `;
        }

        if (post.status === 'pending' || post.status === 'failed') {
            actions += `<button type="button" class="button button-small wpaigen-cancel-schedule" data-id="${post.id}">Cancel</button> `;
        }

        if (post.status === 'failed' && post.error_message) {
            actions += `<button type="button" class="button button-small wpaigen-view-error" data-error="${escapeHtml(post.error_message)}">View Error</button> `;
        }

        return actions;
    }

    function updateStats() {
        const statuses = ['pending', 'processing', 'published', 'failed'];

        statuses.forEach(status => {
            const data = {
                action: 'wpaigen_get_scheduled_posts',
                nonce: wpaigen_ajax_object.nonce,
                status: status,
                limit: 1,
                offset: 0
            };

            $.get(wpaigen_ajax_object.ajax_url, data, function(response) {
                if (response.success) {
                    $(`#${status}-count`).text(response.data.total);
                }
            });
        });
    }

    function showScheduleLoading(show) {
        if (show) {
            $('#wpaigen-schedule-loading').css('display', 'flex');
        } else {
            $('#wpaigen-schedule-loading').css('display', 'none');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString();
    }

    // --- Event Delegation for Schedule Page ---
    $(document).on('click', '.wpaigen-cancel-schedule', function() {
        const id = $(this).data('id');

        if (!confirm('Are you sure you want to cancel this scheduled article?')) {
            return;
        }

        const data = {
            action: 'wpaigen_delete_schedule',
            nonce: wpaigen_ajax_object.nonce,
            schedule_id: id
        };

        $.post(wpaigen_ajax_object.ajax_url, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                loadScheduledPosts();
                updateStats();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });

    $(document).on('click', '.wpaigen-view-error', function() {
        const error = $(this).data('error');
        alert('Error: ' + error);
    });

    // --- Initialize based on current page ---
    if ($('#wpaigen-generate-form').length > 0) {
        initGoogleTrends();
        initScheduling();
    }

    if ($('.wpaigen-schedule-page').length > 0) {
        initSchedulePage();
    }
});
