(function( $ ) {
	'use strict';

	/**
	 * Initializes our event handlers.
	 */
	function bsr_init() {
		bsr_search_replace();
		bsr_update_sliders();
		bsr_csv_upload();
	}

	/**
	 * Recursive function for performing batch operations.
	 */
	function bsr_process_step( action, step, page, data ) {

		$.ajax({
			type: 'POST',
			url: bsr_object_vars.endpoint + action,
			data: {
				bsr_ajax_nonce : bsr_object_vars.ajax_nonce,
				action: action,
				bsr_step: step,
				bsr_page: page,
				bsr_data: data
			},
			dataType: 'json',
			success: function( response ) {

				// Maybe display more details.
				if ( typeof response.message != 'undefined' ) {
					$('.bsr-description').remove();
					$('.bsr-progress-wrap').append( '<p class="description bsr-description">' + response.message + '</p>' );
				}

				if ( 'done' == response.step ) {

					bsr_update_progress_bar( '100%' );

					// Maybe run another action.
					if ( typeof response.next_action != 'undefined' ) {
						bsr_update_progress_bar( '0%', 0 );
						bsr_process_step( response.next_action, 0, 0, response.bsr_data );
					} else {
						$('.bsr-processing-wrap').remove();
						$('.bsr-disabled').removeClass('bsr-disabled button-disabled' );
						window.location = response.url;
					}

				} else {
					bsr_update_progress_bar( response.percentage );
					bsr_process_step( action, response.step, response.page, response.bsr_data );
				}

			}
		}).fail(function (response) {
			$('.bsr-processing-wrap').remove();
			$('.bsr-disabled').removeClass('bsr-disabled button-disabled' );
			$('#bsr-error-wrap').html( '<div class="error"><p>' + bsr_object_vars.unknown + '</p></div>' ).show();
			if ( window.console && window.console.log ) {
				console.log(response);
			}
		});

	}

	/**
	 * Initializes a search/replace.
	 */
	function bsr_search_replace() {

		var search_replace_submit = $( '#bsr-submit' );
		var bsr_error_wrap = $( '#bsr-error-wrap' );
		search_replace_submit.on( 'click', function( e ) {

			e.preventDefault();

			if ( ! search_replace_submit.hasClass( 'button-disabled' ) ) {

				if ( ! $( '#search_for' ).val() ) {
					bsr_error_wrap.html( '<div class="error"><p>' + bsr_object_vars.no_search + '</p></div>' ).show();
				} else if ( ! $( '#bsr-table-select' ).val() ) {
					bsr_error_wrap.html( '<div class="error"><p>' + bsr_object_vars.no_tables + '</p></div>' ).show();
				} else {
					var str 	= $( '.bsr-action-form' ).serialize();
					var data 	= str.replace(/%5C/g, "#BSR_BACKSLASH#" );

					bsr_error_wrap.html('').hide();
					search_replace_submit.addClass( 'bsr-disabled button-disabled' );
					$( '#bsr-submit-wrap' ).before('<div class="bsr-processing-wrap"><div class="spinner is-active bsr-spinner"></div><div class="bsr-progress-wrap"><div class="bsr-progress"></div></div></div>');
					$('.bsr-progress-wrap').append( '<p class="description bsr-description">' + bsr_object_vars.processing + '</p>' );
					bsr_process_step( 'process_search_replace', 0, 0, data );
				}

			}

		});

	}

	/**
	 * Updates the progress bar for AJAX bulk actions.
	 */
	function bsr_update_progress_bar( percentage, speed ) {
		if ( typeof speed == 'undefined' ) {
			speed = 150;
		}
		$( '.bsr-progress' ).animate({
			width: percentage
		}, speed );
	}

	/**
	 * Handles CSV file upload and preview.
	 */
	var bsr_csv_key = '';

	function bsr_csv_upload() {
		var csv_file_input = $( '#bsr_csv_file' );
		var csv_preview = $( '#bsr-csv-preview' );
		var csv_preview_content = $( '#bsr-csv-preview-content' );
		var search_for = $( '#search_for' );
		var replace_with = $( '#replace_with' );

		// Handle file selection
		csv_file_input.on( 'change', function( e ) {
			var file = e.target.files[0];

			if ( ! file ) {
				csv_preview.hide();
				bsr_csv_key = '';
				search_for.prop( 'disabled', false );
				replace_with.prop( 'disabled', false );
				return;
			}

			// Validate file type
			if ( ! file.name.toLowerCase().endsWith( '.csv' ) ) {
				alert( 'Please select a CSV file.' );
				csv_file_input.val( '' );
				return;
			}

			// Read and preview CSV
			var reader = new FileReader();
			reader.onload = function( e ) {
				var content = e.target.result;
				var lines = content.split( '\n' );
				var preview_html = '<table style="width: 100%; border-collapse: collapse;"><thead><tr><th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Search For</th><th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Replace With</th></tr></thead><tbody>';

				var has_data = false;
				var skip_first = false;

				// Check if first row is header
				if ( lines.length > 0 ) {
					var first_row = lines[0].split( ',' );
					if ( first_row.length >= 2 ) {
						var col1 = first_row[0].toLowerCase().trim().replace( /"/g, '' );
						var col2 = first_row[1].toLowerCase().trim().replace( /"/g, '' );
						if ( col1 === 'search_for' || col1 === 'search' ) {
							skip_first = true;
						}
					}
				}

				for ( var i = ( skip_first ? 1 : 0 ); i < Math.min( lines.length, 10 + ( skip_first ? 1 : 0 ) ); i++ ) {
					if ( ! lines[i].trim() ) continue;

					var cols = lines[i].split( ',' );
					if ( cols.length >= 2 ) {
						has_data = true;
						preview_html += '<tr><td style="border: 1px solid #ddd; padding: 8px;">' +
							cols[0].replace( /"/g, '' ).trim().substring( 0, 100 ) +
							'</td><td style="border: 1px solid #ddd; padding: 8px;">' +
							cols[1].replace( /"/g, '' ).trim().substring( 0, 100 ) +
							'</td></tr>';
					}
				}

				if ( lines.length > 10 + ( skip_first ? 1 : 0 ) ) {
					preview_html += '<tr><td colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: center; font-style: italic;">... and ' + ( lines.length - 10 - ( skip_first ? 1 : 0 ) ) + ' more rows</td></tr>';
				}

				preview_html += '</tbody></table>';

				if ( has_data ) {
					csv_preview_content.html( preview_html );
					csv_preview.show();
					// Disable manual input fields when CSV is selected
					search_for.prop( 'disabled', true );
					replace_with.prop( 'disabled', true );
				} else {
					csv_preview.hide();
					alert( 'No valid data found in CSV file.' );
					csv_file_input.val( '' );
				}
			};

			reader.onerror = function() {
				alert( 'Error reading CSV file.' );
				csv_file_input.val( '' );
			};

			reader.readAsText( file );
		});

		// Override form submission to handle CSV upload first
		var original_submit_handler = $( '#bsr-submit' );
		original_submit_handler.off( 'click' );
		original_submit_handler.on( 'click', function( e ) {
			e.preventDefault();

			var bsr_error_wrap = $( '#bsr-error-wrap' );

			if ( original_submit_handler.hasClass( 'button-disabled' ) ) {
				return;
			}

			var file = csv_file_input[0].files[0];

			if ( file ) {
				// CSV file selected - upload it first
				var formData = new FormData();
				formData.append( 'bsr_csv_file', file );
				formData.append( 'bsr_ajax_nonce', bsr_object_vars.ajax_nonce );
				formData.append( 'action', 'upload_csv' );

				// Show processing
				bsr_error_wrap.html( '<div class="notice notice-info"><p>Uploading and validating CSV file...</p></div>' ).show();
				original_submit_handler.addClass( 'button-disabled' );

				$.ajax({
					type: 'POST',
					url: bsr_object_vars.endpoint + 'upload_csv',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function( response ) {
						if ( response.success ) {
							bsr_csv_key = response.data.key;
							bsr_error_wrap.html( '' ).hide();

							// Add CSV key to form data
							$( '<input>' ).attr({
								type: 'hidden',
								name: 'bsr_csv_key',
								value: bsr_csv_key
							}).appendTo( '.bsr-action-form' );

							// Now proceed with search/replace
							bsr_start_search_replace();
						} else {
							bsr_error_wrap.html( '<div class="error"><p>' + response.data.message + '</p></div>' ).show();
							original_submit_handler.removeClass( 'button-disabled' );
						}
					},
					error: function() {
						bsr_error_wrap.html( '<div class="error"><p>Error uploading CSV file.</p></div>' ).show();
						original_submit_handler.removeClass( 'button-disabled' );
					}
				});
			} else {
				// No CSV file - use manual input
				if ( ! search_for.val() ) {
					bsr_error_wrap.html( '<div class="error"><p>' + bsr_object_vars.no_search + '</p></div>' ).show();
				} else if ( ! $( '#bsr-table-select' ).val() ) {
					bsr_error_wrap.html( '<div class="error"><p>' + bsr_object_vars.no_tables + '</p></div>' ).show();
				} else {
					bsr_start_search_replace();
				}
			}
		});

		function bsr_start_search_replace() {
			var bsr_error_wrap = $( '#bsr-error-wrap' );
			var str = $( '.bsr-action-form' ).serialize();
			var data = str.replace(/%5C/g, "#BSR_BACKSLASH#" );

			bsr_error_wrap.html('').hide();
			original_submit_handler.addClass( 'button-disabled' );
			$( '#bsr-submit-wrap' ).before('<div class="bsr-processing-wrap"><div class="spinner is-active bsr-spinner"></div><div class="bsr-progress-wrap"><div class="bsr-progress"></div></div></div>');
			$('.bsr-progress-wrap').append( '<p class="description bsr-description">' + bsr_object_vars.processing + '</p>' );
			bsr_process_step( 'process_search_replace', 0, 0, data );
		}
	}

	/**
	 * Updates the "Max Page Size" slider.
	 */
	function bsr_update_sliders( percentage ) {
		$('#bsr-page-size-slider').slider({
			value: bsr_object_vars.page_size,
			range: "min",
			min: 1000,
			max: 50000,
			step: 1000,
			slide: function( event, ui ) {
				$('#bsr-page-size-value').text( ui.value );
				$('#bsr_page_size').val( ui.value );
			}
		});
	}

	bsr_init();

	function toggle_tooltip( icon ) {
		var icon = $( icon );
		var bubble = icon.next();

		// Close any that are already open
		$( '.helper-message' ).not( bubble ).hide();

		var position = icon.position();

		if ( icon.parent()[0].nodeName === 'TD' ) {
			position = icon.offset();
		}

		if ( bubble.hasClass( 'left' ) ) {
			bubble.css({
				'left': ( position.left - bubble.width() - icon.width() - 29 ) + 'px',
				'top': ( position.top + icon.height() / 2 - 18 ) + 'px'
			})
		} else if ( bubble.hasClass( 'bottom' ) ) {
			bubble.css( {
				'left': ( ( position.left - bubble.width() / 2 ) - 5 ) + 'px',
				'top': ( position.top + icon.height() + 19 ) + 'px'
			} );
		} else {
			bubble.css( {
				'left': ( position.left + icon.width() + 19 ) + 'px',
				'top': ( position.top + icon.height() / 2 - 18 ) + 'px'
			} );
		}

		bubble.toggle();
	}

	$('body').on('thickbox:iframe:loaded', function(){
		var $iframeBody = $( '#TB_window iframe' ).contents().find( 'body' );

		$iframeBody.on( 'mouseover', '.tooltip', function( e ) {
			e.preventDefault();
			$iframeBody.find( '.helper-message' ).hide();
			toggle_tooltip( this );
			e.stopPropagation();
		});

		$iframeBody.on( 'mouseleave', 'td', function( e ) {
			$iframeBody.find( '.helper-message' ).hide();
		});
	});

	$( 'body' ).on( 'mouseover', '.tooltip', function( e ) {
		toggle_tooltip( this );
	} );

	$( 'body' ).on( 'mouseleave', '.tooltip', function( e ) {
		$( '.helper-message' ).hide();
	} );

	$( '.notice.inline' )
		.appendTo('.bsr-notice-container' )
		.css( 'display', 'block' );

	setTimeout(function() {
		const $settings_saved_notice = $( '#setting-error-settings_updated' );
		const $bsr_notices = $( '.bsr-updated' );

		if ( $settings_saved_notice.length || $bsr_notices.length ) {
			$( '<div class="bsr-inner-notice-container"></div>' ).prependTo( '.inside' );
			$settings_saved_notice.prependTo( '.bsr-inner-notice-container' ).css( 'display', 'block' );
			$bsr_notices.prependTo( '.bsr-inner-notice-container' ).css( 'display', 'block' );
		}

		$( '.bsr-inner-notice-container .notice-dismiss' ).on( 'click', function ( e ) {
			if ( ! $bsr_notices.length ) {
				$( '.bsr-inner-notice-container' ).remove();
			}
		});
	}, 75);



})( jQuery );
