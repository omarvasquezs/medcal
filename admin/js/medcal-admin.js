(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	/**
	 * Initialize drag and drop functionality when the DOM is ready
	 */
	$(function() {
		// Initialize sortable for procedure cards
		if ($('#sortable-procedures').length > 0) {
			$('#sortable-procedures').sortable({
				handle: '.medcal-drag-handle',
				placeholder: 'medcal-procedure-card-placeholder',
				opacity: 0.8,
				update: function(event, ui) {
					// Get the new order of procedures
					const procedureOrder = [];
					$('#sortable-procedures .medcal-procedure-card').each(function() {
						procedureOrder.push($(this).data('procedure-id'));
					});
					
					// Save the new order via AJAX
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'medcal_update_procedure_order',
							procedure_order: procedureOrder,
							nonce: medcal_vars.procedure_order_nonce
						},
						success: function(response) {
							if (response.success) {
								// Show success message
								const noticeContainer = $('<div class="notice notice-success is-dismissible"><p>' + medcal_vars.order_success_message + '</p></div>');
								$('.medcal-admin-section').first().prepend(noticeContainer);
								
								// Auto-dismiss the notice after 3 seconds
								setTimeout(function() {
									noticeContainer.fadeOut('slow', function() {
										$(this).remove();
									});
								}, 3000);
							} else {
								// Show error message
								alert(medcal_vars.order_error_message);
							}
						},
						error: function() {
							// Show generic error message
							alert(medcal_vars.order_error_message);
						}
					});
				}
			});
			
			// Add placeholder styling
			$('<style>.medcal-procedure-card-placeholder { background: #e0e0e0; border: 2px dashed #aaa; height: 100px; margin-bottom: 15px; border-radius: 5px; }</style>').appendTo('head');
		}
	});

})( jQuery );
