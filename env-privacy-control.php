<?php
/**
 * Plugin Name: WP Environment Privacy Control
 * Description: Force option of "Discourage search engines from indexing this site" when HOME URL is not production or WP_ENV is not production.
 * Author: neodavet
 * Author URI: https://neodavet.github.io/davetportfolio/
 * Version: 1.0
 * License: GPL2+
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('WP_ENV_PRIVACY_CONTROL_VERSION', '1.0');
define('WP_ENV_PRIVACY_CONTROL_OPTION', 'wp_env_privacy_control_settings');

/**
 * Initialize the plugin
 */
function wp_env_privacy_control_init() {
    // Add admin menu
    add_action('admin_menu', 'wp_env_privacy_control_admin_menu');
    
    // Register settings
    add_action('admin_init', 'wp_env_privacy_control_register_settings');
    
    // Add settings link to plugins page
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_env_privacy_control_settings_link');
}
add_action('init', 'wp_env_privacy_control_init');

/**
 * Add admin menu under Tools
 */
function wp_env_privacy_control_admin_menu() {
    add_management_page(
        'WP Environment Privacy Control',
        'WP Environment Privacy Control',
        'manage_options',
        'wp-env-privacy-control',
        'wp_env_privacy_control_admin_page'
    );
}

/**
 * Register plugin settings
 */
function wp_env_privacy_control_register_settings() {
    register_setting(
        'wp_env_privacy_control_settings',
        WP_ENV_PRIVACY_CONTROL_OPTION,
        array(
            'type' => 'object',
            'sanitize_callback' => 'wp_env_privacy_control_sanitize_settings',
            'default' => array(
                'production_url' => 'https://www.yoursite.com/',
                'disable_when_plugin_disabled' => true
            )
        )
    );
}

/**
 * Sanitize settings
 */
function wp_env_privacy_control_sanitize_settings($input) {
    $sanitized = array();
    
    if (isset($input['production_url'])) {
        $sanitized['production_url'] = esc_url_raw(trim($input['production_url']));
        
        // Ensure URL has trailing slash for consistency
        if (!empty($sanitized['production_url']) && substr($sanitized['production_url'], -1) !== '/') {
            $sanitized['production_url'] .= '/';
        }
    }
    
    // Sanitize the new setting
    $sanitized['disable_when_plugin_disabled'] = isset($input['disable_when_plugin_disabled']) ? (bool) $input['disable_when_plugin_disabled'] : false;
    
    return $sanitized;
}

/**
 * Get plugin settings
 */
function wp_env_privacy_control_get_settings() {
    $defaults = array(
        'production_url' => 'https://www.yoursite.com/',
        'disable_when_plugin_disabled' => true
    );
    
    $settings = get_option(WP_ENV_PRIVACY_CONTROL_OPTION, array());
    return wp_parse_args($settings, $defaults);
}

/**
 * Get production URL from settings
 */
function wp_env_privacy_control_get_production_url() {
    $settings = wp_env_privacy_control_get_settings();
    return $settings['production_url'];
}

/**
 * Admin page content
 */
