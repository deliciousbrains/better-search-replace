(function( $ ) {
	'use strict';

	/**
	 * Initializes our event handlers.
	 */
	function bsr_init() {
		bsr_search_replace();
		bsr_update_slider();
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
						$('.bsr-spinner').remove();
						$('.bsr-progress-wrap').remove();
						$('.bsr-disabled').removeClass('bsr-disabled button-disabled' );
						window.location = response.url;
					}

				} else {
					bsr_update_progress_bar( response.percentage );
					bsr_process_step( action, response.step, response.page, response.bsr_data );
				}

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

	}

	/**
	 * Initializes a search/replace.
	 */
	function bsr_search_replace() {

		var search_replace_submit = $( '#bsr-submit' );
		search_replace_submit.click( function( e ) {

			e.preventDefault();

			if ( ! search_replace_submit.hasClass( 'button-disabled' ) ) {

				var data = $( '.bsr-action-form' ).serialize();

				search_replace_submit.addClass( 'bsr-disabled button-disabled' );
				$( '#bsr-submit-wrap' ).append('<div class="spinner is-active bsr-spinner"></div><div class="bsr-progress-wrap"><div class="bsr-progress"></div></div>');
				bsr_process_step( 'process_search_replace', 0, 0, data );
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
	function bsr_update_slider( percentage ) {
		$('#bsr-slider').slider({
			value: bsr_object_vars.page_size,
			range: "min",
			min: 1000,
			max: 50000,
			step: 1000,
			change: function( event, ui ) {
				$('#bsr-page-size-value').text( ui.value );
				$('#bsr_page_size').val( ui.value );
			}
		});
	}

	bsr_init();

})( jQuery );
