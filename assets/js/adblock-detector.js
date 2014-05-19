/**
 * This file contains the JavaScript required to detect ad blocking browser
 * extensions and display/hide the appropriate content.
 */

function Abd_Detector (options) {
	var self = this;	//	Pointer to main object at any scope below this.

	/**
	 * A function used to output debugging information. This can be overloaded
	 * in the options object.
	 */
	this.debugMsg = function(msg, level) {
		//	options.debugMessage has either a boolean true if we are supposed 
		//	to output messages on our own, a boolean false if we are not, or a 
		//	function that deals with debug messages
		
		if (options.debugMessage) {
			//	Is it a function to take care of the message?
			if (typeof(options.debugMessage) == 'function') {
				//	Okay, then call it and pass the message and log level.
				options.debugMessage(msg, level);
			}
			else {
				//	Then we need to handle it ourselves. Let's output to the
				//	console any and all messages if debugging is allowed.
				console.log("ABD Detector:: " + msg);
			}
		}
		else {
			//	Then we aren't supposed to have debug messages... Don't do 
			//	anything.
		}
	}

	/**
	 * Adds HTML elements to the DOM that will likely bait ad blockers into removing or hiding.
	 * @return void
	 */
	this.loadFakeAds = function() {
		jQuery(document).ready(function() {
			//	Make a juicy ad like iframe.
			var frame = jQuery('<iframe/>', {
				id: 'abd-ad-iframe',

				//	junk URL that would set off an ad blocker's alarm bells
				src: 'http://exadwese.us/adserver/adlogger_tracker.php',
				
				//	make it even more juicy by making it a common ad size
				height: '728',
				width: '90',

				//	and now let's hide it from the typical user so they don't
				//	see an ugly empty iframe by moving it way up and left,
				//	but mark it as visible to ad blockers by setting display and
				//	visible
				style: 'position: absolute; top: -1000px; left: -1000px; display: block; visibility: visible;'
			});
			frame.appendTo('body');
			self.debugMsg("Inserting fake ad iframe");		


			//	Okay, just in case ad blockers get smart and ingore the iframe,
			//	let's make a div that looks like an advertisement, and
			//	size it and hide it like we did the iframe.
			var div = jQuery("<div id='abd-ad-div' style='position: absolute; top: -1000px; left: -1000px; display: block; visibility: visible; width: 336px; height: 280px;'>Advertisment ad adsense adlogger</div>");
			div.appendTo('body');
			self.debugMsg("Inserting fake ad div");
		});
	};	//	end this.loadFakeAds

	/**
	 * Checks to see if the bait ads inserted by Abd_Detector.loadFakeAds() 
	 * are present. Note that this function does not wait for the DOM to fully 
	 * load, the bait to be inserted, or the presumed ad blocker to process 
	 * anything.  You will likely want to delay the execution of this function 
	 * using setTimeout, or similar, to prevent false positives.
	 * @return boolean true if bait ads are present and unharmed, false if ad is missing or hidden.
	 */
	this.checkAdStatus = function() {
		//	Okay, let's make some easy variable to make things readable.
		var frame = jQuery('#abd-ad-iframe');
		var frameNoJq = document.getElementById("abd-ad-iframe");

		var div = jQuery('#abd-ad-div');
		var divNoJq = document.getElementById("abd-ad-div");

		var retVal = true;

		//	Ad blockers can hide things in numerous ways. Check every way I
		//	can think of.

		//	Check for the appended frame
		if (frame.length === 0) {	//	jQuery couldn't find it in the DOM
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: jQuery selector empty - $('#abd-ad-iframe').length === 0)");
		}
		else if (frameNoJq == undefined) {	//	JavaScript (no jQuery) couldn't find it
			retval = false;

			self.debugMsg("iframe removal detected! (Detection Method: no element with id found - document.getElementById == undefined")
		}
		else if (frame.height < 50) { //	Frame resized too small
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe height resized to near 0 - $('#abd-ad-iframe').height < 50");
		}
		else if (frame.is("hidden")) {	//	Frame hidden via visibility or display
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe hidden - $('#abd-ad-iframe').is('hidden')");
		}
		else if (frame.find(":hidden").length !== 0) {	//	Yet another way to look for hidden frame in case blockers are tricky
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe hidden - $('$abd-ad-iframe').find(':hidden').length === 0");
		}
		else if (frame.css('visibility') === 'hidden' || frame.css('display') === 'none') {	//	and again... another way to look for hidden frame
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe css changed to hidden - frame.css('visibility') === 'hidden' || frame.css('display') === 'none'");
		}

		else {	//	Well... I'm out of ideas... the damn thing must be there.
			self.debugMsg("No iframe removal detected.");
		}


		//	Check for appended div
		if (div.length === 0) {	//	jQuery couldn't find it in the DOM
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: jQuery selector empty");
		}
		else if (divNoJq == undefined) {	//	JavaScript (no jQuery) couldn't find it
			retval = false;

			self.debugMsg("div removal detected! (Detection Method: no element with id found - document.getElementById == undefined")
		}
		else if (div.height === 0) { //	Frame resized too small
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: height resized to 0");
		}
		else if (div.is("hidden")) {	//	Frame hidden via visibility or display
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: div hidden");
		}
		else if (div.find(":hidden").length !== 0) {	//	Yet another way to look for hidden div in case blockers are tricky
			retVal = false;

			self.debugMsg("iframe removal detected! (Detection Method: iframe hidden - $('$abd-ad-iframe').find(':hidden').length === 0");
		}
		else if (div.css('visibility') === 'hidden' || div.css('display') === 'none') {	//	Yet another way to look for hidden div in case blockers are tricky
			retVal = false;

			self.debugMsg("div removal detected! (Detection Method: CSS visibility or display altered!)");
		}

		else {	//	Well... I'm out of ideas... the damn thing must be there.
			self.debugMsg("No div removal detected");
		}


		//	Check for bait javascript file (assets/js/advertisement.js) flags
		if (window.abd_script_load_flag !== true) {
			//	Then the bait javascript file, advertisement.js, was not loaded or run
			retVal = false;

			self.debugMsg("js removal detected! (Detection Method: Bait javascript file prevented from loading and execution!)");
		}
		else {	//	It's there
			self.debugMsg("No js removal detected");
		}


		//	Okay, retVal should still be true from its declaration above if no
		//	blocking was detected.  If it was detected, then it was set to false.
		//	Which is what we want to return.
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


//	Let's run the damn thing and see what we can catch
//	Run the ad blocker.
Abd_Detector({
	//	What do we want to do if no ad blockers are detected?
	noBlockerFunc: function() {
		jQuery('div.ABD_display_noadblock').show();
		jQuery('div.ABD_display_adblock').hide();
	},
	//	What do we want to do if we detect an ad blocker?
	blockerFunc: function() {
		jQuery('div.ABD_display_noadblock').hide();
		jQuery('div.ABD_display_adblock').show();
	},
	//	Do we want to see debug messages?
	debugMessage: true
});