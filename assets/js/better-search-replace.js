(function( $ ) {
	'use strict';

	/**
	 * Initializes our event handlers.
	 */
	function bsr_init() {
		bsr_search_replace();
		bsr_update_sliders();
		bsr_thickbox_fix();
	}

	/**
	 * Builds the custom admin URL with a valid bsr-ajax query value.
	 *
	 * @param {string} action Admin action to be passed as a query arg to endpoint.
	 */
	function bsr_ajax_url( action ) {
		var base = bsr_object_vars.endpoint;
		return base + ( base.indexOf( '?' ) !== -1 ? '&' : '?' ) + 'bsr-ajax=' + encodeURIComponent( action );
	}

	/**
	 * Recursive function for performing batch operations.
	 */
	function bsr_process_step( action, step, page, data ) {

		$.ajax({
			type: 'POST',
			url: bsr_ajax_url( action ),
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
					$( '<p class="description bsr-description"></p>' ).text( response.message ).appendTo( '.bsr-progress-wrap' );
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

	/**
	 * Fixes the Thickbox iframe src truncation.
	 *
	 * Core thickbox.js truncates iframe src at the substring "TB_"; this restores the full URL when action=bsr_view_details.
	 */
	function bsr_thickbox_fix() {
		document.addEventListener('DOMContentLoaded', function() {
			if ( typeof window.tb_show !== 'function' ) {
				return;
			}

			var origTbShow = window.tb_show;

			window.tb_show = function ( caption, url, imageGroup ) {
				origTbShow.call( this, caption, url, imageGroup );

				if ( typeof url !== 'string' || url.indexOf( 'action=bsr_view_details' ) === -1 ) {
					return;
				}

				var applySrc = function () {
					var el = document.getElementById( 'TB_iframeContent' );
					if ( el ) {
						el.setAttribute( 'src', url );
					}
				};

				if ( window.requestAnimationFrame ) {
					window.requestAnimationFrame( function () {
						window.requestAnimationFrame( applySrc );
					} );
				} else {
					window.setTimeout( applySrc, 0 );
				}
			};
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
