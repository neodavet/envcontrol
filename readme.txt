=== Environment Privacy Control ===
Contributors: davet86
Tags: environment, privacy, search engines, development, staging
Requires at least: 4.0
Tested up to: 6.7
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically controls search engine indexing based on your environment (production vs development/staging).

== Description ==

A WordPress plugin that automatically controls search engine indexing based on your environment (production vs development/staging).

This plugin prevents accidental search engine indexing of development, staging, or testing WordPress sites by automatically enforcing the "Discourage search engines from indexing this site" option when not in a production environment.

= Features =

* **Automatic Environment Detection**: Detects production vs non-production environments using two methods:
  * WP_ENV constant (takes priority if defined)
  * URL comparison between current site and configured production URL
* **Automatic Privacy Control**: Forces search engine discouragement on non-production environments
* **Production Override**: Allows search engine indexing only on production sites
* **Admin Dashboard**: Provides a clear status overview and configuration interface
* **Production URL Setting**: Define your production site URL for environment comparison
* **Plugin Deactivation Behavior**: Choose whether to restore search engine indexing when the plugin is deactivated
* **Status Dashboard**: Real-time environment status display under Tools menu
* **Visual Indicators**: Clear production/non-production status with color-coded indicators
* **Settings Integration**: Direct settings link from the plugins page
* **Admin Notices**: Warning notifications when running on non-production environments

= Safety Features =

* **Database Protection**: Automatically updates the blog_public option based on environment
* **Activation/Deactivation Hooks**: Properly handles plugin lifecycle events
* **Backward Compatibility**: Maintains compatibility with existing installations

= Use Cases =

* **Development Sites**: Prevent accidental indexing of localhost or development domains
* **Staging Sites**: Protect staging environments from search engines
* **Multi-Environment Workflows**: Seamlessly deploy across different environments
* **Client Sites**: Ensure development work doesn't appear in search results

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-envcontrol/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Tools > WP Environment Privacy Control screen to configure the plugin.

== Frequently Asked Questions ==

= How does the plugin detect environments? =

The plugin uses two methods:
1. WP_ENV constant (recommended) - if defined in wp-config.php
2. URL comparison between current site and configured production URL

= What environments are supported? =

The plugin recognizes 'production' as the production environment. Any other value (development, staging, local, etc.) is treated as non-production.

= Can I use this plugin on multisite? =

Currently, the plugin is designed for single-site installations. Multisite support is planned for future releases.

= What happens when I deactivate the plugin? =

You can configure whether the plugin should restore search engine indexing when deactivated. This setting is available in the plugin configuration.

== Screenshots ==

1. Environment status dashboard showing production environment
2. Environment status dashboard showing development environment
3. Plugin configuration settings
4. Admin notice for non-production environments

== Changelog ==

= 1.0.0 =
* Initial release
* Complete rewrite with improved environment detection
* Added configurable plugin deactivation behavior
* Enhanced admin interface with status dashboard
* Improved URL handling and validation
* Added backward compatibility support

== Configuration ==

= Setting Up Production URL =

1. Navigate to Tools > WP Environment Privacy Control
2. Enter your production site URL (e.g., https://www.yoursite.com/)
3. Configure plugin deactivation behavior
4. Save settings

= Environment Detection Methods =

**Method 1: WP_ENV Constant (Recommended)**
Add this to your wp-config.php:

`// For production
define('WP_ENV', 'production');

// For development/staging
define('WP_ENV', 'development');
// or
define('WP_ENV', 'staging');`

**Method 2: URL Comparison**
If WP_ENV is not defined, the plugin compares:
* Current site URL: home_url('/')
* Configured production URL (from plugin settings)

== Upgrade Notice ==

= 1.0.0 =
Initial release of the rewritten plugin with enhanced features and improved environment detection.
