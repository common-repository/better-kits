/* global importer_endpoint, console */

/**
 * Templates Customizer Admin Dashboard Script
 */

var better_kits_templates = function ( $ ) {
	'use strict';

	$( function () {

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

		function setupButtons() {
			var import_button = $( '.wp-full-overlay-header .better-kits-import-template' );
			var close_button = $( 'button.close-full-overlay' );
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
			$( import_button ).attr( 'data-template-file', $( activeTheme ).data( 'template-file' ) );
			$( import_button ).attr( 'data-template-title', $( activeTheme ).data( 'template-title' ) );
			$( import_button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
			$( close_button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
		}

		$( '.better-kits-preview-template' ).on(
			'click', function () {
				
				var template_slug = $( this ).data( 'template-slug' );
				var previewUrl = $( this ).data( 'screenshot-url' );
				$( '.better-kits-template-frame' ).attr( 'src', previewUrl );
				$( '.better-kits-theme-info.' + template_slug ).addClass( 'active' );
				setupButtons();
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
					error: function(error){
						console.log(error);
					},
					success: function ( data ) {
						console.log(data);
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
							},
							type: 'POST',
							success: function ( data ) {
								$( '.better-kits-updating' ).replaceWith( '<span class="better-kits-done-import">done <i class="dashicons-yes dashicons"></i></span><a class="better-kits-done-external" href= "'+data+'" target="_blank"><i class="dashicons-external dashicons"></i> View now</a>' );
							},
							error: function ( error ) {
								console.error( error );
							},
							complete: function () {
								delete_json(template_slug);
								$( '.better-kits-updating' ).replaceWith( '<span class="better-kits-done-import">done <i class="dashicons-yes dashicons"></i></span>' );
							}
						}, 'json'
					);
				}
			}
		);

		$( '.wp-full-overlay' ).on(
			'click', 'button.close-full-overlay', function () {
				var template_slug = $( this ).data( 'template-slug' );
				console.log(template_slug);
				delete_json(template_slug);
			}
		);

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

		function activatePlugin( activationUrl, plugin ) {
			$.ajax(
				{
					type: 'GET',
					url: activationUrl,
					beforeSend: function () {
						$( plugin ).removeClass( 'better-kits-activate' ).addClass( 'better-kits-installing' );
						$( plugin ).find( 'span.dashicons' ).replaceWith( '<span class="dashicons dashicons-update" style="-webkit-animation: rotation 2s infinite linear; animation: rotation 2s infinite linear; color: #ffb227 "></span>' );
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

		// Hide preview.
		$( '.close-full-overlay' ).on(
			'click', function () {
				$( '.better-kits-template-preview .better-kits-theme-info.active' ).removeClass( 'active' );
				$( '.better-kits-template-preview' ).hide();
				$( '.better-kits-template-frame' ).attr( 'src', '' );
			}
		);

		// Change preview source.
		function changePreviewSource() {
			var previewUrl = $( '.better-kits-theme-info.active' ).data( 'screenshot-url' );
			$( '.better-kits-template-frame' ).attr( 'src', previewUrl );
		}
	});
};

better_kits_templates( jQuery );
