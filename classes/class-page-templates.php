<?php

namespace BetterKits;

if ( ! class_exists( '\BetterKits\PageTemplates' ) ) {
	class PageTemplates {

		/**
		 * @var PageTemplates
		 */

		protected static $instance = null;

		/**
		 * The version of this library
		 * @var string
		 */
		public static $version = '1.0.0';

		/**
		 * Holds the module slug.
		 *
		 * @since   1.0.0
		 * @access  protected
		 * @var     string $slug The module slug.
		 */
		protected $slug = 'templates-directory';

		protected $source_url;

		/**
		 * Defines the library behaviour
		 */
		protected function init() {
			add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
			
			//Enqueue admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_template_dir_scripts' ) );
						
			// Get the full-width pages feature
			add_action( 'init', array( $this, 'load_full_width_page_templates' ), 11 );
			// Remove the blank template from the page template selector
			// Filter to add fetched.
			add_filter( 'template_directory_templates_list', array( $this, 'filter_templates' ), 99 );
				
			add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'better_kits_admin_page_scripts'), 999999, 1);

			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'better_kits_pop_up_styles' ) );

			add_action( 'elementor/preview/enqueue_styles', array( $this, 'better_kits_pop_up_styles' ) );
		
			add_action( 'elementor/editor/footer', array( $this, 'better_kits_insert_templates' ) );

		}

		/**
		 * Elementor page scipts
		 */
		public function better_kits_admin_page_scripts() {
			$plugin_slug = 'better-kits';
			wp_enqueue_script('better-kits-admin-page-script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/elementor-admin-page.js', array( 'jquery', 'wp-util', 'masonry', 'imagesloaded' ), $this::$version, true );
			wp_localize_script( 'better-kits-admin-page-script', 'importer_endpoint',
				array(
					'url'                 => $this->better_kits_get_endpoint_url( '/import_elementor' ),
					'upload_json_url'     => $this->better_kits_get_endpoint_url( '/upload_json' ),
					'delete_json_url'     => $this->better_kits_get_endpoint_url( '/delete_json' ),
					'plugin_slug'         => $plugin_slug,
					'fetch_templates_url' => $this->better_kits_get_endpoint_url( '/fetch_templates' ),
					'nonce'               => wp_create_nonce( 'wp_rest' ),
				) 
			);
		}

		/**
		 * Elementor page styles
		 */
		public function better_kits_pop_up_styles() {
			wp_enqueue_style('better-kits-admin-page-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/elementor-admin-page.css', array(), $this::$version );
		}

		/**
		 * Insert Template
		 *
		 * @return void
		 */
		public function better_kits_insert_templates() {
			ob_start();
			require_once dirname( __FILE__ ) . '/views/templates-tpl.php';
			ob_end_flush();
		}

		/**
		 * Enqueue the scripts for the dashboard page of the
		 */
		public function enqueue_template_dir_scripts() {
			$current_screen = get_current_screen();
			if ( $current_screen->id === 'toplevel_page_better_kits_templates' ) {
				if ( $current_screen->id === 'toplevel_page_better_kits_templates' ) {
					$plugin_slug = 'better-kits';
				}  
				$script_handle = $this->slug . '-script';
				wp_enqueue_script( 'plugin-install' );
				wp_enqueue_script( 'updates' );
				wp_register_script('better-kits-admin-page', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/elementor-admin-page.js', array( 'jquery', 'wp-util', 'masonry', 'imagesloaded' ), $this::$version, true );
				wp_register_script( $script_handle, plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/script.js', array( 'jquery', 'updates' ), $this::$version );
				wp_localize_script( $script_handle, 'importer_endpoint',
					array(
						'url'                 => $this->better_kits_get_endpoint_url( '/import_elementor' ),
						'upload_json_url'     => $this->better_kits_get_endpoint_url( '/upload_json' ),
						'delete_json_url'     => $this->better_kits_get_endpoint_url( '/delete_json' ),
						'plugin_slug'         => $plugin_slug,
						'fetch_templates_url' => $this->better_kits_get_endpoint_url( '/fetch_templates' ),
						'nonce'               => wp_create_nonce( 'wp_rest' ),
					) );
				wp_enqueue_script( $script_handle );
				wp_enqueue_style( $this->slug . '-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin.css', array(), $this::$version );
			}
		}	

		/**
		 *
		 *
		 * @param string $path
		 *
		 * @return string
		 */
		public function better_kits_get_endpoint_url( $path = '' ) {
			return rest_url( $this->slug . $path );
		}

		/**
		 * Register Rest endpoint for requests.
		 */
		public function register_endpoints() {
			register_rest_route( $this->slug, '/import_elementor', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'import_elementor' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			) );
			register_rest_route( $this->slug, '/fetch_templates', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'fetch_templates' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			) );
			register_rest_route( $this->slug, '/upload_json', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'upload_json' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			) );
			register_rest_route( $this->slug, '/delete_json', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'delete_json' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			) );
		}

		public function filter_templates( $templates ) {
			$current_screen = get_current_screen();
			if ( $current_screen->id === 'toplevel_page_better_kits_templates' ) {
				$fetched = get_option( 'better_kits_synced_templates' );
			} else {
				$fetched = get_option( 'sizzify_synced_templates' );
			}
			if ( empty( $fetched ) ) {
				return $templates;
			}
			if ( ! is_array( $fetched ) ) {
				return $templates;
			}
			$new_templates = array_merge( $templates, $fetched['templates'] );

			return $new_templates;
		}

		public function upload_json(\WP_REST_Request $request) {
	
			$params = $request->get_params();
			$slug = $params['slug'];
		
			$upload_dir = wp_upload_dir();
		
			$api_home_url = 'https://sites.kitforest.com/';
		
			$templates_file = $api_home_url . 'wp-json/templates/v1/templates';
		
			$template_file = $templates_file . '/' . $slug;
			
			$json_data = file_get_contents( $template_file );
		
			$filename = $slug . '.json';
		
			if ( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			}
			else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
		
			file_put_contents( $file, $json_data );
		
			$attachment = array(
				'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => 'application/json',
				'post_title' => $slug,
				'post_content' => '',
				'post_status' => 'inherit'
			);
		
			$attach_id = wp_insert_attachment( $attachment, $file );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
		}

		public function delete_json(\WP_REST_Request $request) {

			$params = $request->get_params();
			$slug = $params['slug'];

			$upload_dir = wp_upload_dir();
		
			$date = date('Y') . '/' . date('m');
		
			$attachment_url = $upload_dir['baseurl']  .'/'. $date .'/'. $slug . '.json';
		
			$attachment_id = attachment_url_to_postid( $attachment_url );
		
			wp_delete_attachment( $attachment_id, true );

			return $attachment_id;
		}
		
		/**
		 * Utility method to call Elementor import routine.
		 *
		 * @param \WP_REST_Request $request the async request.
		 *
		 * @return string
		 */
		 
		public function import_elementor( \WP_REST_Request $request ) {
			if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
				return 'no-elementor';
			}

			$params        = $request->get_params();
			$template_name = $params['template_name'];
			$template_url  = $params['template_url'];

			require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
			require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );

			// Mime a supported document type.
			$elementor_plugin = \Elementor\Plugin::$instance;
			$elementor_plugin->documents->register_document_type( 'not-supported', \Elementor\Modules\Library\Documents\Page::get_class_full_name() );

			$template                   = download_url( esc_url( $template_url ) );
			$name                       = $template_name;
			$_FILES['file']['tmp_name'] = $template;
			$elementor                  = new \Elementor\TemplateLibrary\Source_Local;
			$elementor->import_template( $name, $template );
			unlink( $template );

			$args = array(
				'post_type'        => 'elementor_library',
				'nopaging'         => true,
				'posts_per_page'   => '1',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => true,
			);

			$query = new \WP_Query( $args );

			$last_template_added = $query->posts[0];
			//get template id
			$template_id = $last_template_added->ID;

			wp_reset_query();
			wp_reset_postdata();

			//page content
			$page_content = $last_template_added->post_content;


			if(isset($params['builder_import'])){

				$api_home_url = 'https://kits.kitforest.com/';

				$templates_file = $api_home_url . 'wp-json/templates/v1/templates';

				$template_file = $templates_file . '/' . $params['template_slug'];
				
				$json_data = json_decode(file_get_contents( $template_file ), true);

				return $json_data['content'];
			
			} else {
				
				//meta fields
				$elementor_data_meta      = get_post_meta( $template_id, '_elementor_data' );
				$elementor_ver_meta       = get_post_meta( $template_id, '_elementor_version' );
				$elementor_edit_mode_meta = get_post_meta( $template_id, '_elementor_edit_mode' );
				$elementor_css_meta       = get_post_meta( $template_id, '_elementor_css' );

				$elementor_metas = array(
					'_elementor_data'      => ! empty( $elementor_data_meta[0] ) ? wp_slash( $elementor_data_meta[0] ) : '',
					'_elementor_version'   => ! empty( $elementor_ver_meta[0] ) ? $elementor_ver_meta[0] : '',
					'_elementor_edit_mode' => ! empty( $elementor_edit_mode_meta[0] ) ? $elementor_edit_mode_meta[0] : '',
					'_elementor_css'       => $elementor_css_meta,
				);

				// Create post object
				$new_template_page = array(
					'post_type'     => 'page',
					'post_title'    => $template_name,
					'post_status'   => 'publish',
					'post_content'  => $page_content,
					'meta_input'    => $elementor_metas,
					'page_template' => apply_filters( 'template_directory_default_template', 'templates/builder-fullwidth.php' )
				);

				$post_id = wp_insert_post( $new_template_page );
				$redirect_url = add_query_arg( array(
					'post'   => $post_id,
					'action' => 'elementor',
				), admin_url( 'post.php' ) );
				
				return ( $redirect_url );
			}
			
		}

		/**
		 * Getter method for the source url
		 * @return mixed
		 */
		public function get_source_url() {
			return $this->source_url;
		}

		/**
		 * Setting method for source url
		 *
		 * @param $url
		 */
		protected function set_source_url( $url ) {
			$this->source_url = $url;
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
				$needs = ( is_plugin_active( $slug . '/' . $slug . '.php' ) ||
				           is_plugin_active( $slug . '/index.php' ) ) ?
					'deactivate' : 'activate';

				return $needs;
			} else {
				return 'install';
			}
		}

		/**
		 * If the composer library is present let's try to init.
		 */
		public function load_full_width_page_templates() {
			if ( class_exists( '\BetterKits\FullWidthTemplates' ) ) {
				\BetterKits\FullWidthTemplates::instance();
			}
		}

		/**
		 * @static
		 * @since  1.0.0
		 * @access public
		 * @return PageTemplates
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->init();
			}

			return self::$instance;
		}
	}
}
