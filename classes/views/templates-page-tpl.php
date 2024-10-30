<?php
/**
 * The View for Rendering the Templates Main Dashboard Page.
 *
 * @link       https://www.kitforest.com
 * @since      1.0.0
 *
 * @package    BetterKits
 * @subpackage BetterKits/PageTemplates
 * @codeCoverageIgnore
 */


$preview_url = add_query_arg( 'better_kits_templates', '', home_url() ); // Define query arg for custom endpoint.
$html = '';

if ( is_array( $templates_array ) ) { ?>
	<div class="better-kits-template-dir wrap">
        	<?php
        	$mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']): ''; 
        	$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']): '';
			$url = $_SERVER['REQUEST_URI'];  
			$template_text = 'better_kits_templates';
			$get_admin_url = get_admin_url();
			?>
            <div class="better-kits-form-area">
				<h2 class="wp-heading-inline better-kits-first-heading"> <?php echo apply_filters( 'better_kits_template_dir_page_title', __( 'Better Kits', 'better-kits' ) ); ?></h2>
				<form method="post" id="better-kits-searchform-template"> 
					<span> 
						<input type="hidden" name="mode" value="action">
						<input type="text" name="search" placeholder="<?php if (strpos($url, $template_text)!==false){echo esc_attr("Find Elementor Templates");} ?>" value="" required>
						<button type="submit" id="searchsubmit">
							<i class="icon"></i>
						</button>
					</span>
				</form>
            </div>
        <?php
        	if($mode=="action"){
        		$get_admin_url = get_admin_url();
        ?>
        <div class="better-kits-search-result">
        	<?php echo wp_kses_post( 'search result for <span>'.$search.'</span>', 'better-kits' ); ?>
        </div>
    	<?php } ?>
        <div class="better-kits-template-browser">
		<?php
		$search_found = false;
		foreach ( $templates_array as $template => $properties ) {
		if($mode=="action"){
		$keywords = str_replace(",","",$properties['keywords']);
		$title = $properties['title'];
		$lower_title = strtolower($properties['title']);
		$pos = strpos($keywords, $search);
		if ($lower_title==$search) { ?>
    		<div class="better-kits-template">
					<?php if ( $properties['template_type'] == 'premium' ) { ?>
						<div class="pro-templates"><span>pro</span></div>
					<?php } ?>
					<div class="more-details better-kits-preview-template"
						 data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
						 data-screenshot-url="<?php echo esc_url( $properties['screenshot'] ); ?>"
						 data-template-slug="<?php echo esc_attr( $template ); ?>"
						 data-pro="<?php echo esc_attr( $properties['template_type'] ) ?>">
						<span><?php echo esc_attr( 'More Details.'); ?></span></div>
					<div class="better-kits-template-screenshot">
						<img src="<?php echo esc_url( $properties['screenshot'] ); ?>"
							 alt="<?php echo esc_html( $properties['title'] ); ?>">
					</div>
					<h2 class="template-name template-header 1"><?php echo esc_html( $properties['title'] ); ?></h2>
				</div>
		<?php  $search_found = true;	}
		if ($title==$search) { ?>
    		<div class="better-kits-template">
					<?php if ( $properties['template_type'] == 'premium' ) { ?>
						<div class="pro-templates"><span>pro</span></div>
					<?php } ?>
					<div class="more-details better-kits-preview-template"
						 data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
						 data-screenshot-url="<?php echo esc_url( $properties['screenshot'] ); ?>"
						 data-template-slug="<?php echo esc_attr( $template ); ?>"
						 data-pro="<?php echo esc_attr( $properties['template_type'] ) ?>">
						<span><?php echo esc_attr( 'More Details.'); ?></span></div>
					<div class="better-kits-template-screenshot">
						<img src="<?php echo esc_url( $properties['screenshot'] ); ?>"
							 alt="<?php echo esc_html( $properties['title'] ); ?>">
					</div>
					<h2 class="template-name template-header 2"><?php echo esc_html( $properties['title'] ); ?></h2>
				</div>
		<?php  $search_found = true;	} if($pos) {	?>
				<div class="better-kits-template">
					<?php if ( $properties['template_type'] == 'premium' ) { ?>
						<div class="pro-templates"><span>pro</span></div>
					<?php } ?>
					<div class="more-details better-kits-preview-template"
						 data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
						 data-screenshot-url="<?php echo esc_url( $properties['screenshot'] ); ?>"
						 data-template-slug="<?php echo esc_attr( $template ); ?>"
						 data-pro="<?php echo esc_attr( $properties['template_type'] ) ?>">
						<span><?php echo esc_attr( 'More Details.'); ?></span></div>
					<div class="better-kits-template-screenshot">
						<img src="<?php echo esc_url( $properties['screenshot'] ); ?>"
							 alt="<?php echo esc_html( $properties['title'] ); ?>">
					</div>
					<h2 class="template-name template-header 3"><?php echo esc_html( $properties['title'] ); ?></h2>
				</div>
			<?php  $search_found = true; } }elseif($mode=="")  { ?>
				<div class="better-kits-template">
					<?php if ( $properties['template_type'] == 'premium' ) { ?>
						<div class="pro-templates"><span>Pro</span></div>
					<?php } ?>
					<div class="more-details better-kits-preview-template"
						 data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
						 data-screenshot-url="<?php echo esc_url( $properties['screenshot'] ); ?>"
						 data-template-slug="<?php echo esc_attr( $template ); ?>"
						 data-pro="<?php echo esc_attr( $properties['template_type'] ) ?>">
						<span><?php echo esc_html( 'More Details' ); ?></span></div>
					<div class="better-kits-template-screenshot" style="background-image:url(<?php echo esc_url( $properties['screenshot'] ); ?>);"></div>
					<h2 class="template-name template-header 4"><?php echo esc_html( $properties['title'] ); ?></h2>
				</div>
		<?php	$search_found = true;}
		} 
		if (!$search_found) {
		    echo esc_html('No templates found');
		}
		?>
		</div>
	</div>
	<div class="wp-clearfix clearfix"></div>
<?php } // End if().
?>
<div class="better-kits-template-preview theme-install-overlay wp-full-overlay" style="display: none; ">
	<div class='card'>
        <button class="close-full-overlay">
            <span class="screen-reader-text">
                <?php esc_html_e( 'Close', 'better-kits' ); ?>
            </span>
        </button>

        <div class='card_left'>
            <?php foreach ( $templates_array as $template => $properties ) { ?>
				<div class="install-theme-info better-kits-theme-info <?php echo esc_attr( $template ); ?>"
	                data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
	                <?php if( isset( $properties['import_file'] ) ) { ?>
					data-template-slug="<?php echo $properties['slug'] ?>"
					data-template-file="<?php echo esc_url( $properties['import_file'] ); ?>"
	               <?php  } ?>
	                data-template-title="<?php echo esc_html( $properties['title'] ); ?>"
	                data-pro="<?php echo esc_attr( $properties['template_type'] ) ?>">
	                <h1>
	                    <?php echo esc_html( $properties['title'] ); ?>
	                </h1>
	                <div class='card_review'>
	                    <p>
	                        <?php echo esc_html( $properties['description'] ); ?>
	                    </p>
	                </div>
					<div class="better-kits-keywords">
						<p><?php esc_html_e( 'Tags', 'better-kits' ); ?></p>
						<span><?php echo esc_html( $properties['keywords'] ); ?></span>
					</div>
					<?php
					if ( ! empty( $properties['required_plugins'] ) && is_array( $properties['required_plugins'] ) ) {
					 ?>
						<div class="better-kits-required-plugins">
							<p><?php esc_html_e( 'Required Plugins', 'better-kits' ); ?></p>
							<?php
							foreach ( $properties['required_plugins'] as $plugin_slug => $details ) {
								if ( $this->check_plugin_state( $plugin_slug ) === 'install' ) {
									echo '<div class="better-kits-installable plugin-card-' . esc_attr( $plugin_slug ) . '">';
									echo '<span class="dashicons dashicons-no-alt"></span>';
									echo $details['title'];
									echo $this->get_button_html( $plugin_slug );
									echo '</div>';
								} elseif ( $this->check_plugin_state( $plugin_slug ) === 'activate' ) {
									echo '<div class="better-kits-activate plugin-card-' . esc_attr( $plugin_slug ) . '">';
									echo '<span class="dashicons dashicons-admin-plugins"></span>';
									echo $details['title'];
									echo $this->get_button_html( $plugin_slug );
									echo '</div>';
								} else {
									echo '<div class="better-kits-installed plugin-card-' . esc_attr( $plugin_slug ) . '">';
									echo '<span class="dashicons dashicons-yes"></span>';
									echo $details['title'];
									echo '</div>';
								}
							} ?>
						</div>
					<?php } ?>


					<div class="wp-full-overlay-header">
						<?php if (strpos($url, $template_text)!==false){?>
							<span class="better-kits-preview-template button button-primary" >
								<a href="<?php echo esc_url( $properties['demo_url'] ); ?>" target="_blank">
								<?php esc_html_e( 'Preview', 'better-kits' ); ?>
							</a>
							</span>
							<span class="better-kits-import-template button button-primary">
								<?php esc_html_e( 'Import', 'better-kits' ); ?>
							</span>
						<?php } ?>
					</div>

	            </div>
            <?php } ?>

        </div>
        <div class='card_right better-kits-main-preview'>
            <img class="better-kits-template-frame" src="" alt="preview">
        </div>
    </div>
</div>