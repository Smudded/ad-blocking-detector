(function($) {
	$(document).ready(function() {

		//////////////////////////////////////////
		//	Record Ad Blocking Detector Stats	//
		//////////////////////////////////////////
		$(document).on("abd_status_change", function(event, data) {
			console.log('ABD Detector:: Running statistics AJAX query.');

			var sd = {
				'adblocker': -1,
				'_wpnonce': ABDSettings.statsAjaxNonce,
				'action': 'submit_stats'
			}
			if(data.blockerDetected) {
				sd.adblocker = 1;
			}
			else {
				sd.adblocker = 0;
			}


			$.post(
				ABDSettings.ajaxUrl, 
				sd, 
				function(response) {
					console.log('ABD Detector:: Statistics AJAX query finished. Response: ' + response);
				}
			);
		});

	});	//	end $(document).ready(function() {
})(jQuery);