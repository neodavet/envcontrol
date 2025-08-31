<?php
/**
 * Plugin Name: Environment Control
 * Description: Control WordPress settings based on environment detection (production vs non-production). Includes search engine indexing control and provides a framework for implementing custom environment-based settings.
 * Author: neodavet
 * Author URI: https://neodavet.github.io/davetportfolio/
 * Version: 1.0
 * Text Domain: envcontrol
 * Tags: environment, production, development, staging, settings, indexing, framework
 * Requires at least: 6.8
 * Tested up to: 6.8
 * Stable tag: 1.0
 * Requires PHP: 5.6
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('ENV_CONTROL_VERSION', '1.0');
define('ENV_CONTROL_OPTION', 'env_control_settings');

/**
 * Initialize the plugin
 */
function env_control_init() {
    // Add admin menu
    add_action('admin_menu', 'env_control_admin_menu');
    
    // Register settings
    add_action('admin_init', 'env_control_register_settings');
    
    // Add settings link to plugins page
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'env_control_settings_link');
}
add_action('init', 'env_control_init');

/**
 * Add admin menu under Tools
 */
function env_control_admin_menu() {
    add_management_page(
        'Environment Control',
        'Environment Control',
        'manage_options',
        'env-control',
        'env_control_admin_page'
    );
}

/**
 * Register plugin settings
 */
