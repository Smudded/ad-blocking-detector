function Abd_Detector (options) {
	var self = this;

	this.debugMsg = function(msg, level) {
		if (options.debugMessages) {
			if (typeof(options.debugMessage) == 'function') {
				options.debugMessage(msg, level);
			}
			else {
				console.log("Abd_Detector:: " + msg);
			}
		}
	}

	/**
	 * Adds HTML elements to the DOM that will likely bait ad blockers into removing or hiding.
	 * @return void
	 */
	this.loadFakeAds = function() {
		jQuery(document).ready(function() {
			var frame = jQuery('<iframe/>', {
				id: 'abd-ad-iframe',
				src: 'http://exadwese.us/adserver/adlogger_tracker.php',	//	junk URL that would set off an ad blocker's alarm bells
				height: '728',
				width: '90',
				style: 'position: absolute; top: -1000px; left: -1000px;'
			});

			frame.appendTo('body');

			self.debugMsg("Inserting fake ad iframe (" + frame.html() + ").");			
		});
	};	//	end this.loadFakeAds

	/**
	 * Checks to see if the bait ads inserted by Abd_Detector.loadFakeAds() are present.
	 * Note that this function does not wait for the DOM to fully load, the bait to be
	 * inserted, or the presumed ad blocker to process anything.  You will likely want
	 * to delay the execution of this function using setTimeout, or similar, to prevent 
	 * false positives.
	 * @return boolean true if bait ads are present and unharmed, false if ad is missing or hidden.
	 */
	this.checkAdStatus = function() {
		var frame = jQuery('#abd-ad-iframe');
		var retVal = true;

		if (frame.length === 0) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: jQuery selector empty - $('#abd-ad-iframe').length === 0)");
		}
		else if (frame.height === 0) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe height resized to 0 - $('#abd-ad-iframe').height === 0");
		}
		else {
			self.debugMsg("No iframe removal detected.");
		}


		return retVal;
	};	//	end this.checkAdStatus

	/**
	 * Loads bait ads, checks to see if they were blocked, and executes the appropriate action.
	 * @param  {function} noBlockerFunc The function to execute if no ad blocker is detected.
	 * @param  {function} blockerFunc   The function to execute if an ad blocker is detected.
	 * @return {void}
	 */
	this.executeFunc = function(noBlockerFunc, blockerFunc) {
		this.loadFakeAds();

		setTimeout(function() {
			if (this.checkAdStatus()) {
				noBlockerFunc();
			}
			else {
				blockerFunc();
			}
		}, 300);
	};

	////////////////
	//	Constructor
	////////////////
	this.executeFunc(options.noBlockerFunc, options.blockerFunc);
}	//	end Abd_Detector