/**
 * This file contains the JavaScript required to detect ad blocking browser
 * extensions and display/hide the appropriate content.
 */

window.Abd_Detector_Debug_Messages = [];

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


		//	Save a running tally of log messages.
		Abd_Detector_Debug_Messages.push(msg);
	};

	/**
	 * Adds HTML elements to the DOM that will likely bait ad blockers into removing or hiding.
	 * @return void
	 */
	this.loadFakeAds = function() {
		//	All fake ads are now inserted by WordPress enqueueing.
		//	See the includes/setup.php file. In the ABD_Setup class,
		//	there is a function, enqueue_helper_footer() where the
		//	fake ads are loaded.  This is done to prevent odd dynamic
		//	element insertion errors.

		//	If you want to load any other ad type material, you can try
		//	it here.

		//////////////////////////////////////
		//	IFRAME FRAME BUST PREVENTION	//
		//////////////////////////////////////
		//	The iframe is only written to the page if JavaScript is present and
		//	the browser supports some important iframe attributes that will prevent
		//	a the iframe from busting out and redirecting the parent page.  Check the
		//	file mentioned above to see how that is done specifically.
		//
		//	To make debugging simpler, let's output a log message if the iframe is missing.
		if( !self.iframeSecurityPresent() ) {
			self.debugMsg( "Browser lacks necessary iframe security attributes. Omitting iframe checks!" );
		}
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


		var retVal = {
			blockerDetected: false,
			status: 'noadblock',
			detectionMethod: 'N/A'
		};

		//	Ad blockers can hide things in numerous ways. Check every way I
		//	can think of.
		if(ABDSettings.enableIframe == 'yes' || ABDSettings.enableIframe == '') {
			//	Check for the appended frame
			//
			//	Okay, we have a problem... We might not have loaded the iframe if the browser
			//	doesn't support some security features that prevent frame busting.  If
			//	the iframe is missing for this reason, the checks we're about to run will
			//	yield a false positive.  So, make sure the iframe was inserted before
			//	running the checks!
			if( self.iframeSecurityPresent() ) {	//	The iframe should be there! Run the checks.
				retVal = self.checkAdStatusIframeHelper('#abd-ad-iframe', frame, frameNoJq, retVal);
			}
		}
		else {
			self.debugMsg("iframe detection disabled");
		}


		if( ABDSettings.enableDiv == 'yes' || ABDSettings.enableDiv == '' ) {
			retVal = self.checkAdStatusDivHelper(div, divNoJq, retVal);
		}
		else {
			self.debugMsg("div detection disabled");
		}


		if( ABDSettings.enableJsFile == 'yes' || ABDSettings.enableJsFile == '' ) {
			//	Check for bait javascript file (assets/js/advertisement.js) flags
			if (window.abd_script_load_flag !== true) {
				//	Then the bait javascript file, advertisement.js, was not loaded or run
				mthd_text = "js removal detected! (Detection Method: Bait javascript file prevented from loading and execution!)";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else {	//	It's there
				self.debugMsg("No js removal detected");
			}
		}
		else {
			self.debugMsg("js detection disabled");
		}


		//	Now, go through user defined selectors checks
		if( ABDSettings.cssSelectors ) {
			var selectors = ABDSettings.cssSelectors;
		}
		else {
			var selectors = [];
		}
		jQuery.each(selectors, function(index, val) {
			//	Is this empty or not a string?
			if(!val || typeof val != 'string') {
				return true;	//	jQuery each() equivalent of continue;
			}

			//	Is this a JSON string?
			var value = val;
			try {
				value = jQuery.parseJSON( value );
			}
			catch(e) {
				value = val;
			}

			self.debugMsg("checking user defined selector: " + value);

			//	Select this item, and make sure it exists
			var item = jQuery(value);
			if( item.length ) {	//	item exists
				self.debugMsg("found " + item.length + " matches for the specified selector");
			}
			else {
				self.debugMsg("specified selector doesn't match any element on page");
				return;	//	jQuery.each equivalent of continue statement
			}

			//	Iterate through each match
			jQuery(value).each(function(ind, elem) {				
				//	Is the wrapper height shrunk to zero?
				if(jQuery(elem).height() < 50) {
					mthd_text = "user defined wrapper removal detected! (Detection Method: wrapper height resized to near 0 - $(selector).height < 50";

					retVal = {
						blockerDetected: true,
						status: 'adblock',
						detectionMethod: mthd_text
					}

					self.debugMsg(mthd_text);
				}

				//	Is the wrapper empty?
				if(jQuery.trim(jQuery(elem).html()) == '') {
					mthd_text = "user defined wrapper removal detected! (Detection Method: wrapper is empty - $.trim($(selector).html()) == ''";

					retVal = {
						blockerDetected: true,
						status: 'adblock',
						detectionMethod: mthd_text
					}

					self.debugMsg(mthd_text);
				}

				//	If there is an iframe within the wrapper, is it screwed up?
				jQuery(elem).find('iframe').each(function() {
					retVal = self.checkAdStatusIframeHelper(value, jQuery(elem), 'skip', retVal);
				});
			});
		});


		//	Okay, retVal should still be true from its declaration above if no
		//	blocking was detected.  If it was detected, then it was set to false.
		//	Which is what we want to return.
		return retVal;
	};	//	end this.checkAdStatus

		this.checkAdStatusIframeHelper = function( selector, frameJq, frameNoJq, retVal ) {
			if (frameJq.length === 0) {	//	jQuery couldn't find it in the DOM
				mthd_text = "iframe removal detected! (Detection Method: jQuery selector empty - $('" + selector + "').length === 0)";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (frameNoJq == undefined) {	//	JavaScript (no jQuery) couldn't find it
				mthd_text = "iframe removal detected! (Detection Method: no element with id found - document.getElementById(" + selector + ") == undefined";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (frameJq.height < 50) { //	Frame resized too small
				mthd_text = "iframe removal detected! (Detection Method: iframe height resized to near 0 - $('" + selector + "').height < 50";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (frameJq.is("hidden")) {	//	Frame hidden via visibility or display
				mthd_text = "iframe removal detected! (Detection Method: iframe hidden - $('" + selector + "').is('hidden')";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (frameJq.find(":hidden").length !== 0) {	//	Yet another way to look for hidden frame in case blockers are tricky
				mthd_text = "iframe removal detected! (Detection Method: iframe hidden - $('" + selector + "').find(':hidden').length === 0";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (frameJq.css('visibility') === 'hidden' || frameJq.css('display') === 'none') {	//	and again... another way to look for hidden frame
				mthd_text = "iframe removal detected! (Detection Method: iframe css changed to hidden - $('" + selector + "').css('visibility') === 'hidden' || $('" + selector + "').css('display') === 'none')";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}

			else {	//	Well... I'm out of ideas... the damn thing must be there.
				self.debugMsg("No iframe removal detected.");
			}

			return retVal;
		}	//	end this.checkAdStatusIframeHelper
		this.checkAdStatusDivHelper = function( div, divNoJq, retVal ) {
			//	Check for appended div
			if (div.length === 0) {	//	jQuery couldn't find it in the DOM
				mthd_text = "div removal detected! (Detection Method: jQuery selector empty)";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (divNoJq == undefined) {	//	JavaScript (no jQuery) couldn't find it
				mthd_text = "div removal detected! (Detection Method: no element with id found - document.getElementById == undefined";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (div.height === 0) { //	Frame resized too small
				mthd_text = "div removal detected! (Detection Method: height resized to 0";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (div.is("hidden")) {	//	Frame hidden via visibility or display
				mthd_text = "div removal detected! (Detection Method: div hidden";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (div.find(":hidden").length !== 0) {	//	Yet another way to look for hidden div in case blockers are tricky
				mthd_text = "iframe removal detected! (Detection Method: iframe hidden - $('$abd-ad-iframe').find(':hidden').length === 0";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}
			else if (div.css('visibility') === 'hidden' || div.css('display') === 'none') {	//	Yet another way to look for hidden div in case blockers are tricky
				mthd_text = "div removal detected! (Detection Method: CSS visibility or display altered!)";

				retVal = {
					blockerDetected: true,
					status: 'adblock',
					detectionMethod: mthd_text
				}

				self.debugMsg(mthd_text);
			}

			else {	//	Well... I'm out of ideas... the damn thing must be there.
				self.debugMsg("No div removal detected");
			}

			return retVal;
		}	//	end this.checkAdStatusDivHelper

	/**
	 * Loads bait ads, checks to see if they were blocked, and executes the appropriate action.
	 * @param  {function} noBlockerFunc The function to execute if no ad blocker is detected.
	 * @param  {function} blockerFunc   The function to execute if an ad blocker is detected.
	 * @return {void}
	 */
	this.executeFunc = function(noBlockerFunc, blockerFunc) {
		var self = this;	//	We're gonna want this this inside the .ready
							//	function.  Not that this.  So save this this
							//	and use it instead of that this.

		jQuery(document).ready(function () {
			self.loadFakeAds();

			//	Okay, now we want to detect the Ad Status.
			//	However, for God only knows what reason, sometimes
			//	this executes before loadFakeAds has finished appending and
			//	inserting the fake ads.  It shouldn't.  But it does.
			//
			//	And to make things worse, it doesn't do it on YOUR web browser,
			//	only the browsers of other unfortunate souls.  So you can't test.
			//	You can't experiment.  You can't find the cause.  Only treat the
			//	symptom.  So let's treat it.
			//
			//	We need a little timeout to cool our heels before we get
			//	cracking.
			//
			//
			//
			//	I know what you're thinking... this is bullshit.  John's an idiot.
			//	There's no way on this Earth that this needs a timeout.
			//
			//	Well, this is bullshit... and John may very well be an idiot.  But
			//	there needs to be a timeout here.  If you decide to tinker with
			//	this, be VERY CAREFUL!  Or, you know... don't.
			setTimeout(function() {
				var res = self.checkAdStatus();
				
				if (!res.blockerDetected) {
					noBlockerFunc(res);
				}
				else {
					blockerFunc(res);
				}
			}, 500);
		});
	};

	/**
	 * Defers calling of a function until jQuery has loaded or it is impractical to
	 * wait any longer.
	 * @param  function funcToRun    The function to run once jQuery has loaded.
	 * @param  int recurseDepth 	 (optional) The number of times left in recursion. Usually should be omitted.
	 * @param  int totalTime    	 (optional) The number of milliseconds waited so far. Usually should be omitted.
	 * @return int                   The total time spent waiting in milliseconds AFTER RECURSION HAS FINISHED. Not available during recursion.  -1 If defer failed
	 */
	this.jQueryDefer = function( funcToRun, recurseDepth, totalTime ) {
		//	Setup defaults
		if( typeof recurseDepth === 'undefined' ) {
			recurseDepth = 100;
		}
		if( typeof totalTime === 'undefined' ) {
			totalTime = 0;
		}
		var waitTime = 25;


		//	Does jQuery exist?
		if( typeof jQuery !== 'undefined' ) {
			//	Yes, it does, so log any fun messages and run the function.
			if( totalTime > 0 ) {
				self.debugMsg( "Asynchronous jQuery detected. ABD waited " + totalTime/1000 + " seconds for jQuery to load." );
			}
			funcToRun();
			return totalTime;
		}
		else if( recurseDepth > 0 ) {
			//	No, it doesn't, and we still have time to wait. Recurse another level.
			setTimeout( function() {
				self.jQueryDefer( funcToRun, --recurseDepth, totalTime + waitTime );
				},
			waitTime );
		}
		else {
			//	No, it doesn't, and we've waited long enough.  Throw an error and die.
			self.debugMsg( "Cannot run!  jQuery didn't load.  Try adding an exception for jQuery in any asynchronous JavaScript plugins. Total wait time: " + totalTime/1000 + " seconds." );
			return -1;
		}
	}

	/**
	 * Determines whether the browser supports either the sandbox or the security
	 * attribute for iframes.  This is important for detecting frame busting in
	 * the bait ad iframe.
	 */
	this.iframeSecurityPresent = function() {
		var frm = document.createElement('iframe');
		if( ('sandbox' in frm) || ('security' in frm) ) {
			return true;
		}

		return false;
	}

	////////////////
	//	Constructor
	////////////////
	this.allonsy = function() {
		this.executeFunc( options.noBlockerFunc, options.blockerFunc );
	}
	//	Wait until jQuery is loaded, then call this.executeFunc which is wrapped
	//	in this.allonsy so we can pass parameters.
	this.jQueryDefer( this.allonsy );

}	//	end Abd_Detector


//	Let's run the damn thing and see what we can catch
//	Run the ad blocker.
Abd_Detector({
	//	What do we want to do if no ad blockers are detected?
	noBlockerFunc: function(res) {
		jQuery('div.ABD_display_noadblock').show();
		jQuery('div.ABD_display_adblock').hide();

		//	Add class to body tag
		jQuery('body').addClass('ABD_noadblock');

		//	Throw event
		jQuery(document).trigger('abd_status_change', res);
	},
	//	What do we want to do if we detect an ad blocker?
	blockerFunc: function(res) {
		jQuery('div.ABD_display_noadblock').hide();
		jQuery('div.ABD_display_adblock').show();

		//	Add class to body tag
		jQuery('body').addClass('ABD_adblock');

		//	Throw event		
		jQuery(document).trigger('abd_status_change', res);
	},
	//	Do we want to see debug messages?
	debugMessage: true
});
