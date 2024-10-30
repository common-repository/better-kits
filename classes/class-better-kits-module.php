<?php

/**
 * The Better Kits Template Directory Module.
 *
 * @link       https://www.kitforest.com
 * @since      1.0.0
 *
 * @package    Template_Directory_Better_Kits_Module
 */

/**
 * The class defines a new module to be used by Better Kits plugin.
 */
class Template_Directory_Better_Kits_Module extends Better_Kits_Module_Abstract {

	/**
	 * Template_Directory_Better_Kits_Module constructor.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
		$this->name           = __( 'Better Kits Template Directory Module', 'better-kits' );
		$this->description    = __( 'The awesome template directory is aiming to provide a wide range of templates that you can import straight into your website.', 'better-kits' );
		$this->active_default = true;
	}

	/**
	 * Determine if module should be loaded.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return bool
	 */
	public function Better_Kits_enable_module() {
		return true;
	}

	/**
	 * The loading logic for the module.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function load() {
		return true;
	}

	/**
	 * Method to define hooks needed.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function better_kits_hooks() {
		// Get the full-width pages feature
		$this->loader->better_kits_add_action( 'init', $this, 'better_kits_load_template_directory_library' );
		$this->loader->better_kits_add_action( 'init', $this, 'better_kits_load_full_width_page_templates' );
		$this->loader->better_kits_add_filter( 'better_kits_template_dir_products', $this, 'better_kits_add_page', 20 );
	}

	/**
	 * Add the menu page.
	 *
	 * @param $products
	 *
	 * @return array
	 */
	public function better_kits_add_page( $products ) {
		$better_kits_pages = array(
			'better-kits' => array(
				'directory_page_title' => __( 'Better Kits', 'better-kits' ),
				'page_slug'            => 'better_kits_templates',
			),
		);
		return array_merge( $products, $better_kits_pages );
	}

	/**
	 * If the composer library is present let's try to init.
	 */
	public function better_kits_load_full_width_page_templates() {
		if ( class_exists( '\BetterKits\FullWidthTemplates' ) ) {
			\BetterKits\FullWidthTemplates::instance();
		}
	}

	/**
	 * Call the Templates Directory library
	 */
	public function better_kits_load_template_directory_library() {
		if ( class_exists( '\BetterKits\PageTemplates' ) ) {
			\BetterKits\PageTemplates::instance();
		}
	}
}
