<?php
/**
 * Plugin Name: Env Control
 * Description: Control WordPress settings based on environment detection (production vs non-production). Includes search engine indexing control and provides a framework for implementing custom environment-based settings.
 * Author: neodavet
 * Author URI: https://neodavet.github.io/davetportfolio/
 * Version: 1.0.1
 * Text Domain: envcontrol
 * Tags: environment, production, development, staging, settings, indexing, framework
 * Requires at least: 6.8
 * Tested up to: 7.0
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package EnvControl
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'ENV_CONTROL_VERSION', '1.0.1' );
define( 'ENV_CONTROL_OPTION', 'env_control_settings' );

/**
 * Initialize the plugin
 */
function env_control_init() {
	// Add admin menu.
	add_action( 'admin_menu', 'env_control_admin_menu' );

	// Register settings.
	add_action( 'admin_init', 'env_control_register_settings' );

	// Add settings link to plugins page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'env_control_settings_link' );
}
add_action( 'init', 'env_control_init' );

/**
 * Add admin menu under Tools
 */
function env_control_admin_menu() {
	add_management_page(
		__( 'Environment Control', 'envcontrol' ),
		__( 'Environment Control', 'envcontrol' ),
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
			'type'              => 'object',
			'sanitize_callback' => 'env_control_sanitize_settings',
			'default'           => array(
				'production_url'               => 'https://www.yoursite.com/',
				'disable_when_plugin_disabled' => true,
			),
		)
	);
}

/**
 * Sanitize settings.
 *
 * @param array $input Raw settings input.
 * @return array Sanitized settings.
 */
function env_control_sanitize_settings( $input ) {
	$sanitized = array();

	if ( isset( $input['production_url'] ) ) {
		$sanitized['production_url'] = esc_url_raw( trim( $input['production_url'] ) );

		// Ensure URL has trailing slash for consistency.
		if ( ! empty( $sanitized['production_url'] ) && substr( $sanitized['production_url'], -1 ) !== '/' ) {
			$sanitized['production_url'] .= '/';
		}
	}

	// Sanitize the disable when plugin disabled setting.
	$sanitized['disable_when_plugin_disabled'] = isset( $input['disable_when_plugin_disabled'] ) ? (bool) $input['disable_when_plugin_disabled'] : false;

	return $sanitized;
}

/**
 * Get plugin settings
 */
