<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="wrap wpaigen-dashboard">
  <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <div class="wpaigen-header">
    <div class="wpaigen-logo">WPaigen<span>AI Generator</span></div>
    <p>Crafting Content with AI Power.</p>
  </div>

  <div class="wpaigen-grid">
    <div class="wpaigen-card wpaigen-usage-card">
      <h2>Usage Overview</h2>
      <div class="usage-stats">
        <div class="stat-item">
          <span class="stat-label">Usage Today</span>
          <span class="stat-value" id="wpaigen-usage-today">
            <?php echo esc_html( get_option( 'wpaigen_usage_today', 0 ) ); ?>
          </span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Daily Limit</span>
          <span class="stat-value" id="wpaigen-daily-limit">
            <?php
                            $limit = (int) get_option( 'wpaigen_daily_limit', 2 );
                            echo $limit === -1 ? 'Unlimited' : esc_html( $limit );
                        ?>
          </span>
        </div>
        <div class="stat-item">
          <span class="stat-label">Current Plan</span>
          <span class="stat-value wpaigen-plan-badge" id="wpaigen-current-plan">
            <?php echo esc_html( ucfirst( get_option( 'wpaigen_license_type', 'Free' ) ) ); ?>
          </span>
        </div>
      </div>
    </div>

    <div class="wpaigen-card wpaigen-quick-start-card">
      <h2>Get Started Instantly</h2>
      <p>Generate high-quality articles and SEO-optimized content in minutes with the power of AI.</p>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpaigen-generate' ) ); ?>"
        class="button button-primary wpaigen-button">Start Generating Now</a>
    </div>
  </div>

  <div
    class="wpaigen-section-upgrade <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'pro' ) ? 'wpaigen-hidden' : ''; ?>">
    <div class="wpaigen-card wpaigen-pro-banner">
      <h2>Unlock Unlimited Potential with WPaigen Pro</h2>
      <div class="pro-benefits">
        <ul>
          <li><span class="dashicons dashicons-yes"></span> Unlimited daily generation</li>
          <li><span class="dashicons dashicons-yes"></span> Advanced writing styles</li>
          <li><span class="dashicons dashicons-yes"></span> Auto SEO optimization</li>
          <li><span class="dashicons dashicons-yes"></span> Priority support</li>
          <li><span class="dashicons dashicons-yes"></span> Scheduled publishing <span class="coming-soon">(Coming
              Soon)</span></li>
        </ul>
      </div>
      <div class="pro-cta">
        <span class="pro-price">Rp. 50.000 / Lifetime License</span>
        <button id="wpaigen-btn-get-pro" class="button button-primary wpaigen-button">Get Pro License</button>
      </div>
    </div>
  </div>

    <div id="wpaigen-email-modal" class="wpaigen-modal">
        <div class="wpaigen-modal-content">
            <span class="wpaigen-modal-close">&times;</span>

            <div class="wpaigen-modal-header-section">
                <h3 class="wpaigen-product-title">WP AI Generator Pro</h3>
                <p class="wpaigen-product-tagline">Unlock unlimited AI-powered content generation</p>
            </div>

            <div class="wpaigen-price-section">
                <span class="wpaigen-price">Rp 50.000</span> <p class="wpaigen-price-note">One-time payment • Lifetime access</p> </div>

            <div class="wpaigen-benefits-section">
                <ul class="wpaigen-benefits-list">
                    <li><span class="wpaigen-icon-check dashicons dashicons-yes-alt"></span> Unlimited daily generation</li>
                    <li><span class="wpaigen-icon-check dashicons dashicons-yes-alt"></span> Advanced writing styles (6 styles)</li>
                    <li><span class="wpaigen-icon-check dashicons dashicons-yes-alt"></span> Multiple languages (8 languages)</li>
                    <li><span class="wpaigen-icon-check dashicons dashicons-yes-alt"></span> Unlimited word count</li>
                    <li><span class="wpaigen-icon-check dashicons dashicons-yes-alt"></span> Priority support</li>
                </ul>
            </div>

            <div class="wpaigen-form-section">
                <label for="wpaigen-email-input" class="wpaigen-label">Email Address</label>
                <input type="email" id="wpaigen-email-input" placeholder="your.email@example.com" required>
                <p class="wpaigen-email-helper-text">We'll send your license key to this email.</p>
                <button id="wpaigen-email-submit" class="button button-primary wpaigen-button" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px;">
                    <span class="dashicons dashicons-privacy"></span> Proceed to Payment
                </button>
                <div id="wpaigen-modal-message" class="wpaigen-message"></div>
            </div>

            <div class="wpaigen-secure-payment-info">
                <p class="wpaigen-secure-text">Secure payment powered by Midtrans. Your payment information is encrypted.</p>
            </div>
        </div>
    </div>

    <div id="wpaigen-success-modal" class="wpaigen-modal wpaigen-modal-success">
        <div class="wpaigen-modal-content">
            <h2 class="success-title">Payment Successful! 🎉</h2>
            <p class="success-message">Awesome! Your Pro license has been activated. Please check your <strong>inbox and spam folder</strong> at <strong id="success-email-display"></strong> for your
                license key and further instructions.</p>
            <button class="button button-primary wpaigen-button wpaigen-success-button wpaigen-modal-success-close">Got It!</button>
        </div>
    </div>

</div>