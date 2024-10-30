<?php
/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://www.kitforest.com
 * @since      1.0.0
 *
 * @package    Better_Kits
 * @subpackage Better_Kits/classes
 */


class Better_Kits_Admin
{

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * The templates list.
	 *
	 * @return array
	 */
	public function templates_list() {

		$api_home_url = 'https://sites.kitforest.com/';

		$templates_file = $api_home_url . 'wp-json/templates/v1/templates';

		$templates_data_content = json_decode(file_get_contents($templates_file), true);

		$defaults_if_empty = array(
			'title'            => __( 'A new Better Kits', 'better-kits' ),
			'description'      => __( 'Awesome Better Kits', 'better-kits' ),
			'import_file'      => '',
		);
		
		$date = date('Y') . '/' . date('m');

		$upload_dir = wp_get_upload_dir(); // set to save in the /wp-content/uploads folder

		$templates_list = [];

		for($i=0; $i < count($templates_data_content); $i++) {
			$plugins = [];
			if(!empty($templates_data_content[$i]['plugins'])) {
				for($plugins_count=0; $plugins_count < count($templates_data_content[$i]['plugins']); $plugins_count++) {
					$plugins[$templates_data_content[$i]['plugins'][$plugins_count]] = array('title' => __( str_replace("-"," ",$templates_data_content[$i]['plugins'][$plugins_count]), 'better-kits' ));
				}
			}
			$templates_list[$templates_data_content[$i]['slug']] = array(
				'slug'       => $templates_data_content[$i]['slug'],
				'title'       => $templates_data_content[$i]['title'],
				'description' => __( $templates_data_content[$i]['description'], 'better-kits' ),
				'theme_url'   => esc_url('https://wordpress.org/themes/hello-elementor'),
				'demo_url'    => esc_url( $templates_data_content[$i]['url']),
				'screenshot'  => esc_url( $templates_data_content[$i]['thumbnail']),
				'import_file' => esc_url( $upload_dir['baseurl']  .'/'. $date .'/'. $templates_data_content[$i]['slug'] . '.json'),
				'keywords'    => __( strtolower($templates_data_content[$i]['title']) . ', ' . $templates_data_content[$i]['keywords'] ),
				'required_plugins' => $plugins,
				'template_type' => $templates_data_content[$i]['template_type'],
			);
		};

		foreach ( $templates_list as $template => $properties ) {
			$templates_list[ $template ] = wp_parse_args( $properties, $defaults_if_empty );
		};

		return apply_filters( 'template_directory_templates_list', $templates_list );

		print_r(plugin_dir_url( dirname( __FILE__ ) ) . $this->slug . '/js/script.js');
	
	}

	/**
	 * Check plugin state.
	 *
	 * @param string $slug plugin slug.
	 *
	 * @return bool
	 */
	public function check_plugin_state( $slug ) {
		if ( file_exists( WP_CONTENT_DIR . '/plugins/' . $slug . '/' . $slug . '.php' ) || file_exists( WP_CONTENT_DIR . '/plugins/' . $slug . '/index.php' ) ) {
			require_once( ABSPATH . 'wp-admin' . '/includes/plugin.php' );
			$needs = '';
			if( $slug == 'better-elementor-addons' && class_exists( 'Better_Elementor_Elements' ) ) {
				$needs = 'deactivate';
			}elseif( is_plugin_active( $slug . '/' . $slug . '.php' ) ){
				$needs = 'deactivate';
			}elseif( is_plugin_active( $slug . '/index.php' ) ){
				$needs = 'deactivate';
			}else{
				$needs = 'activate';
			}

			return $needs;
		} else {
			return 'install';
		}
	}

