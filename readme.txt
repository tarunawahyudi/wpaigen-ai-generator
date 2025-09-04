=== WPaigen AI Generator ===
Contributors: taruna97
Donate Link: https://buymeacoffee.com/tarunawahyudi
Tags: ai, content generator, article generator, seo, content creation
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WPaigen AI Generator: AI-powered plugin for generating high-quality, SEO-optimized articles and content directly in your WP dashboard.

== Description ==

WPaigen AI Generator is a powerful **WP plugin** that leverages advanced Artificial Intelligence to help you generate high-quality, SEO-optimized articles and content directly within your **WP dashboard**. Save countless hours on content creation, boost your productivity, and enhance your website's search engine rankings with ease.

This plugin is designed for bloggers, content marketers, SEO specialists, and anyone who needs to produce engaging and relevant content efficiently. From generating full articles based on a single keyword to fine-tuning language and tone, WPaigen AI Generator puts the power of AI at your fingertips.

**Key Features:**

* **AI-Powered Article Generation:** Create unique, human-like articles from just a keyword.
* **SEO Optimization:** Automatically generate content that is optimized for search engines, helping you rank higher.
* **Customizable Content:**
    * Control article length to suit your requirements.
    * Choose between Indonesian and English languages.
    * Select a professional writing tone.
    * Option to include a featured image automatically.
* **Intuitive Dashboard:** A clean, user-friendly interface to manage your content generation.
    * Monitor daily usage and limits.
    * Track your current plan (Free or Pro).
    * Prominent display for Pro version features and offers.
* **Seamless Pro Upgrade:** Easily upgrade to WPaigen Pro for unlimited access to all features. <-- Simplified "WPaigen AI Generator Pro" to "WPaigen Pro"
    * Secure payment processing integrated with Midtrans.
* **Free Version Available:** Start generating content immediately with our generous free plan.

**Unlock more potential with WPaigen Pro:** <-- Simplified "WPaigen AI Generator Pro" to "WPaigen Pro"

* Unlimited daily article generation.
* Access to multiple writing styles (e.g., Creative, Formal, Conversational).
* Support for more languages (e.g., up to 50+ languages, if applicable).
* Unlimited word count per article.
* Advanced SEO features.
* Priority customer support.
* And much more!

WPaigen AI Generator is the ultimate tool to streamline your content strategy.

== Installation ==

1.  **Upload the plugin files** to the `/wp-content/plugins/wpaigen-ai-generator` directory, or install the plugin through the **WP plugins screen** directly.
2.  **Activate the plugin** through the 'Plugins' screen in your **WP admin area**.
3.  Navigate to **WPaigen AI Generator > Dashboard** in your **WP admin menu** to start generating content.
4.  For Pro features, click 'Get Pro Version' on the Dashboard and follow the instructions to upgrade.

---

== External Services ==

The WPaigen AI Generator plugin utilizes external services to provide its core functionality and secure payment processing. Here are the details regarding these services:

### 1. WPaigen API (Plugin Backend Service) <-- Changed to simply "WPaigen API"

This plugin connects to our custom backend API, the **WPaigen API**, for user license management and AI content generation functionality.

* **What the service is and what it is used for:**
    * The **WPaigen API** is a backend service provided by the plugin developer (Taruna Wahyudi) to process license-related requests, manage feature usage, and generate AI content (articles) based on user input.
    * **API URL:** `https://wp-ai-generator-api-production.up.railway.app`

* **What data is sent and when:**
    * **Free License Registration:** When a user registers for a free license, your **email** and **site domain** are sent to the WPaigen API to generate and record the license key.
    * **License Activation:** When you activate a license (both free and Pro), your **license key** and **site domain** are sent to validate and activate the license on your site.
    * **License Validation:** Periodically, or when certain features are accessed, your **license key** (as an Authorization header) and **site domain** are sent to validate the license status. This ensures the plugin operates according to your license plan and adheres to usage limits.
    * **Article Generation:** When you request to generate an article, the **keyword, language, length (word count), and tone** you selected are sent to the WPaigen API. This data is used by the AI model on the backend to generate the requested content.
    * **Plugin Updates:** The plugin makes calls to the API to check for available updates, sending the **plugin slug** and current **plugin version**.
    * **Plugin Information:** The plugin may retrieve general information about itself from the API.
    * **Transaction Creation:** When you initiate a purchase for the Pro version, your **email** and **site domain** are sent to create a transaction record.
    * **Contact Us:** If you use the contact form within the plugin, your **name, email, and message** will be sent to the API for communication purposes.
    * **Free Version Download:** When a user downloads the free version, their **email** is sent for recording and distribution purposes.

