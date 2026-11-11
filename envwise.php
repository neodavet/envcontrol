<?php
/**
 * Plugin Name: Envwise Environment-Based Control
 * Description: Control WordPress settings based on environment detection (production vs non-production). Includes search engine indexing control and provides a framework for implementing custom environment-based settings.
 * Author: David Tavares Cuevas
 * Author URI: https://neodavet.github.io/davetportfolio/
 * Version: 1.1.0
 * Text Domain: envwise
 * Tags: environment, production, development, staging, settings, indexing, framework
 * Requires at least: 6.8
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (C) 2026 David Tavares Cuevas
 *
 * @package Envwise
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'ENVWISE_VERSION', '1.1.0' );
define( 'ENVWISE_OPTION', 'envwise_settings' );

/**
 * Initialize the plugin
 */
function envwise_init() {
	// Add admin menu.
	add_action( 'admin_menu', 'envwise_admin_menu' );

	// Register settings.
	add_action( 'admin_init', 'envwise_register_settings' );

	// Add settings link to plugins page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'envwise_settings_link' );
}
add_action( 'init', 'envwise_init' );

/**
 * Add admin menu under Tools
 */
function envwise_admin_menu() {
	add_management_page(
		__( 'Envwise', 'envwise' ),
		__( 'Envwise', 'envwise' ),
		'manage_options',
		'envwise',
		'envwise_admin_page'
	);
}

/**
 * Register plugin settings
 */
