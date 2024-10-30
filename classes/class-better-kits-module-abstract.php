<?php
/**
 * The abstract class for Better Kits Modules.
 *
 * @link       https://www.kitforest.com
 * @since      1.0.0
 *
 * @package    Better_Kits
 * @subpackage Better_Kits/classes
 */

abstract class Better_Kits_Module_Abstract {

	/**
	 * Flags if module should autoload.
	 */
	public $auto = false;


	/**
	 * Better_Kits_Module_Abstract constructor.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->slug = str_replace( '_', '-', strtolower( str_replace( '_better_kits_Module', '', get_class( $this ) ) ) );
	}

	/**
	 * Registers the loader.
	 * And setup activate and deactivate hooks. Added in v2.3.3.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param Better_Kits_Loader $loader The loader class used to register action hooks and filters.
	 */
	public function register_loader( Better_Kits_Loader $loader ) {
		$this->loader = $loader;
		$this->loader->better_kits_add_action( $this->get_slug() . '_activate', $this, 'activate' );
		$this->loader->better_kits_add_action( $this->get_slug() . '_deactivate', $this, 'deactivate' );
	}

	/**
	 * Getter method for slug.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return mixed|string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Registers the loader.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param Better_Kits_Model $model The loader class used to register action hooks and filters.
	 */
	public function register_model( Better_Kits_Model $model ) {
		$this->model = $model;
	}
	

	/**
	 * Method to check if module status is active.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return bool
	 */
	final public function get_is_active() {
		if ( $this->auto === true ) {
			return true;
		}
		if ( ! isset( $this->model ) ) {
			return false;
		}
		return $this->model->get_is_module_active( $this->slug, $this->active_default );
	}
}
