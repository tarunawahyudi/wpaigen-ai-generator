<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="wrap wpaigen-generate-page">
  <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <div class="wpaigen-header">
    <div class="wpaigen-logo">Generate Article</div>
    <p>Effortlessly create content with AI assistance.</p>
  </div>

  <div class="wpaigen-grid-2-col">
    <div class="wpaigen-card">
      <h2>Article Generation</h2>
      <form id="wpaigen-generate-form">
        <div class="wpaigen-form-group">
          <label for="wpaigen-keyword">Keyword:</label>
          <input type="text" id="wpaigen-keyword" name="keyword" placeholder="e.g., Kopi Indonesia, Pola Hidup Sehat"
            required>
        </div>

        <div class="wpaigen-form-group">
          <label for="wpaigen-language">Language:</label>
          <select id="wpaigen-language" name="language">
            <option value="indonesian">Indonesia</option>
            <option value="english">English</option>
            <option value="spanish" class="pro-feature" disabled>Spanish (Pro)</option>
            <option value="french" class="pro-feature" disabled>French (Pro)</option>
            <option value="german" class="pro-feature" disabled>German (Pro)</option>
            <option value="mandarin" class="pro-feature" disabled>Mandarin Chinese (Pro)</option>
            <option value="arabic" class="pro-feature" disabled>Arabic (Pro)</option>
            <option value="portuguese" class="pro-feature" disabled>Portuguese (Pro)</option>
            <option value="russian" class="pro-feature" disabled>Russian (Pro)</option>
            <option value="japanese" class="pro-feature" disabled>Japanese (Pro)</option>
            <option value="korean" class="pro-feature" disabled>Korean (Pro)</option>
            <option value="javanese" class="pro-feature" disabled>Javanese (Pro)</option>
          </select>
          <span
            class="pro-info language-info <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'free' ) ? '' : 'wpaigen-hidden'; ?>">
            <span class="dashicons dashicons-lock"></span> Upgrade to Pro for more languages.
          </span>
        </div>

        <div class="wpaigen-form-group">
          <label for="wpaigen-length">Word Count:</label>
          <input type="number" id="wpaigen-length" name="length" min="50" max="200" value="200" required>
          <span
            class="pro-info length-info <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'free' ) ? '' : 'wpaigen-hidden'; ?>">
            Max 200 words for Free version. <br> <span class="dashicons dashicons-lock"></span> Upgrade to Pro for
            unlimited.
          </span>
        </div>

        <div class="wpaigen-form-group">
          <label for="wpaigen-tone">Writing Style:</label>
          <select id="wpaigen-tone" name="tone">
            <option value="professional">Professional</option>
            <option value="casual" class="pro-feature" disabled>Casual (Pro)</option>
            <option value="seo" class="pro-feature" disabled>SEO-Optimized (Pro)</option>
            <option value="persuasive" class="pro-feature" disabled>Persuasive (Pro)</option>
            <option value="narrative" class="pro-feature" disabled>Narrative (Pro)</option>
            <option value="news" class="pro-feature" disabled>News (Pro)</option>
          </select>
          <span
            class="pro-info tone-info <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'free' ) ? '' : 'wpaigen-hidden'; ?>">
            <span class="dashicons dashicons-lock"></span> Unlock more styles in Pro.
          </span>
        </div>

        <div class="wpaigen-form-group checkbox-group">
          <input type="checkbox" id="wpaigen-use-featured-image" name="use_featured_image" checked>
          <label for="wpaigen-use-featured-image">Include Featured Image</label>
        </div>

        <button type="submit" id="wpaigen-generate-btn" class="button button-primary wpaigen-button">Generate
          Article</button>
        <div id="wpaigen-generate-message" class="wpaigen-message"></div>
      </form>
    </div>

    <div class="wpaigen-sidebar">
      <div class="wpaigen-card wpaigen-tips-card">
        <h2>Pro-Tips for Optimal Content</h2>
        <ul>
          <li>
            <span class="dashicons dashicons-lightbulb"></span>
            <strong>Keyword Selection:</strong> Choose relevant and specific keywords to maximize SEO impact.
          </li>
          <li>
            <span class="dashicons dashicons-groups"></span>
            <strong>Target Audience:</strong> Tailor your writing style to resonate with your target readers for better
            engagement.
          </li>
          <li>
            <span class="dashicons dashicons-edit"></span>
            <strong>Writing Style:</strong> Experiment with different tones to find a unique voice for your brand.
          </li>
        </ul>
      </div>

      <div
        class="wpaigen-card wpaigen-cta-card <?php echo ( get_option( 'wpaigen_license_type', 'free' ) === 'pro' ) ? 'wpaigen-hidden' : ''; ?>">
        <h2>🚀 Boost Your Content Strategy!</h2>
        <p>Upgrade to WPaigen Pro for unlimited access to advanced features and unleash your full content creation
          potential.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpaigen' ) ); ?>"
          class="button button-secondary wpaigen-button">Upgrade to Pro</a>
      </div>
    </div>
  </div>

  <div id="wpaigen-loading-overlay" class="wpaigen-loading-overlay">
    <div class="wpaigen-loading-spinner">
      <div class="spinner-border"></div>
      <p id="wpaigen-loading-text">Generating Article...</p>
    </div>
  </div>

</div>