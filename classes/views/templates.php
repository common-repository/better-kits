<?php
/**
 * Shortcode Markup
 *
 * TMPL - Single Demo Preview
 * TMPL - No more demos
 * TMPL - Filters
 * TMPL - List
 *
 * @package Astra Sites
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$Better_Kits_Admin = new Better_Kits_Admin('better', '1');

$templates_array = $Better_Kits_Admin->templates_list();

$preview_url = add_query_arg( 'better_kits_templates', '', home_url() ); // Define query arg for custom endpoint.
$html = '';

?>

<script type="text/template" id="tmpl-better-template-base-skeleton">
    <div class="better-kits-elementor-templates-card" id="better-sites-modal">
        <div class="templates-card-header">
            <button class="close-templates-card" aria-hidden="true" title="Close"><i class="eicon-editor-close"></i></button>
        </div>
        <div class="better-kits-templates-container">
            <?php
            if ( is_array( $templates_array ) ) {
                foreach ( $templates_array as $template => $properties ) { ?>
                    <div class="better-kits-template">
                        <div class="more-details better-kits-preview-template"
                            data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
                            data-screenshot-url="<?php echo esc_url( $properties['screenshot'] ); ?>"
                            data-template-slug="<?php echo esc_attr( $template ); ?>">
                            <span><?php echo esc_html( 'More Details'); ?></span></div>
                        <div class="better-kits-template-screenshot" style="background-image:url(<?php echo esc_url( $properties['screenshot'] ); ?>);"></div>
                        <h2 class="template-name template-header"><?php echo esc_html( $properties['title'] ); ?></h2>
                    </div>
                <?php };
            };
            ?>
        </div>
    </div>
    <div class="better-kits-template-preview theme-install-overlay wp-full-overlay" style="display: none; ">
        <div class='card'>
            <div class="templates-card-header">
                <button class="close-templates-card" aria-hidden="true" title="Close"><i class="eicon-editor-close"></i></button>
                <button class="back-to-templates" aria-hidden="true" title="Back"><i class="eicon-arrow-left"></i> Back</button>
                <div class="wp-full-overlay-header">
                    <span class="better-kits-import-template button button-primary">
                        <?php esc_html_e( 'Import', 'better-kits' ); ?>
                    </span>
                </div>
            </div>

            <div class='card_left'>
                <?php foreach ( $templates_array as $template => $properties ) { 
                $upsell = 'no';
                if ( isset( $properties['has_badge'] ) && ! isset( $properties['import_file'] ) ) {
                    $upsell = 'yes';
                    $properties['import_file'] = '';
                }?>
                <div class="install-theme-info better-kits-theme-info <?php echo esc_attr( $template ); ?>"
                    data-demo-url="<?php echo esc_url( $properties['demo_url'] ); ?>"
                    <?php if( isset( $properties['import_file'] ) ) { ?>
                    data-template-slug="<?php echo esc_html($properties['slug']) ?>"
                    data-template-file="<?php echo esc_url( $properties['import_file'] ); ?>"
                <?php  } ?>
                    data-template-title="<?php echo esc_html( $properties['title'] ); ?>"
                    data-upsell="<?php echo esc_attr( $upsell ) ?>">
                    <h1>
                        <?php echo esc_html( $properties['title'] ); ?>
                    </h1>
                </div>
                <?php } ?>
            </div>
            <div class='card_right better-kits-main-preview'>
                <img class="better-kits-template-frame" src="" alt="preview">
            </div>
        </div>
    </div>
</script>