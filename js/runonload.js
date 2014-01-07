//	Run the ad blocker.
Abd_Detector({
	noBlockerFunc: function() {
		jQuery('div.ABD_display_noadblock').show();
		jQuery('div.ABD_display_adblock').hide();
	},
	blockerFunc: function() {
		jQuery('div.ABD_display_noadblock').hide();
		jQuery('div.ABD_display_adblock').show();
	},
	debugMessage: true
});