function wp_env_privacy_control_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $settings = wp_env_privacy_control_get_settings();
    $current_url = home_url('/');
    $is_production = wp_env_privacy_control_is_production_environment();
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="wp-env-privacy-control-status">
            <h2>Current Status</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Current URL:</th>
                    <td><code><?php echo esc_html($current_url); ?></code></td>
                </tr>
                <tr>
                    <th scope="row">Production URL:</th>
                    <td><code><?php echo esc_html($settings['production_url']); ?></code></td>
                </tr>
                <tr>
                    <th scope="row">Environment Status:</th>
                    <td>
                        <?php if ($is_production): ?>
                            <span style="color: green; font-weight: bold;">✓ Production Environment</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">✗ Non-Production Environment</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Search Engine Indexing:</th>
                    <td>
                        <?php if ($is_production): ?>
                            <span style="color: green; font-weight: bold;">✓ Allowed</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">✗ Discouraged (Auto-enforced)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Plugin Disabled Behavior:</th>
                    <td>
                        <?php if ($settings['disable_when_plugin_disabled']): ?>
                            <span style="color: blue; font-weight: bold;">✓ Will disable "Discourage search engines" when plugin is deactivated</span>
                        <?php else: ?>
                            <span style="color: orange; font-weight: bold;">⚠ Will preserve current setting when plugin is deactivated</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_env_privacy_control_settings');
            do_settings_sections('wp_env_privacy_control_settings');
            ?>
            
            <div class="wp-env-privacy-control-settings">
                <h2>Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wp_env_production_url">Production URL</label>
                        </th>
                        <td>
                            <input type="url" 
                                   id="wp_env_production_url" 
                                   name="<?php echo WP_ENV_PRIVACY_CONTROL_OPTION; ?>[production_url]" 
                                   value="<?php echo esc_attr($settings['production_url']); ?>" 
                                   class="regular-text"
                                   placeholder="https://www.yoursite.com/"
                                   required />
                            <p class="description">
                                Enter the production URL. This URL will be compared against the current site URL to determine if search engines should be allowed to index the site.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wp_env_disable_when_plugin_disabled">Plugin Disabled Behavior</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                    id="wp_env_disable_when_plugin_disabled" 
                                       name="<?php echo WP_ENV_PRIVACY_CONTROL_OPTION; ?>[disable_when_plugin_disabled]" 
                                       value="1" 
                                       <?php checked($settings['disable_when_plugin_disabled'], true); ?> />
                                Disable "Discourage search engines" option when plugin is disabled
                            </label>
                            <p class="description">
                                When enabled, if this plugin is deactivated, the "Discourage search engines from indexing this site" option will be automatically disabled (set to allow indexing).
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <div class="wp-env-privacy-control-info">
            <h2>How It Works</h2>
            <p>This plugin automatically controls whether search engines can index your website based on the environment:</p>
            <ul>
                <li><strong>Production Environment:</strong> When the current URL matches the production URL OR when <code>WP_ENV</code> is set to 'production', search engines are allowed to index the site.</li>
                <li><strong>Non-Production Environment:</strong> When the current URL doesn't match the production URL OR when <code>WP_ENV</code> is not 'production', the "Discourage search engines from indexing this site" option is automatically enforced.</li>
                <li><strong>Plugin Disabled Behavior:</strong> When the plugin is deactivated, you can choose whether to automatically disable the "Discourage search engines" option (allowing indexing) or preserve the current setting.</li>
            </ul>
            
            <h3>Priority Order</h3>
            <ol>
                <li>If <code>WP_ENV</code> is defined, it takes priority over URL matching</li>
                <li>If <code>WP_ENV</code> is not defined, the plugin compares the current URL with the production URL</li>
            </ol>
            
            <h3>Plugin Lifecycle</h3>
            <ul>
                <li><strong>Activation:</strong> The plugin immediately applies the environment-based logic to the "Discourage search engines" setting</li>
                <li><strong>Runtime:</strong> The plugin continuously monitors and enforces the correct setting based on the environment</li>
                <li><strong>Deactivation:</strong> Depending on your configuration, the plugin can either disable the "Discourage search engines" option or preserve the current setting</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Add settings link to plugins page
 */
function wp_env_privacy_control_settings_link($links) {
    $settings_link = '<a href="' . admin_url('tools.php?page=wp-env-privacy-control') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * Check if current environment is production
 * 
 * @return boolean True if production, false otherwise
 */
function wp_env_privacy_control_is_production_environment() {
    // Check WP_ENV if defined
    if (defined('WP_ENV')) {
        return WP_ENV === 'production';
    }   
    else {
        // Check HOME URL against production URL from settings
        $production_url = wp_env_privacy_control_get_production_url();
        $current_url = home_url('/');

        return $current_url === $production_url;
    }
}

/**
 * Filter the value of the 'blog_public' option based on environment check.
 */
add_filter('pre_option_blog_public', function($default) {
    global $wpdb;
    if (!wp_env_privacy_control_is_production_environment()) {
        $wpdb->query("UPDATE {$wpdb->prefix}options SET option_value = '0' WHERE option_name = 'blog_public'");
        return 0; // Force discourage search engines
    }
    else {
        $wpdb->query("UPDATE {$wpdb->prefix}options SET option_value = '1' WHERE option_name = 'blog_public'");
        return 1; // Use the actual database value for production
    }
});

/**
 * Display a notice in the admin area if we're not in production.
 */
add_action('admin_notices', function() {
    if (!wp_env_privacy_control_is_production_environment()) {
        $message = '<strong>Warning:</strong> ';
        
        if (defined('WP_ENV') && WP_ENV !== 'production') {
            $message .= 'WP_ENV is set to <code>' . esc_html(WP_ENV) . '</code>. ';
        } else {
            $message .= 'Current URL does not match production URL. ';
        }
        
        $message .= 'The option <em>"Discourage search engines from indexing this site"</em> is automatically enforced.';
        
        echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
    }
});

// Backward compatibility - keep the old function name for any existing code
function is_production_environment() {
    return wp_env_privacy_control_is_production_environment();
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'wp_env_privacy_control_activate');

function wp_env_privacy_control_activate() {
    // When plugin is activated, apply the current environment logic
    if (wp_env_privacy_control_is_production_environment()) {
        update_option('blog_public', '1'); // Allow search engines
    } else {
        update_option('blog_public', '0'); // Discourage search engines
    }
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'wp_env_privacy_control_deactivate');

function wp_env_privacy_control_deactivate() {
    $settings = wp_env_privacy_control_get_settings();
    
    // If the setting is enabled, disable the "discourage search engines" option when plugin is deactivated
    if ($settings['disable_when_plugin_disabled']) {
        update_option('blog_public', '1'); // Allow search engines
    }
    // If the setting is disabled, leave the current blog_public setting as is
}