function env_control_get_settings() {
	$defaults = array(
		'production_url'               => 'https://www.yoursite.com/',
		'disable_when_plugin_disabled' => true,
	);

	$settings = get_option( ENV_CONTROL_OPTION, array() );
	return wp_parse_args( $settings, $defaults );
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
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings      = env_control_get_settings();
	$current_url   = home_url( '/' );
	$is_production = env_control_is_production_environment();

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="env-control-intro">
			<p>
				<?php
				echo wp_kses(
					__( '<strong>Environment Control</strong> automatically manages WordPress settings based on your environment (production vs non-production). This plugin serves as a framework that you can extend to implement custom environment-based controls for any WordPress setting.', 'envcontrol' ),
					array( 'strong' => array() )
				);
				?>
			</p>
		</div>

		<div class="env-control-status">
			<h2><?php esc_html_e( 'Current Status', 'envcontrol' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Current URL:', 'envcontrol' ); ?></th>
					<td><code><?php echo esc_html( $current_url ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Production URL:', 'envcontrol' ); ?></th>
					<td><code><?php echo esc_html( $settings['production_url'] ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Environment Status:', 'envcontrol' ); ?></th>
					<td>
						<?php if ( $is_production ) : ?>
							<span style="color: green; font-weight: bold;">✓ <?php esc_html_e( 'Production Environment', 'envcontrol' ); ?></span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗ <?php esc_html_e( 'Non-Production Environment', 'envcontrol' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Search Engine Indexing:', 'envcontrol' ); ?></th>
					<td>
						<?php if ( $is_production ) : ?>
							<span style="color: green; font-weight: bold;">✓ <?php esc_html_e( 'Allowed', 'envcontrol' ); ?></span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗ <?php esc_html_e( 'Discouraged (Auto-enforced)', 'envcontrol' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Plugin Disabled Behavior:', 'envcontrol' ); ?></th>
					<td>
						<?php if ( $settings['disable_when_plugin_disabled'] ) : ?>
							<span style="color: blue; font-weight: bold;">✓ <?php esc_html_e( 'Will allow search engines when plugin is deactivated', 'envcontrol' ); ?></span>
						<?php else : ?>
							<span style="color: orange; font-weight: bold;">⚠ <?php esc_html_e( 'Will keep current indexing setting unchanged when plugin is deactivated', 'envcontrol' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'env_control_settings' );
			do_settings_sections( 'env_control_settings' );
			?>

			<div class="env-control-settings">
				<h2><?php esc_html_e( 'Configuration', 'envcontrol' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="env_production_url"><?php esc_html_e( 'Production URL', 'envcontrol' ); ?></label>
						</th>
						<td>
							<input type="url"
									id="env_production_url"
									name="<?php echo esc_attr( ENV_CONTROL_OPTION ); ?>[production_url]"
									value="<?php echo esc_attr( $settings['production_url'] ); ?>"
									class="regular-text"
									placeholder="https://www.yoursite.com/"
									required />
							<p class="description">
								<?php esc_html_e( 'Enter the production URL. This URL will be compared against the current site URL to determine the environment and control settings accordingly.', 'envcontrol' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="env_disable_when_plugin_disabled"><?php esc_html_e( 'Plugin Disabled Behavior', 'envcontrol' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox"
									id="env_disable_when_plugin_disabled"
										name="<?php echo esc_attr( ENV_CONTROL_OPTION ); ?>[disable_when_plugin_disabled]"
										value="1"
										<?php checked( $settings['disable_when_plugin_disabled'], true ); ?> />
								<?php esc_html_e( 'Explicitly allow search engines when plugin is disabled', 'envcontrol' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When enabled, search engines will be explicitly allowed when this plugin is deactivated. When disabled, the current indexing setting is left unchanged on deactivation, so non-production sites stay discouraged from indexing.', 'envcontrol' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<?php submit_button( __( 'Save Settings', 'envcontrol' ) ); ?>
		</form>

		<div class="env-control-info">
			<h2><?php esc_html_e( 'How It Works', 'envcontrol' ); ?></h2>
			<p><?php esc_html_e( 'This plugin automatically controls WordPress settings based on environment detection:', 'envcontrol' ); ?></p>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Production Environment:</strong> When the current URL matches the production URL OR when <code>WP_ENV</code> is set to \'production\', the site operates in production mode with search engine indexing allowed.', 'envcontrol' ),
						array(
							'strong' => array(),
							'code'   => array(),
						)
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Non-Production Environment:</strong> When the current URL doesn\'t match the production URL OR when <code>WP_ENV</code> is not \'production\', the "Discourage search engines from indexing this site" option is automatically enforced.', 'envcontrol' ),
						array(
							'strong' => array(),
							'code'   => array(),
						)
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Plugin Disabled Behavior:</strong> When the plugin is deactivated, search engines will be allowed by default to prevent accidental blocking of production sites.', 'envcontrol' ),
						array( 'strong' => array() )
					);
					?>
				</li>
			</ul>

			<h3><?php esc_html_e( 'Environment Detection Priority', 'envcontrol' ); ?></h3>
			<ol>
				<li>
					<?php
					echo wp_kses(
						__( 'If <code>WP_ENV</code> constant is defined, it takes priority over URL matching', 'envcontrol' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( 'If <code>WP_ENV</code> is not defined, the plugin compares the current URL with the production URL', 'envcontrol' ),
						array( 'code' => array() )
					);
					?>
				</li>
			</ol>

			<h3><?php esc_html_e( 'Extensibility Framework', 'envcontrol' ); ?></h3>
			<p><?php esc_html_e( 'This plugin provides a foundation for implementing custom environment-based controls:', 'envcontrol' ); ?></p>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Hook into environment detection:</strong> Use <code>env_control_is_production_environment()</code> in your custom code', 'envcontrol' ),
						array(
							'strong' => array(),
							'code'   => array(),
						)
					);
					?>
				</li>
				<li><?php esc_html_e( 'Add custom settings: Extend the settings array and admin interface', 'envcontrol' ); ?></li>
				<li><?php esc_html_e( 'Control any WordPress option: Use filters similar to the search engine indexing implementation', 'envcontrol' ); ?></li>
				<li><?php esc_html_e( 'Environment-specific features: Enable/disable functionality based on environment', 'envcontrol' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Developer Functions', 'envcontrol' ); ?></h3>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<code>env_control_is_production_environment()</code> - Check if current environment is production', 'envcontrol' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<code>env_control_get_settings()</code> - Get plugin settings', 'envcontrol' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<code>env_control_get_production_url()</code> - Get configured production URL', 'envcontrol' ),
						array( 'code' => array() )
					);
					?>
				</li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Add settings link to plugins page.
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function env_control_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'tools.php?page=env-control' ) . '">' . __( 'Settings', 'envcontrol' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

/**
 * Check if current environment is production
 *
 * @return boolean True if production, false otherwise
 */
function env_control_is_production_environment() {
	// Check WP_ENV if defined.
	if ( defined( 'WP_ENV' ) ) {
		return WP_ENV === 'production';
	} else {
		// Check HOME URL against production URL from settings.
		$production_url = env_control_get_production_url();
		$current_url    = home_url( '/' );

		return $current_url === $production_url;
	}
}

/**
 * Filter the value of the 'blog_public' option based on environment check.
 */
add_filter(
	'pre_option_blog_public',
	function () {
		if ( ! env_control_is_production_environment() ) {
			return 0; // Force discourage search engines.
		} else {
			return 1; // Allow search engines in production.
		}
	}
);

/**
 * Display a notice in the admin area if we're not in production.
 * This notice self-dismisses when the environment becomes production.
 */
add_action(
	'admin_notices',
	function () {
		// Only show on plugin settings page and dashboard to avoid overwhelming users.
		$current_screen = get_current_screen();
		if ( ! $current_screen || ! in_array( $current_screen->id, array( 'dashboard', 'tools_page_env-control' ), true ) ) {
			return;
		}

		if ( ! env_control_is_production_environment() ) {
			$message = '<strong>' . __( 'Environment Control Notice:', 'envcontrol' ) . '</strong> ';

			if ( defined( 'WP_ENV' ) && WP_ENV !== 'production' ) {
				/* translators: %s: The current WP_ENV value (e.g., development, staging) */
				$message .= sprintf( __( 'WP_ENV is set to %s.', 'envcontrol' ), '<code>' . esc_html( WP_ENV ) . '</code>' );
			} else {
				$message .= __( 'Current URL does not match production URL.', 'envcontrol' );
			}

			$message .= ' ' . __( 'Search engine indexing is automatically disabled.', 'envcontrol' );
			$message .= ' <a href="' . admin_url( 'tools.php?page=env-control' ) . '">' . __( 'Configure Environment Control', 'envcontrol' ) . '</a>';

			echo '<div class="notice notice-info"><p>' . wp_kses_post( $message ) . '</p></div>';
		}
	}
);


/**
 * Plugin activation hook
 */
register_activation_hook( __FILE__, 'env_control_activate' );

/**
 * Runs on plugin activation to apply the current environment logic immediately.
 */
function env_control_activate() {
	// When plugin is activated, apply the current environment logic.
	if ( env_control_is_production_environment() ) {
		update_option( 'blog_public', '1' ); // Allow search engines.
	} else {
		update_option( 'blog_public', '0' ); // Discourage search engines.
	}
}

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, 'env_control_deactivate' );

/**
 * Runs on plugin deactivation.
 *
 * Restores search engine visibility only when the "disable_when_plugin_disabled"
 * setting is enabled; otherwise leaves the current blog_public value untouched
 * so non-production sites stay discouraged from indexing after deactivation.
 */
function env_control_deactivate() {
	$settings = env_control_get_settings();

	if ( $settings['disable_when_plugin_disabled'] ) {
		update_option( 'blog_public', '1' ); // Allow search engines.
	}
}
