<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="wrap wpaigen-license-page">
  <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <div class="wpaigen-header">
    <div class="wpaigen-logo">Manage License</div>
    <p>Activate or validate your WPaigen Pro license.</p>
  </div>

  <div class="wpaigen-license-content">
    <div class="wpaigen-license-left">
      <div class="wpaigen-card">
        <h2>License Key Management</h2>
        <form id="wpaigen-license-form">
          <div class="wpaigen-form-group">
            <label for="wpaigen-license-key">License Key:</label>
            <input type="text" id="wpaigen-license-key" name="license_key"
              placeholder="Enter your WPaigen Pro License Key"
              value="<?php echo esc_attr( get_option('wpaigen_license_key', '') ); ?>" required>
          </div>
          <button type="submit" id="wpaigen-validate-license-btn" class="button button-primary wpaigen-button">Activate
            License</button>
          <div id="wpaigen-license-message" class="wpaigen-message"></div>
        </form>
        <p class="license-status-text">Current Plan: <strong
            id="current-plan-display"><?php echo esc_html( ucfirst( get_option( 'wpaigen_license_type', 'Free' ) ) ); ?></strong>
        </p>
      </div>
    </div>

    <div class="wpaigen-license-right">
      <div class="wpaigen-card wpaigen-contact-card">
        <h2><span class="dashicons dashicons-info"></span> Need Assistance?</h2>
        <p>Contact us for support or inquiries.</p>
        <div class="contact-info">
          <p><span class="dashicons dashicons-admin-users"></span> <strong>Name:</strong> Taruna Wahyudi</p>
          <p><span class="dashicons dashicons-email"></span> <strong>Email:</strong> <a
              href="mailto:wahyuditaruna97@gmail.com">wahyuditaruna97@gmail.com</a></p>
          <p><span class="dashicons dashicons-admin-site"></span> <strong>Website:</strong> <a
              href="https://tarunawahyudi.github.io" target="_blank">https://tarunawahyudi.github.io</a></p>
          <p><span class="dashicons dashicons-whatsapp"></span> <strong>Telegram:</strong> <a
              href="tg://resolve?phone=6287876220034">087876220034</a></p>
        </div>
      </div>
    </div>
  </div>

  <div
    class="wpaigen-section-upgrade <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'pro' ) ? 'wpaigen-hidden' : ''; ?>">
    <div class="wpaigen-card wpaigen-pro-banner">
      <h2>Upgrade to Pro</h2>
      <p>Unlock all features and enjoy unlimited generation with WPaigen Pro.</p>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpaigen' ) ); ?>"
        class="button button-primary wpaigen-button">View Pro Benefits</a>
    </div>
  </div>

</div>