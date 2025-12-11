<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap wpaigen-schedule-page">
  <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

  <div class="wpaigen-header">
    <div class="wpaigen-logo">Scheduled Articles</div>
    <p>Manage your scheduled article publications.</p>
  </div>

  <div class="wpaigen-filters">
    <div class="wpaigen-filter-group">
      <label for="wpaigen-status-filter">Filter by Status:</label>
      <select id="wpaigen-status-filter">
        <option value="all">All Status</option>
        <option value="pending">Pending</option>
        <option value="processing">Processing</option>
        <option value="published">Published</option>
        <option value="failed">Failed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div class="wpaigen-filter-group">
      <label for="wpaigen-search-input">Search:</label>
      <input type="text" id="wpaigen-search-input" placeholder="Search keyword or tone..." />
    </div>
    <div class="wpaigen-filter-group">
      <label for="wpaigen-per-page">Items per page:</label>
      <select id="wpaigen-per-page">
        <option value="10">10</option>
        <option value="20" selected>20</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
    </div>
    <button type="button" id="wpaigen-refresh-schedules" class="button button-secondary">
      <span class="dashicons dashicons-update"></span> Refresh
    </button>
  </div>

  <div class="wpaigen-card">
    <div class="wpaigen-schedule-stats">
      <div class="stat-card">
        <h3> Pending </h3>
        <span class="stat-number" id="pending-count">0</span>
      </div>
      <div class="stat-card">
        <h3> Processing </h3>
        <span class="stat-number" id="processing-count">0</span>
      </div>
      <div class="stat-card">
        <h3> Published </h3>
        <span class="stat-number" id="published-count">0</span>
      </div>
      <div class="stat-card">
        <h3> Failed </h3>
        <span class="stat-number" id="failed-count">0</span>
      </div>
    </div>
  </div>

  <div class="wpaigen-card">
    <!-- Results Info -->
    <div class="wpaigen-results-info">
      <div class="results-count" id="wpaigen-results-count">
        Showing <span id="wpaigen-showing-from">0</span> - <span id="wpaigen-showing-to">0</span> of <span id="wpaigen-total-items">0</span> items
      </div>
    </div>

    <div class="wpaigen-table-container">
      <table class="wp-list-table widefat fixed striped wpaigen-schedule-table">
        <thead>
          <tr>
            <th scope="col" class="manage-column">Keyword</th>
            <th scope="col" class="manage-column">Language</th>
            <th scope="col" class="manage-column">Word Count</th>
            <th scope="col" class="manage-column">Tone</th>
            <th scope="col" class="manage-column">Scheduled Date</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column">Actions</th>
          </tr>
        </thead>
        <tbody id="wpaigen-schedule-tbody">
          <tr>
            <td colspan="7" class="text-center">Loading scheduled articles...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Proper Pagination -->
    <div class="wpaigen-pagination-wrapper" id="wpaigen-pagination-wrapper" style="display: none;">
      <div class="wpaigen-pagination-controls">
        <button type="button" class="button button-secondary" id="wpaigen-prev-page" disabled>
          <span class="dashicons dashicons-arrow-left-alt2"></span> Previous
        </button>

        <div class="wpaigen-page-numbers" id="wpaigen-page-numbers">
          <!-- Page numbers will be inserted here -->
        </div>

        <button type="button" class="button button-secondary" id="wpaigen-next-page" disabled>
          Next <span class="dashicons dashicons-arrow-right-alt2"></span>
        </button>
      </div>

      <div class="wpaigen-pagination-info">
        Page <span id="wpaigen-current-page">1</span> of <span id="wpaigen-total-pages">1</span>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div class="wpaigen-pagination-loading" id="wpaigen-pagination-loading" style="display: none;">
      <div class="spinner-border"></div>
      <span>Loading...</span>
    </div>
  </div>

  <div id="wpaigen-schedule-loading" class="wpaigen-loading-overlay" style="display: none;">
    <div class="wpaigen-loading-spinner">
      <div class="spinner-border"></div>
      <p>Loading...</p>
    </div>
  </div>

  <!-- Schedule Details Modal -->
  <div id="wpaigen-schedule-modal" class="wpaigen-modal" style="display: none;">
    <div class="wpaigen-modal-content">
      <div class="wpaigen-modal-header">
        <h3>Schedule Details</h3>
        <button type="button" class="wpaigen-modal-close" id="wpaigen-close-schedule-modal">
          <span class="dashicons dashicons-no-alt"></span>
        </button>
      </div>
      <div class="wpaigen-modal-body" id="wpaigen-schedule-details">
        <!-- Schedule details will be loaded here -->
      </div>
    </div>
  </div>
</div>