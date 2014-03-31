function Abd_Detector (options) {
	var self = this;

	this.debugMsg = function(msg, level) {
		if (options.debugMessage) {
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
				style: 'position: absolute; top: -1000px; left: -1000px; display: block; visibility: visible;'
			});
			frame.appendTo('body');
			self.debugMsg("Inserting fake ad iframe");		


			var div = jQuery("<div id='abd-ad-div' style='position: absolute; top: -1000px; left: -1000px; display: block; visibility: visible;'>Advertisment ad adsense adlogger</div>");
			div.appendTo('body');
			self.debugMsg("Inserting fake ad div");
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
		var frameNoJq = document.getElementById("abd-ad-iframe");

		var div = jQuery('#abd-ad-div');
		var divNoJq = document.getElementById("abd-ad-div");

		var retVal = true;

		//	Appended frame
		if (frame.length === 0) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: jQuery selector empty - $('#abd-ad-iframe').length === 0)");
		}
		else if (frameNoJq == undefined) {
			retval = false;

			self.debugMsg("iframe removal detected! (Detection Method: no element with id found - document.getElementById == undefined")
		}
		else if (frame.height < 50) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe height resized to near 0 - $('#abd-ad-iframe').height < 50");
		}
		else if (frame.is("hidden")) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe hidden - $('#abd-ad-iframe').is('hidden')");
		}
		else if (frame.find(":hidden").length !== 0) {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe hidden - $('$abd-ad-iframe').find(':hidden').length === 0");
		}
		else if (frame.css('visibility') === 'hidden' || frame.css('display') === 'none') {
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe css changed to hidden - frame.css('visibility') === 'hidden' || frame.css('display') === 'none'");
		}
		else {
			self.debugMsg("No iframe removal detected.");
		}

		//	Appended div
		if (div.length === 0) {
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: jQuery selector empty");
		}
		else if (div.height === 0) {
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: height resized to 0");
		}
		else if (div.is("hidden")) {
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: div hidden");
		}
		else if (div.css('visibility') === 'hidden' || div.css('display') === 'none') {
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: CSS visibility or display altered!)");
		}
		else {
			self.debugMsg("No div removal detected");
		}

		//	Bait javascript file
		if (window.abd_script_load_flag !== true) {
			//	Then the bait javascript file, advertisement.js, was not loaded or run
			retVal = false;

			self.debugMsg("js removal detected! (Detection Method: Bait javascript file prevented from loading and execution!)");
		}
		else {
			self.debugMsg("No js removal detected");
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
		}, 500);
	};

	////////////////
	//	Constructor
	////////////////
	this.executeFunc(options.noBlockerFunc, options.blockerFunc);
}	//	end Abd_Detector