function envwise_register_settings() {
	register_setting(
		'envwise_settings',
		ENVWISE_OPTION,
		array(
			'type'              => 'object',
			'sanitize_callback' => 'envwise_sanitize_settings',
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
function envwise_sanitize_settings( $input ) {
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
function envwise_get_settings() {
	$defaults = array(
		'production_url'               => 'https://www.yoursite.com/',
		'disable_when_plugin_disabled' => true,
	);

	$settings = get_option( ENVWISE_OPTION, array() );
	return wp_parse_args( $settings, $defaults );
}

/**
 * Get production URL from settings
 */
function envwise_get_production_url() {
	$settings = envwise_get_settings();
	return $settings['production_url'];
}

/**
 * Admin page content
 */
function envwise_admin_page() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings      = envwise_get_settings();
	$current_url   = home_url( '/' );
	$is_production = envwise_is_production_environment();

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="envwise-intro">
			<p>
				<?php
				echo wp_kses(
					__( '<strong>Envwise</strong> automatically manages WordPress settings based on your environment (production vs non-production). This plugin serves as a framework that you can extend to implement custom environment-based controls for any WordPress setting.', 'envwise' ),
					array( 'strong' => array() )
				);
				?>
			</p>
		</div>

		<div class="envwise-status">
			<h2><?php esc_html_e( 'Current Status', 'envwise' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Current URL:', 'envwise' ); ?></th>
					<td><code><?php echo esc_html( $current_url ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Production URL:', 'envwise' ); ?></th>
					<td><code><?php echo esc_html( $settings['production_url'] ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Environment Status:', 'envwise' ); ?></th>
					<td>
						<?php if ( $is_production ) : ?>
							<span style="color: green; font-weight: bold;">✓ <?php esc_html_e( 'Production Environment', 'envwise' ); ?></span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗ <?php esc_html_e( 'Non-Production Environment', 'envwise' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Search Engine Indexing:', 'envwise' ); ?></th>
					<td>
						<?php if ( $is_production ) : ?>
							<span style="color: green; font-weight: bold;">✓ <?php esc_html_e( 'Allowed', 'envwise' ); ?></span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗ <?php esc_html_e( 'Discouraged (Auto-enforced)', 'envwise' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Plugin Disabled Behavior:', 'envwise' ); ?></th>
					<td>
						<?php if ( $settings['disable_when_plugin_disabled'] ) : ?>
							<span style="color: blue; font-weight: bold;">✓ <?php esc_html_e( 'Will allow search engines when plugin is deactivated', 'envwise' ); ?></span>
						<?php else : ?>
							<span style="color: orange; font-weight: bold;">⚠ <?php esc_html_e( 'Will apply the current environment\'s indexing setting when plugin is deactivated (non-production stays discouraged)', 'envwise' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</div>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'envwise_settings' );
			do_settings_sections( 'envwise_settings' );
			?>

			<div class="envwise-settings">
				<h2><?php esc_html_e( 'Configuration', 'envwise' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="envwise_production_url"><?php esc_html_e( 'Production URL', 'envwise' ); ?></label>
						</th>
						<td>
							<input type="url"
									id="envwise_production_url"
									name="<?php echo esc_attr( ENVWISE_OPTION ); ?>[production_url]"
									value="<?php echo esc_attr( $settings['production_url'] ); ?>"
									class="regular-text"
									placeholder="https://www.yoursite.com/"
									required />
							<p class="description">
								<?php esc_html_e( 'Enter the production URL. This URL will be compared against the current site URL to determine the environment and control settings accordingly.', 'envwise' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="envwise_disable_when_plugin_disabled"><?php esc_html_e( 'Plugin Disabled Behavior', 'envwise' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox"
									id="envwise_disable_when_plugin_disabled"
										name="<?php echo esc_attr( ENVWISE_OPTION ); ?>[disable_when_plugin_disabled]"
										value="1"
										<?php checked( $settings['disable_when_plugin_disabled'], true ); ?> />
								<?php esc_html_e( 'Explicitly allow search engines when plugin is disabled', 'envwise' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When enabled, search engines will be explicitly allowed when this plugin is deactivated. When disabled, the indexing setting matching the current environment is saved on deactivation, so non-production sites stay discouraged from indexing.', 'envwise' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<?php submit_button( __( 'Save Settings', 'envwise' ) ); ?>
		</form>

		<div class="envwise-info">
			<h2><?php esc_html_e( 'How It Works', 'envwise' ); ?></h2>
			<p><?php esc_html_e( 'This plugin automatically controls WordPress settings based on environment detection:', 'envwise' ); ?></p>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Production Environment:</strong> When the current URL matches the production URL OR when <code>WP_ENV</code> is set to \'production\', the site operates in production mode with search engine indexing allowed.', 'envwise' ),
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
						__( '<strong>Non-Production Environment:</strong> When the current URL doesn\'t match the production URL OR when <code>WP_ENV</code> is not \'production\', the "Discourage search engines from indexing this site" option is automatically enforced.', 'envwise' ),
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
						__( '<strong>Plugin Disabled Behavior:</strong> When the plugin is deactivated, search engines are allowed by default to prevent accidental blocking of production sites. If you turn this option off, the setting for the current environment is saved instead (non-production stays discouraged).', 'envwise' ),
						array( 'strong' => array() )
					);
					?>
				</li>
			</ul>

			<h3><?php esc_html_e( 'Environment Detection Priority', 'envwise' ); ?></h3>
			<ol>
				<li>
					<?php
					echo wp_kses(
						__( 'If <code>WP_ENV</code> constant is defined, it takes priority over URL matching', 'envwise' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( 'If <code>WP_ENV</code> is not defined, the plugin compares the current URL with the production URL', 'envwise' ),
						array( 'code' => array() )
					);
					?>
				</li>
			</ol>

			<h3><?php esc_html_e( 'Extensibility Framework', 'envwise' ); ?></h3>
			<p><?php esc_html_e( 'This plugin provides a foundation for implementing custom environment-based controls:', 'envwise' ); ?></p>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<strong>Hook into environment detection:</strong> Use <code>envwise_is_production_environment()</code> in your custom code', 'envwise' ),
						array(
							'strong' => array(),
							'code'   => array(),
						)
					);
					?>
				</li>
				<li><?php echo wp_kses( __( '<strong>Add custom settings:</strong> Extend the settings array and admin interface', 'envwise' ), array( 'strong' => array() ) ); ?></li>
				<li><?php echo wp_kses( __( '<strong>Control any WordPress option:</strong> Use filters similar to the search engine indexing implementation', 'envwise' ), array( 'strong' => array() ) ); ?></li>
				<li><?php echo wp_kses( __( '<strong>Environment-specific features:</strong> Enable/disable functionality based on environment', 'envwise' ), array( 'strong' => array() ) ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Developer Functions', 'envwise' ); ?></h3>
			<ul>
				<li>
					<?php
					echo wp_kses(
						__( '<code>envwise_is_production_environment()</code> - Check if current environment is production', 'envwise' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<code>envwise_get_settings()</code> - Get plugin settings', 'envwise' ),
						array( 'code' => array() )
					);
					?>
				</li>
				<li>
					<?php
					echo wp_kses(
						__( '<code>envwise_get_production_url()</code> - Get configured production URL', 'envwise' ),
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
function envwise_settings_link( $links ) {
	$settings_link = '<a href="' . admin_url( 'tools.php?page=envwise' ) . '">' . __( 'Settings', 'envwise' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

/**
 * Check if current environment is production
 *
 * @return boolean True if production, false otherwise
 */
function envwise_is_production_environment() {
	// Check WP_ENV if defined.
	if ( defined( 'WP_ENV' ) ) {
		return WP_ENV === 'production';
	} else {
		// Check HOME URL against production URL from settings.
		$production_url = envwise_get_production_url();
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
		if ( ! envwise_is_production_environment() ) {
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
		if ( ! $current_screen || ! in_array( $current_screen->id, array( 'dashboard', 'tools_page_envwise' ), true ) ) {
			return;
		}

		if ( ! envwise_is_production_environment() ) {
			$message = '<strong>' . __( 'Envwise Notice:', 'envwise' ) . '</strong> ';

			if ( defined( 'WP_ENV' ) && WP_ENV !== 'production' ) {
				/* translators: %s: The current WP_ENV value (e.g., development, staging) */
				$message .= sprintf( __( 'WP_ENV is set to %s.', 'envwise' ), '<code>' . esc_html( WP_ENV ) . '</code>' );
			} else {
				$message .= __( 'Current URL does not match production URL.', 'envwise' );
			}

			$message .= ' ' . __( 'Search engine indexing is automatically disabled.', 'envwise' );
			$message .= ' <a href="' . admin_url( 'tools.php?page=envwise' ) . '">' . __( 'Configure Envwise', 'envwise' ) . '</a>';

			echo '<div class="notice notice-info"><p>' . wp_kses_post( $message ) . '</p></div>';
		}
	}
);


/**
 * Plugin activation hook
 */
register_activation_hook( __FILE__, 'envwise_activate' );

/**
 * Runs on plugin activation to apply the current environment logic immediately.
 */
function envwise_activate() {
	// Migrate settings saved by versions released under the previous name.
	$legacy_settings = get_option( 'env_control_settings' );
	if ( false !== $legacy_settings && false === get_option( ENVWISE_OPTION ) ) {
		update_option( ENVWISE_OPTION, $legacy_settings );
	}
	delete_option( 'env_control_settings' );

	// When plugin is activated, apply the current environment logic.
	if ( envwise_is_production_environment() ) {
		update_option( 'blog_public', '1' ); // Allow search engines.
	} else {
		update_option( 'blog_public', '0' ); // Discourage search engines.
	}
}

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, 'envwise_deactivate' );

/**
 * Runs on plugin deactivation.
 *
 * When the "disable_when_plugin_disabled" setting is enabled, search engines are
 * always allowed. Otherwise blog_public is synced to the current environment,
 * because the pre_option filter never writes to the database and the stored value
 * may be stale (e.g. a production database cloned to staging).
 */
function envwise_deactivate() {
	$settings = envwise_get_settings();

	if ( $settings['disable_when_plugin_disabled'] || envwise_is_production_environment() ) {
		update_option( 'blog_public', '1' ); // Allow search engines.
	} else {
		update_option( 'blog_public', '0' ); // Keep non-production discouraged.
	}
}