	/**
	 * Generate action button html.
	 *
	 * @param string $slug plugin slug.
	 *
	 * @return string
	 */
	public function get_button_html( $slug ) {
		$button = '';
		$state  = $this->check_plugin_state( $slug );
		if ( ! empty( $slug ) ) {
			switch ( $state ) {
				case 'install':
					$nonce  = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'install-plugin',
								'plugin' => $slug,
							),
							network_admin_url( 'update.php' )
						),
						'install-plugin_' . $slug
					);
					$button .= '<a data-slug="' . $slug . '" class="install-now better-kits-install-plugin button button-primary" href="' . esc_url( $nonce ) . '" data-name="' . $slug . '" aria-label="Install ' . $slug . '">' . __( 'Install and activate', 'better-kits' ) . '</a>';
					break;
				case 'activate':
					if($slug == 'better-elementor-addons'){
						$plugin_link_suffix = $slug . '/better-addons.php';
					}else{
						$plugin_link_suffix = $slug . '/' . $slug . '.php';
					}
					$nonce              = add_query_arg(
						array(
							'action'   => 'activate',
							'plugin'   => rawurlencode( $plugin_link_suffix ),
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $plugin_link_suffix ),
						), network_admin_url( 'plugins.php' )
					);
					$button .= '<a data-slug="' . $slug . '" class="activate-now button button-primary" href="' . esc_url( $nonce ) . '" aria-label="Activate ' . $slug . '">' . __( 'Activate', 'better-kits' ) . '</a>';
					break;
			}// End switch().
		}// End if().
		return $button;
	}
	

	/**
	 * Utility method to render a view from module.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param   string $view_name The view name w/o the `-tpl.php` part.
	 * @param   array  $args      An array of arguments to be passed to the view.
	 *
	 * @return string
	 */
	protected function render_view( $view_name, $args = array() ) {
		ob_start();
		$file = dirname( __FILE__ ) . '/views/' . $view_name . '-tpl.php';
		if ( ! empty( $args ) ) {
			foreach ( $args as $better_kits_rh_name => $better_kits_rh_value ) {
				$$better_kits_rh_name = $better_kits_rh_value;
			}
		}
		if ( file_exists( $file ) ) {
			include $file;
		}

		return ob_get_clean();
	}

	/**
	 * Render the templates admin page.
	 */
	public function better_kits_templates_render() {
		$data = array(
			'templates_array' => $this->templates_list(),
		);
		$allow_html= 
				array(
					'div' => array(
						'class'	 => true,
						'data-demo-url'	 => true,
						'data-screenshot-url'=> true,
						'data-template-slug' => true,
						'data-template-file' => true,
						'data-template-title' => true,
						'data-pro' => true,
						'style' => true,
					), 
					'h1' => array(
						'class'	 => true,
					), 
					'h2' => array(
						'class'	 => true,
					), 
					'p' => array(
						'class'	 => true,
					), 
					'img' => array(
						'class'	 => true,
						'alt'	 => true,
					), 
					'form' => array(
						'method'	 => array(),
						'id'	 => array(),
					),
					'span' => array(
						'class'	 => true,
						'data-template-file' => true,
						'data-template-title' => true,
						'data-template-slug' => true,
					),
					'input' => array(
						'type'	 => true,
						'name'	 => true,
						'value'	 => true,
					),
					'button' => array(
						'class'	 => true,
						'type'	 => true,
						'id'	 => true,
					),
					'i' => array(
						'class'	 => true,
					),
					'a' => array(
						'class' => true,
						'style' => true,
						'href' => true,
						'data-slug' => true,
						'data-name' => true,
						'aria-label' => true,
					)
				);

		echo wp_kses($this->render_view( 'templates-page', $data ),$allow_html);
	}

			
	/**
	 * Add admin menu items for better-kits.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function better_kits_menu_pages()
	{

		add_menu_page( 
			__('Better Kits', 'better-kits'), 
			__('Better Kits', 'better-kits'), 
			'manage_options', 
			'better_kits_templates', 
			array(
				$this,
				'better_kits_templates_render',
			),
			'',
			'59'
		);

	}
}

class Better_Kits_Module_Factory {

	/**
	 * The build method for creating a new better_kits_Module class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $module_name The name of the module to instantiate.
	 * @return mixed
	 */
	public static function build( $module_name ) {
		$module = str_replace( '-', '_', ucwords( $module_name ) ) . '_better_kits_Module';
		if ( class_exists( $module ) ) {
			return new $module();
		}
		return false;
	}
}

class Better_Kits_Global_Settings {

	/**
	 * The main instance var.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     Better_Kits_Global_Settings $instance The instance of this class.
	 */
	public static $instance;

	/**
	 * The instance method for the static class.
	 * Defines and returns the instance of the static class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return Better_Kits_Global_Settings
	 */
	public static function Better_Kits_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Better_Kits_Global_Settings ) ) {
			self::$instance          = new Better_Kits_Global_Settings();
			self::$instance->modules = apply_filters(
				'mods',
				array(
					'template-directory',
				)
			);
		}// End if().

		return self::$instance;
	}

	/**
	 * Registers a module object reference in the $module_objects array.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param   string                    $name The name of the module from $modules array.
	 * @param   Better_Kits_Module_Abstract $module The module object.
	 */
	public function register_module_reference( $name, Better_Kits_Module_Abstract $module ) {
		self::$instance->module_objects[ $name ] = $module;
	}

	/**
	 * Method to retrieve instance of modules.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return array
	 */
	public function get_modules() {
		return self::Better_Kits_instance()->modules;
	}
}

class Better_Kits_Model {

	/**
	 * The model namespace.
	 */
	private $namespace = 'better_kits_data';

	/**
	 * Holds the core settings.
	 */
	private $better_kits_core_settings;

	/**
	 * Holds all enabled modules statuses.
	 */
	private $module_status;

	/**
	 * Holds all enabled modules options.
	 */
	private $module_settings;

	/**
	 * Defines a default data array.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return array
	 */
	public function default_data() {
		$data = array(
			'better_kits_core_settings'   => $this->better_kits_core_settings,
			'module_status'   => $this->module_status,
			'module_settings' => $this->module_settings,
		);

		return $data;
	}

	/**
	 * Utility method to return the active status of a module.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $slug The module slug.
	 * @param   boolean $default The default active state.
	 * @return bool
	 */
	public function get_is_module_active( $slug, $default ) {
		$data = $this->get();
		if ( isset( $data['module_status'][ $slug ]['active'] ) ) {
			return $data['module_status'][ $slug ]['active'];
		}
		return $default; // @codeCoverageIgnore
	}

	/**
	 * Base model method to retrieve data from DB.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get() {
		return get_option( $this->namespace, $this->default_data() );
	}
}
