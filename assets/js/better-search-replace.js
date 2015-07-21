
(function( $ ) {
	'use strict';

	$('#bsr-slider').slider({
		value: bsr_object_vars.page_size,
		range: "min",
		min: 5000,
		max: 50000,
		step: 5000,
		change: function( event, ui ) {
			$('#bsr-page-size-value').text( ui.value );
			$('#bsr_page_size').val( ui.value );
		}
	});

})( jQuery );