* **Privacy Policy and Terms of Service:**
    * For information regarding data handling and service terms related to WPaigen API, please refer to our dedicated pages:
        * **WPaigen API Terms of Service:** [https://wpaigen.stacklab.id/terms](https://wpaigen.stacklab.id/terms)
        * **WPaigen API Privacy Policy:** [https://wpaigen.stacklab.id/privacy](https://wpaigen.stacklab.id/privacy)

### 2. Midtrans Snap.js (Payment Service)

For secure payment processing when you upgrade to WPaigen Pro, the plugin integrates a third-party payment service. <-- Changed to simply "WPaigen Pro"

* **What the service is and what it is used for:**
    * **Midtrans Snap.js** is a JavaScript library from Midtrans, a payment gateway provider. It is used to initialize and display a secure payment interface (payment widget) directly on your site, allowing you to make payments for Pro upgrades without leaving your **WP dashboard**.
    * **Service URL:** `https://api.midtrans.com/snap/snap.js`

* **What data is sent and when:**
    * When you choose to upgrade to the Pro version and click the payment button, Midtrans Snap.js is loaded.
    * Transaction information such as the **payment amount, item details (e.g., WPaigen Pro subscription), and basic customer information (name, email, site domain – which you previously provided within the plugin)** are sent to Midtrans servers via Snap.js to process the payment. This includes data necessary for transaction identification and processing through various methods offered by Midtrans (e.g., bank transfer, credit card, e-wallets, etc.).
    * Once the payment is complete, Midtrans will send a **callback** (payment status notification) to the WPaigen API (`/api/transactions/midtrans/callback`) to confirm the transaction and automatically activate your license. The data sent in this callback includes `order_id`, `transaction_status`, `payment_type`, `settlement_time`, and other details relevant to the payment status.

* **Privacy Policy and Terms of Service:**
    * **Midtrans** is a leading third-party payment service provider in Indonesia. You can review their privacy policy and terms of service on their official website:
        * **Midtrans Terms of Service:** [https://midtrans.com/promoenginetnc](https://midtrans.com/promoenginetnc)
        * **Midtrans Privacy Policy:** [https://midtrans.com/privacy-notice](https://midtrans.com/privacy-notice)

---

== Frequently Asked Questions ==

= What is WPaigen AI Generator? =
WPaigen AI Generator is a **WP plugin** that uses Artificial Intelligence to create articles and other content directly on your **WP site**.

= Is there a free version available? =
Yes, WPaigen AI Generator offers a free version with daily generation limits and basic features. You can download and use it to try out the core functionalities.

= What are the limitations of the free version? =
The free version typically allows for 2 articles per day, with a maximum of 200 words per article, and supports Indonesian & English languages with a professional writing style. These limits can be adjusted by the developer.

= How do I upgrade to the Pro version? =
You can upgrade to the Pro version directly from the WPaigen AI Generator dashboard in your **WP admin area**. Click on the 'Get Pro Version' button and follow the secure payment process.

= What payment methods are supported for the Pro version? =
The Pro version uses Midtrans for secure payment processing, which supports various local and international payment methods.

= Does the plugin support multiple languages? =
The free version supports Indonesian and English. The Pro version unlocks support for many more languages.

= How does SEO optimization work? =
The AI algorithm is designed to generate content with relevant keywords and optimal structure to improve its search engine visibility. For advanced SEO features, the Pro version is recommended.

= Where can I get support? =
For free version users, you can submit issues on the [WordPress.org support forum](https://wordpress.org/support/plugin/wpaigen-ai-generator/). Pro users receive priority support directly from our team.

= What kind of content can I generate? =
Primarily, the plugin generates articles and blog posts. Its capabilities will expand with future updates.

---

== Changelog ==

= 1.0.0 =
* **Initial Release of WPaigen AI Generator.**
* **Core AI Generation Functionality:**
    * Ability to generate articles based on a single keyword input.
    * User control over generated article length (word count).
    * Language selection option for generated content (Indonesian, English).
    * Tone selection for article output (Professional).
    * Option to automatically fetch and include a featured image for the generated post.
* **Admin Dashboard Overview:**
    * Revamped Dashboard Layout: Implemented a new, modern CSS Grid-based layout for the main dashboard.
        * "Unlock Unlimited Potential (Pro Version)" section is now positioned on the right side, spanning two rows.
        * "Usage Overview" and "Get Started Instantly" cards are stacked vertically on the left side.
        * Optimized for immediate visibility of the "Get Pro License Now" button without scrolling.
    * Usage Tracking & Limits: Displays current daily usage and the total daily generation limit (for Free users) or "Unlimited" (for Pro users).
    * Current Plan Display: Shows the user's current plan status (Free or Pro).
    * Pro Promotion Countdown: Integrated a dynamic JavaScript countdown timer for limited-time offers on the Pro version.
        * Displays remaining days, hours, minutes, and seconds.
        * Automatically hides the countdown and adjusts pricing/button text when the promotion ends.
* **Pro Version Upgrade Flow:**
    * "Get Pro License Now" button on the dashboard to initiate the upgrade process.
    * Email input modal for collecting user information before payment.
    * Secure payment integration with Midtrans for seamless transactions.
    * Success modal to confirm payment and provide license key instructions.
* **License Management:**
    * Dedicated "License" page for users to activate/validate their license keys.
    * Updates plugin capabilities and dashboard stats based on license validation.
* **Loading Overlays & Messages:**
    * Global loading overlay to indicate ongoing processes (e.g., generation, payment, validation).
    * Dynamic message display system (success, error, info) for user feedback.
* **Security:**
    * Implemented robust Nonces for all AJAX requests to protect against Cross-Site Request Forgery (CSRF) attacks.
    * Input sanitization on server-side processing for user-submitted data.

---

== Upgrade Notice ==

= 2.1.0 =
Add domain field for create transaction pro license

= 2.0.0 =
This update introduces enhanced license management, ensuring one license per domain, and includes important security updates for Midtrans integration. Significant internal refactoring also improves plugin performance and stability. It is highly recommended to update to this version.

= 1.0.0 =
This is the initial release of WPaigen AI Generator. No upgrade is necessary from previous versions. Install and activate to start generating content!
