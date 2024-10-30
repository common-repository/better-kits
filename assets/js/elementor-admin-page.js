/**
 * Templates Customizer Admin Dashboard Script
 */

!(function ($) {
	"use strict";

	// Elementor editor button
	function addButton() {

		let add_section_tmpl = $( "#tmpl-elementor-add-section" );

		if ( add_section_tmpl.length > 0 ) {

			let action_for_add_section = add_section_tmpl.text();
			
			action_for_add_section = action_for_add_section.replace( '<div class="elementor-add-section-drag-title', '<div class="elementor-add-section-area-button elementor-add-better-site-button" title="Better Kits Templates"> <i class="eicon-folder"></i> </div><div class="elementor-add-section-drag-title' );

			add_section_tmpl.text( action_for_add_section );

		}

	};

	addButton();

	elementor.on( "preview:loaded", function() {

		let base_skeleton = wp.template( 'better-template-base-skeleton' );

		if ( $( '#better-sites-modal' ).length == 0 ) {

			$( 'body' ).append( base_skeleton() );

			$( '#better-sites-modal' ).attr('style', 'display:none;');
		
		};

		$( elementor.$previewContents[0].body ).on( "click", ".elementor-add-better-site-button", function () {
			$( '#better-sites-modal' ).attr('style', 'display:block;');
		} );

		// Delete json file after import
		function delete_json(slug){
			$.ajax({
				type: "POST",
				url: importer_endpoint.delete_json_url,
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', importer_endpoint.nonce );
				},
				success: function(data){
					console.log(data);
				},
				error: function(error){
					console.log(error);
				},
				complete: function(data){
					console.log(data);
				},
				data: {
					slug: slug,
				},
			},)
		}

		// Setup import button data
		function setupImportButton() {
			var button = $( '.wp-full-overlay-header .better-kits-import-template' );
			var close_button = $('.templates-card-header .close-templates-card');
			var back_button = $('.templates-card-header .back-to-templates');
			var activeTheme = $( '.better-kits-theme-info.active' );
			var installable = $( '.active .better-kits-installable' );
			if ($( activeTheme ).data( 'pro' ) == 'premium') {
				$( '.wp-full-overlay-header .better-kits-import-template' ).replaceWith( '<a class="better-kits-get-access" href= "#0">Get access</a>' );
			} else if ( $( activeTheme ).data( 'pro' ) == 'free' && installable.length > 0 ) {
				$('.better-kits-get-access').replaceWith('<span class="better-kits-import-template button button-primary">Import</span>')
				$( '.wp-full-overlay-header .better-kits-import-template' ).text( 'Install and Import' );
			} else {
				$('.better-kits-get-access').replaceWith('<span class="better-kits-import-template button button-primary">Import</span>')
				$( '.wp-full-overlay-header .better-kits-import-template' ).text( 'Import' );
			}
			$( button ).attr( 'data-template-file', $( activeTheme ).data( 'template-file' ) );
			$( button ).attr( 'data-template-title', $( activeTheme ).data( 'template-title' ) );
			$( button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
			$( close_button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
			$( back_button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
		}

		// Handle preview click
		$( '.better-kits-preview-template' ).on(
			'click', function () {
				
				var template_slug = $( this ).data( 'template-slug' );
				var previewUrl = $( this ).data( 'screenshot-url' );
				$( '.better-kits-template-frame' ).attr( 'src', previewUrl );
				$( '.better-kits-theme-info.' + template_slug ).addClass( 'active' );
				setupImportButton();
				$( '.better-kits-template-preview' ).fadeIn();
				$('.better-kits-import-template').addClass('better-kits-import-template-disable');
				$('.better-kits-import-template.better-kits-import-template-disable').text('Please wait...');
				
				$.ajax({
					type: "POST",
					url: importer_endpoint.upload_json_url,
					beforeSend: function ( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', importer_endpoint.nonce );
					},
					data: {
						slug: template_slug,
					},
					success: function ( data ) {
						console.log(data);
					},
					error: function ( error ) {
						console.log( error );
					},
					complete: function () {
						$('.better-kits-import-template').removeClass('better-kits-import-template-disable');
						$('.better-kits-import-template').text('Import');
					}
				},)
			}
		);
			
		// Handle import click.
		$( '.wp-full-overlay-header' ).on(
			'click', '.better-kits-import-template', function () {
				$( this ).addClass( 'better-kits-import-queue updating-message better-kits-updating' ).html( '' );
				var template_url = $( this ).data( 'template-file' );
				var template_name = $( this ).data( 'template-title' );
				var template_slug = $( this ).data( 'template-slug' );
				var template_model = new Backbone.Model({
					getTitle() {
					  return template_name;
					},
				});
				if ( $( '.active .better-kits-installable' ).length || $( '.active .better-kits-activate' ).length ) {
					checkAndInstallPlugins();
				} else {
					$.ajax(
						{
							url: importer_endpoint.url,
							beforeSend: function ( xhr ) {
								$( '.better-kits-import-queue' ).addClass( 'better-kits-updating' ).html( '' );
								xhr.setRequestHeader( 'X-WP-Nonce', importer_endpoint.nonce );
							},
							data: {
								template_url: template_url,
								template_name: template_name,
								template_slug: template_slug,
								builder_import: true
							},
							type: 'POST',
							success: function ( data ) {
								$( '.better-kits-updating' ).replaceWith( '<span class="better-kits-done-import">done <i class="dashicons-yes dashicons"></i></span>' );
								var page_content = data;

								console.log( page_content );
								console.groupEnd();

								if ( undefined !== page_content && '' !== page_content ) {
									elementor.channels.data.trigger('template:before:insert', template_model);
									elementor.getPreviewView().addChildModel( page_content, { at : 0 } || {} );
									elementor.channels.data.trigger('template:after:insert', {});
									if ( undefined != $e && 'undefined' != typeof $e.internal ) {
										$e.internal( 'document/save/set-is-modified', { status: true } )
									} else {
										elementor.saver.setFlagEditorChange(true);
									}
								}
							},
							error: function ( error ) {
								console.error( error );
							},
							complete: function () {
								delete_json(template_slug);
								$('.close-templates-card').trigger( "click" );
								$( '.better-kits-updating' ).replaceWith( '<span class="better-kits-done-import">done <i class="dashicons-yes dashicons"></i></span>' );
								elementor.reloadPreview();
							}
						}, 'json'
					);
				};
			}
		);

		// Close Button
		$( '.close-templates-card' ).on('click', function () {
			var template_slug = $( this ).data( 'template-slug' );
			delete_json(template_slug);
			$( '#better-sites-modal' ).attr('style', 'display:none;');
			$( '.better-kits-template-preview .better-kits-theme-info.active' ).removeClass( 'active' );
			$( '.better-kits-template-preview' ).hide();
			$( '.better-kits-template-frame' ).attr( 'src', '' );
		} );

		// Back Button.
		$( '.back-to-templates' ).on(
			'click', function () {
				var template_slug = $( this ).data( 'template-slug' );
				delete_json(template_slug);
				$( '.better-kits-template-preview .better-kits-theme-info.active' ).removeClass( 'active' );
				$( '.better-kits-template-preview' ).hide();
				$( '.better-kits-template-frame' ).attr( 'src', '' );
			}
		);

		// Check plugins
		function checkAndInstallPlugins() {
			var installable = $( '.active .better-kits-installable' );
			var toActivate = $( '.active .better-kits-activate' );
			if ( installable.length || toActivate.length ) {

				$( installable ).each(
					function () {
						var plugin = $( this );
						$( plugin ).removeClass( 'better-kits-installable' ).addClass( 'better-kits-installing' );
						$( plugin ).find( 'span.dashicons' ).replaceWith( '<span class="dashicons dashicons-update" style="-webkit-animation: rotation 2s infinite linear; animation: rotation 2s infinite linear; color: #ffb227 "></span>' );
						var slug = $( this ).find( '.better-kits-install-plugin' ).attr( 'data-slug' );
						wp.updates.installPlugin(
							{
								slug: slug,
								success: function ( response ) {
									activatePlugin( response.activateUrl, plugin );
								}
							}
						);
					}
				);

				$( toActivate ).each(
					function () {
						var plugin = $( this );
						var activateUrl = $( plugin ).find( '.activate-now' ).attr( 'href' );
						if ( typeof activateUrl !== 'undefined' ) {
							activatePlugin( activateUrl, plugin );
						}
					}
				);
			}
		}

		// Activate plugins
		function activatePlugin( activationUrl, plugin ) {
			$.ajax(
				{
					type: 'GET',
					url: activationUrl,
					beforeSend: function () {
						$( plugin ).removeClass( 'better-kits-activate' ).addClass( 'better-kits-installing' );
						$( plugin ).find( 'span.dashicons' ).replaceWith( '<span class="dashicons dashicons-update" style="-webkit-animation: spin 2s infinite linear; animation: spin 2s infinite linear; color: #ffb227 "></span>' );
					},
					success: function () {
						$( plugin ).find( '.dashicons' ).replaceWith( '<span class="dashicons dashicons-yes" style="color: #34a85e"></span>' );
						$( plugin ).removeClass( 'better-kits-installing' );
					},
					complete: function () {
						if ( $( '.active .better-kits-installing' ).length === 0 ) {
							$( '.better-kits-import-queue' ).trigger( 'click' );
						}
					}
				}
			);
		}

		// Change preview source.
		function changePreviewSource() {
			var previewUrl = $( '.better-kits-theme-info.active' ).data( 'screenshot-url' );
			$( '.better-kits-template-frame' ).attr( 'src', previewUrl );
		}
	
	});

})(jQuery);