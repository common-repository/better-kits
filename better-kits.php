<?php
/**
 * Plugin Name: Better Kits
 * Plugin URI: http://kitforest.com
 * Description: Better Kits is an Elementor templates library and allows you to select from over 100s of designs to choose from.
 * Version: 1.0.3
 * Author: kitforest
 * Author URI: http://kitforest.com
 * Text Domain: better-kits
 *
 * @package Better Kits
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Better_Kits {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'better-kits';

		$this->version = '1.0.0';

		$this->Better_Kits_load_dependencies();
		$this->Better_Kits_prepare_modules();
		$this->Better_Kits_define_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function Better_Kits_load_dependencies() {
		$this->loader = new Better_Kits_Loader();

	}

	/**
	 * Check Modules and register them.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function Better_Kits_prepare_modules() {
		$global_settings = new Better_Kits_Global_Settings();
		$modules_to_load = $global_settings->Better_Kits_instance()->get_modules();
		$better_kits_model      = new Better_Kits_Model();

		$module_factory = new Better_Kits_Module_Factory();
		foreach ( $modules_to_load as $module_name ) {
			$module = $module_factory::build( $module_name );
			if ( $module === false ) {
				continue;
			}
			$global_settings->register_module_reference( $module_name, $module );
			if ( $module->Better_Kits_enable_module() ) {
				$module->register_loader( $this->get_loader() );
				$module->register_model( $better_kits_model );
				if ( $module->get_is_active() ) {
					$module->better_kits_hooks(); // @codeCoverageIgnore
				}
				$this->loader->better_kits_add_action( 'better_kits_modules', $module, 'load' );
			}
		}
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Better_Kits_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register all of the hooks related to the functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function Better_Kits_define_hooks() {

		$plugin_admin = new Better_Kits_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->better_kits_add_action( 'admin_menu', $plugin_admin, 'better_kits_menu_pages' );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function better_kits_run() {
		$this->loader->better_kits_run();
	}

}

class Better_Kits_Loader {

	/**
	 * The array of actions registered with WordPress.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 */
	protected $filters;

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 */
	public function better_kits_add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->better_kits_add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 */
	public function better_kits_add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->better_kits_add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 */
	private function better_kits_add( $better_kits_hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$better_kits_hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $better_kits_hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function better_kits_run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_better_kits() {
	define( 'better_kits_URL', plugins_url( '/', __FILE__ ) );
	define( 'better_kits_path', dirname( __FILE__ ) );
	$plugin = new Better_Kits();
	$plugin->better_kits_run();
}

/**
 * Plugin internationalization.
 *
 * @since   1.0.0
 */
function load_better_kits_textdomain() {

	load_plugin_textdomain(
		'better-kits',
		false,
		better_kits_path . '/languages/'
	);

}
add_action( 'init', 'load_better_kits_textdomain' );


/**
 * Required classes.
 *
 * @since   1.0.0
 */
require 'classes/better-kits-classes.php';
require 'classes/class-better-kits-module-abstract.php';
require 'classes/class-better-kits-module.php';
require_once( dirname( __FILE__ ) . '/classes/class-page-templates.php' );
require_once( dirname( __FILE__ ) . '/classes/class-full-width-templates.php' );

/**
 * The start of the app.
 *
 * @since   1.0.0
 */
run_better_kits();