function env_control_register_settings() {
    register_setting(
        'env_control_settings',
        ENV_CONTROL_OPTION,
        array(
            'type' => 'object',
            'sanitize_callback' => 'env_control_sanitize_settings',
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
function env_control_sanitize_settings($input) {
    $sanitized = array();
    
    if (isset($input['production_url'])) {
        $sanitized['production_url'] = esc_url_raw(trim($input['production_url']));
        
        // Ensure URL has trailing slash for consistency
        if (!empty($sanitized['production_url']) && substr($sanitized['production_url'], -1) !== '/') {
            $sanitized['production_url'] .= '/';
        }
    }
    
    // Sanitize the disable when plugin disabled setting
    $sanitized['disable_when_plugin_disabled'] = isset($input['disable_when_plugin_disabled']) ? (bool) $input['disable_when_plugin_disabled'] : false;
    
    return $sanitized;
}

/**
 * Get plugin settings
 */
function env_control_get_settings() {
    $defaults = array(
        'production_url' => 'https://www.yoursite.com/',
        'disable_when_plugin_disabled' => true
    );
    
    $settings = get_option(ENV_CONTROL_OPTION, array());
    return wp_parse_args($settings, $defaults);
}

/**
 * Get production URL from settings
 */
function env_control_get_production_url() {
    $settings = env_control_get_settings();
    return $settings['production_url'];
}

/**
 * Admin page content
 */
function env_control_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $settings = env_control_get_settings();
    $current_url = home_url('/');
    $is_production = env_control_is_production_environment();
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="env-control-intro">
            <p><strong>Environment Control</strong> automatically manages WordPress settings based on your environment (production vs non-production). This plugin serves as a framework that you can extend to implement custom environment-based controls for any WordPress setting.</p>
        </div>
        
        <div class="env-control-status">
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
                            <span style="color: blue; font-weight: bold;">✓ Will allow search engines when plugin is deactivated</span>
                        <?php else: ?>
                            <span style="color: orange; font-weight: bold;">⚠ Will allow search engines when plugin is deactivated</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('env_control_settings');
            do_settings_sections('env_control_settings');
            ?>
            
            <div class="env-control-settings">
                <h2>Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="env_production_url">Production URL</label>
                        </th>
                        <td>
                            <input type="url" 
                                   id="env_production_url" 
                                   name="<?php echo esc_attr(ENV_CONTROL_OPTION); ?>[production_url]" 
                                   value="<?php echo esc_attr($settings['production_url']); ?>" 
                                   class="regular-text"
                                   placeholder="https://www.yoursite.com/"
                                   required />
                            <p class="description">
                                Enter the production URL. This URL will be compared against the current site URL to determine the environment and control settings accordingly.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="env_disable_when_plugin_disabled">Plugin Disabled Behavior</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                    id="env_disable_when_plugin_disabled" 
                                       name="<?php echo esc_attr(ENV_CONTROL_OPTION); ?>[disable_when_plugin_disabled]" 
                                       value="1" 
                                       <?php checked($settings['disable_when_plugin_disabled'], true); ?> />
                                Explicitly allow search engines when plugin is disabled
                            </label>
                            <p class="description">
                                When enabled, search engines will be explicitly allowed when this plugin is deactivated. When disabled, search engines will still be allowed when the plugin is deactivated (safe default behavior).
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php submit_button('Save Settings'); ?>
        </form>
        
        <div class="env-control-info">
            <h2>How It Works</h2>
            <p>This plugin automatically controls WordPress settings based on environment detection:</p>
            <ul>
                <li><strong>Production Environment:</strong> When the current URL matches the production URL OR when <code>WP_ENV</code> is set to 'production', the site operates in production mode with search engine indexing allowed.</li>
                <li><strong>Non-Production Environment:</strong> When the current URL doesn't match the production URL OR when <code>WP_ENV</code> is not 'production', the "Discourage search engines from indexing this site" option is automatically enforced.</li>
                <li><strong>Plugin Disabled Behavior:</strong> When the plugin is deactivated, search engines will be allowed by default to prevent accidental blocking of production sites.</li>
            </ul>
            
            <h3>Environment Detection Priority</h3>
            <ol>
                <li>If <code>WP_ENV</code> constant is defined, it takes priority over URL matching</li>
                <li>If <code>WP_ENV</code> is not defined, the plugin compares the current URL with the production URL</li>
            </ol>
            
            <h3>Extensibility Framework</h3>
            <p>This plugin provides a foundation for implementing custom environment-based controls:</p>
            <ul>
                <li><strong>Hook into environment detection:</strong> Use <code>env_control_is_production_environment()</code> in your custom code</li>
                <li><strong>Add custom settings:</strong> Extend the settings array and admin interface</li>
                <li><strong>Control any WordPress option:</strong> Use filters similar to the search engine indexing implementation</li>
                <li><strong>Environment-specific features:</strong> Enable/disable functionality based on environment</li>
            </ul>
            
            <h3>Developer Functions</h3>
            <ul>
                <li><code>env_control_is_production_environment()</code> - Check if current environment is production</li>
                <li><code>env_control_get_settings()</code> - Get plugin settings</li>
                <li><code>env_control_get_production_url()</code> - Get configured production URL</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Add settings link to plugins page
 */
function env_control_settings_link($links) {
    $settings_link = '<a href="' . admin_url('tools.php?page=env-control') . '">' . __('Settings', 'envcontrol') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/**
 * Check if current environment is production
 * 
 * @return boolean True if production, false otherwise
 */
function env_control_is_production_environment() {
    // Check WP_ENV if defined
    if (defined('WP_ENV')) {
        return WP_ENV === 'production';
    }   
    else {
        // Check HOME URL against production URL from settings
        $production_url = env_control_get_production_url();
        $current_url = home_url('/');

        return $current_url === $production_url;
    }
}

/**
 * Filter the value of the 'blog_public' option based on environment check.
 */
add_filter('pre_option_blog_public', function($default) {
    if (!env_control_is_production_environment()) {
        return 0; // Force discourage search engines
    }
    else {
        return 1; // Allow search engines in production
    }
});

/**
 * Display a notice in the admin area if we're not in production.
 */
add_action('admin_notices', function() {
    if (!env_control_is_production_environment()) {
        $message = '<strong>' . __('Environment Control Notice:', 'envcontrol') . '</strong> ';
        
        if (defined('WP_ENV') && WP_ENV !== 'production') {
            /* translators: %s: The current WP_ENV value (e.g., development, staging) */
            $message .= sprintf(__('WP_ENV is set to %s.', 'envcontrol'), '<code>' . esc_html(WP_ENV) . '</code>');
        } else {
            $message .= __('Current URL does not match production URL.', 'envcontrol');
        }
        
        $message .= ' ' . __('Search engine indexing is automatically disabled.', 'envcontrol');
        
        echo '<div class="notice notice-info"><p>' . wp_kses_post($message) . '</p></div>';
    }
});

// Backward compatibility function for any existing code that might use this
function is_production_environment() {
    return env_control_is_production_environment();
}

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'env_control_activate');

function env_control_activate() {
    // When plugin is activated, apply the current environment logic
    if (env_control_is_production_environment()) {
        update_option('blog_public', '1'); // Allow search engines
    } else {
        update_option('blog_public', '0'); // Discourage search engines
    }
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'env_control_deactivate');

function env_control_deactivate() {
    $settings = env_control_get_settings();
    
    // Always allow search engines when plugin is deactivated (safe default)
    // This prevents accidental blocking of production sites
    update_option('blog_public', '1'); // Allow search engines
}
