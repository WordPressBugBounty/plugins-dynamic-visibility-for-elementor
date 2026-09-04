<?php
namespace DynamicVisibilityForElementor;

use DynamicVisibilityForElementor\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Main Plugin Class
 *
 * @since 0.0.1
 */
class Plugin {

	/**
	 * @var \DynamicVisibilityForElementor\Controls
	 */
	public $controls;
	/**
	 * @var \DynamicVisibilityForElementor\Wpml
	 */
	public $wpml;
	/**
	 * @var \DynamicVisibilityForElementor\AdminPages\Notices
	 */
	public $notices;
	/**
	 * @var \DynamicVisibilityForElementor\Modules\QueryControl\Module
	 */
	public $query_control;

	protected static $instance;

	public function __construct() {
		self::$instance = $this;

		$this->wpml = new Wpml();
		$this->controls = new Controls();
		$this->notices = new AdminPages\Notices();
		$this->query_control = new \DynamicVisibilityForElementor\Modules\QueryControl\Module();

		add_action( 'init', function () {
			load_plugin_textdomain( 'dynamic-visibility-for-elementor' );

			// Promo
			if ( current_user_can( 'install_plugins' ) ) {
				$this->get_promo_notice_dashboard();
			}

			// Plugin page links (must be after load_plugin_textdomain)
			add_filter('plugin_action_links_' . DVE_PLUGIN_BASE, [
				$this,
				'add_action_links',
			]);
			add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		} );

		add_action( 'elementor/init', [ $this, 'add_dve_to_elementor' ] );

		// Admin Style
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
	}

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			new self();
		}
		return self::$instance;
	}

	/**
	 * @return void
	 */
	public function add_dve_to_elementor() {
		add_action('elementor/frontend/after_register_styles', function () {
			wp_register_style(
				'dce-dynamic-visibility-style',
				DVE_URL . 'assets/css/dynamic-visibility.css',
				[],
				DVE_VERSION
			);
			// Enqueue Visibility Style
			wp_enqueue_style( 'dce-dynamic-visibility-style' );
		});

		add_action('wp_enqueue_scripts', function () {
			wp_register_script(
				'dce-visibility',
				DVE_URL . 'assets/js/visibility.js',
				[ 'jquery' ],
				DVE_VERSION,
				true
			);
		});

		// DCE Custom Icons - in Elementor Editor
		add_action('elementor/preview/enqueue_styles', function () {
			wp_register_style(
				'dce-preview',
				DVE_URL . 'assets/css/preview.css',
				[],
				DVE_VERSION
			);
			// Enqueue DCE Elementor Style
			wp_enqueue_style( 'dce-preview' );
		});

		add_action('elementor/editor/after_enqueue_scripts', [
			$this,
			'dce_editor',
		]);

		// Controls
		add_action('elementor/controls/controls_registered', [
			$this->controls,
			'on_controls_registered',
		]);

		$visibility_extension = new Extensions\DynamicVisibility\Manager();
		if ( method_exists( $visibility_extension, 'run_once' ) ) {
			$visibility_extension->run_once();
		}
	}

	/**
	 * @return void
	 */
	public function enqueue_admin() {
		// Style
		wp_register_style(
			'dve-admin-css',
			DVE_URL . 'assets/css/admin.css',
			[],
			DVE_VERSION
		);
		wp_enqueue_style( 'dve-admin-css' );

		// Scripts
		wp_enqueue_script(
			'dve-admin-js',
			DVE_URL . 'assets/js/admin.js',
			[],
			DVE_VERSION,
			true
		);
	}

	/**
	 * @return bool
	 */
	private function is_novamira_installed() {
		return defined( 'NOVAMIRA_VERSION' );
	}

	/**
	 * @return void
	 */
	public function get_promo_notice_dashboard() {
		if ( $this->is_novamira_installed() ) {
			$this->get_dynamic_content_promo_notice();
			return;
		}

		$msg = sprintf(
			__(
				'%1$sYour AI agent builds inside your WordPress.%2$s',
				'dynamic-visibility-for-elementor'
			) . '<br />',
			'<strong>',
			'</strong>'
		);
		$msg .= sprintf(
			__(
				'Novamira gives your AI full access to PHP execution, WP-CLI commands, database queries, and file edits. Real features shipped on your dev and staging sites. %1$sDiscover Novamira &rarr;%2$s',
				'dynamic-visibility-for-elementor'
			),
			'<a target="_blank" rel="noopener noreferrer" href="https://novamira.ai/?utm_source=wp-plugins&utm_campaign=dynamic-visibility&utm_medium=wp-dash-promo"><strong>',
			'</strong></a>'
		);
		$msg .= '<br />';
		$msg .= sprintf(
			__(
				'Want memory and expertise for your AI? Save 10&#37; on %1$sNovamira Pro%2$s with promo code %3$sVISIBILITY%4$s.',
				'dynamic-visibility-for-elementor'
			),
			'<a target="_blank" rel="noopener noreferrer" href="https://novamira.ai/pro/?utm_source=wp-plugins&utm_campaign=dynamic-visibility&utm_medium=wp-dash-promo">',
			'</a>',
			'<strong>',
			'</strong>'
		);
		$this->notices->info( $msg, 'novamira_intro' );
	}

	/**
	 * @return void
	 */
	private function get_dynamic_content_promo_notice() {
		$msg = sprintf(
			__(
				'%1$sBuy now Dynamic.ooo - Dynamic Content for Elementor%2$s and save 10&#37; using promo code %3$sVISIBILITY%4$s.',
				'dynamic-visibility-for-elementor'
			) . '<br />',
			'<a target="_blank" rel="noopener noreferrer" href="https://www.dynamic.ooo/upgrade/visibility-to-premium?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash-promo">',
			'</a>',
			'<strong>',
			'</strong>'
		);
		$msg .= __(
			'Get over 150 features for Elementor that save you time and money when building complex websites. Dynamic Content supports ACF Free and Pro, JetEngine, Meta Box, WooCommerce, WPML, Search & Filter Pro, Pods, and Toolset.',
			'dynamic-visibility-for-elementor'
		);
		$this->notices->info( $msg, 'upgrade_10' );
	}

	/**
	 * @return array<mixed>
	 */
	public function add_action_links( $links ) {
		$my_links[] = sprintf(
			'<a href="https://www.dynamic.ooo/upgrade/visibility-to-premium?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash" target="_blank"">' .
				__( 'Go Premium', 'dynamic-visibility-for-elementor' ) .
				'</a>'
		);
		return array_merge( $links, $my_links );
	}

	/**
	 * @return array<mixed>
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if (
			'dynamic-visibility-for-elementor/dynamic-visibility-for-elementor.php' ===
			$plugin_file
		) {
			$row_meta = [
				'docs' =>
					'<a href="https://dnmc.ooo/visibilitydoc" aria-label="' .
					esc_attr(
						__(
							'View Dynamic Visibility Documentation',
							'dynamic-visibility-for-elementor'
						)
					) .
					'" target="_blank">' .
					__( 'Docs', 'dynamic-visibility-for-elementor' ) .
					'</a>',
				'community' =>
					'<a href="http://facebook.com/groups/dynamic.ooo" aria-label="' .
					esc_attr(
						__(
							'Facebook Community',
							'dynamic-visibility-for-elementor'
						)
					) .
					'" target="_blank">' .
					__( 'FB Community', 'dynamic-visibility-for-elementor' ) .
					'</a>',
			];

			$plugin_meta = array_merge( $plugin_meta, $row_meta );
		}

		return $plugin_meta;
	}

	/**
	 * @return void
	 */
	public function dce_editor() {
		// Register styles
		wp_register_style(
			'dce-icons',
			DVE_URL . 'assets/css/dce-icon.css',
			[],
			DVE_VERSION
		);
		// Enqueue styles Icons
		wp_enqueue_style( 'dce-icons' );

		// Register styles
		wp_register_style(
			'dce-editor',
			DVE_URL . 'assets/css/editor.css',
			[],
			DVE_VERSION
		);
		wp_enqueue_style( 'dce-editor' );

		wp_register_script(
			'dce-script-editor',
			DVE_URL . 'assets/js/editor.js',
			[],
			DVE_VERSION
		);
		wp_enqueue_script( 'dce-script-editor' );

		wp_register_script(
			'dce-editor-visibility',
			DVE_URL . 'assets/js/editor-dynamic-visibility.js',
			[],
			DVE_VERSION
		);
		wp_enqueue_script( 'dce-editor-visibility' );

		// select2
		wp_enqueue_style(
			'dce-select2',
			DVE_URL . 'assets/lib/select2/select2.min.css',
			[],
			DVE_VERSION
		);
		wp_enqueue_script(
			'dce-select2',
			DVE_URL . 'assets/lib/select2/select2.full.min.js',
			[ 'jquery' ],
			DVE_VERSION,
			true
		);
	}
}

Plugin::instance();
