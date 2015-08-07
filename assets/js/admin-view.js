(function($) {
	$(document).ready(function() {

		//////////////////////////////////////
		//	Hide Ad Blocker and JS warning 	//
		//////////////////////////////////////
		//	Ad blockers that are problematic prevent this file from loading, so we simply
		//	need a command to hide the warning. If there is no ad blocker, this will run.
		$('#disable-ad-blocker-enable-js-warning').hide();

		//////////////////////////////////////////////////////////////////////
		//	Turn noadblocker and adblocker fields into CodeMirror editors	//
		//////////////////////////////////////////////////////////////////////
		$('textarea').each(function() {
			var taName = $(this).attr('name');
			var taId   = $(this).attr('id');
			
			if(taName && (taName.indexOf("[noadblocker]") > -1 || taName.indexOf("[adblocker]") > -1)) {
				txtArea = document.getElementById( taId );

				if( $(this).is('[readonly="readonly"]') ) {
					var readOnlyVal = true;
				}
				else {
					var readOnlyVal = false;
				}

				CodeMirror.fromTextArea(txtArea, {
					mode: "htmlmixed", 
					lineNumbers: true,
					readOnly: readOnlyVal
				});
			}
		});	//	end $('textarea').each(function() {


		
		//////////////////////////////////////////////////////
		/// Masonry Block the Getting Started & Help Page	//
		//////////////////////////////////////////////////////
		var masonryContainer = $('.abd-masonry-wrapper');
		masonryContainer.imagesLoaded( function() {
			masonryContainer.masonry({
				itemSelector: '.abd-masonry-block',
				columnWidth: 1	//	Multi-width Masonry blocks: http://stackoverflow.com/a/12493089/2523144
			});
		});


		//////////////////////////////////////////////////
		//	On window resize, redraw Masonry Blocks 	//
		//////////////////////////////////////////////////
		//	Be sure to only do this when resizing is finished as some browsers continuously
		//	spam this event during a resize. 
		var resizeId;
		$(window).resize(function() {
			clearTimeout(resizeId);
			resizeId = setTimeout(doneResizing, 500);
		})
		function doneResizing() {
			masonryContainer.masonry('reload');
		}

		//////////////////////////////////
		//	Minimizable Masonry Blocks	//
		//////////////////////////////////
		masonryContainer.children('div.abd-masonry-block').each(function() {
			var container = $(this);
			var h3 = container.children('h3:not(.ui-accordion-header)');			

			//	Setup click listener on heading to toggle display
			h3.click(function() {
				container.children(':not(h3)').toggle();

				masonryContainer.masonry('reload');				
			});

			//	Turn mouse pointers into hand on h3
			h3.css('cursor', 'pointer');
		});

		//////////////////////////
		//	Accordion Sections 	//
		//////////////////////////
		$('.abd-accordion').accordion({
			active: false, 
			collapsible: true,

			//	No scrollbars, auto height adjustment, et cetera
			//	http://stackoverflow.com/a/15413662/2523144
			heightStyle: "content",
			autoHeight: false,
			clearStyle: true,

			//	Readjust Masonry blocks on expand/collapse				
			activate: function(event, ui) {
				masonryContainer.masonry('reload');	
			}
		});
		$('.abd-accordion').addClass('abd-jqui').wrap('<div class="abd-jqui" />');	//	jQuery UI theme scope

		//	Sometimes the layout isn't right after accordioning, so force re-arrange the boxes
		masonryContainer.masonry('reload');

		

		//////////////////////////////////////////////////////////////
		//	Open Popups Rather Than Full Pages	//
		//////////////////////////////////////////////////////////////
		$('a.abd-popup').click(function(event) {
			event.preventDefault();

			window.open($(this).attr('href'), $(this).attr('title'), "width=900,height=600" );
		});


		//////////////////////////////////////////
		//	Get This Shortcode Button Handling	//
		//////////////////////////////////////////
		$('a.abd-shortcode-get-button').click(function() {
			var sc_id = $(this).data('id');
			var shortcode = '[adblockingdetector id="' + sc_id + '"]';

			var inst = '<p>' + objectL10n.copyShortcodeInstructions + '</p>';
			var scinput = '<input id="abd-sc-copy-dialog-input" style="min-width: 320px;" type="text" readonly="readonly" value=\'' + shortcode + '\' />';

			var gsDialog = $("<div/>").html(inst + scinput).dialog( {
				modal: true,
				title: objectL10n.copyShortcodeDialogTitle,
				width: 350,
				position: {my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.close,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],
				open: function() {
					//	Highlight input text on focus
					$('#abd-sc-copy-dialog-input').on("click", function () {
					   $(this).select();
					});

					//	Fix Random jQuery UI CSS Scoping Issues	//
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});
			gsDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});	//	end $('a.abd-shortcod-get-button').click(function() {


		//////////////////////////////////////////
		//	Delete This Shortcode Button Handling	//
		//////////////////////////////////////////
		$('a.abd-shortcode-delete-button').click(function(e) {
			e.preventDefault();
			var targetUrl = $(this).attr("href");

			var warning = '<p>' + objectL10n.deleteDialogWarning + '</p>';

			var dsDialog = $("<div/>").html(warning).dialog( {
				modal: true,
				title: objectL10n.deleteDialogTitle,
				width: 400,
				position: {my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.affirmative,
						icons: {primary: 'ui-icon-check'},
						click: function() {
							window.location.href = targetUrl;
						}
					},
					{
						text: objectL10n.nevermind,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],
				open: function() {
					//	Fix Random jQuery UI CSS Scoping Issues	//
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});
			dsDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});


		///////////////////////////////////////////
		//	Update Manual Plugin Download Button //
		///////////////////////////////////////////
		$('a.abd-update-manual-download-button').click(function(e) {
			var warning = '<p>' + objectL10n.updateManualPluginDownloadWarning + '</p>';

			var umDialog = $("<div/>").html(warning).dialog({
				modal: true,
				title: objectL10n.updateManualPluginDownloadTitle,
				width: 400,
				position: {my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.close,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],				
				open: function() {
					//	Fix Random jQuery UI CSS Scoping Issues	//
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});
			umDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});


		//////////////////////////////////////
		//	Download Manual Plugin Button 	//
		//////////////////////////////////////
		$('a.abd-download-manual-blc-plugin-button').click(function(e) {
			var warning = '<p>' + objectL10n.downloadManualPluginWarning + '</p>';

			var dmpDialog = $("<div />").html(warning).dialog({
				modal: true,
				title: objectL10n.downloadManualPluginTitle,
				width: 500,
				position: {my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.close,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],				
				open: function() {
					//	Fix Random jQuery UI CSS Scoping Issues	//
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});			
			dmpDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});



		//////////////////////////////////////
		//	Submit Log To Developer Button 	//
		//////////////////////////////////////
		$('a.abd-submit-log-to-dev-button').click( function(e) {
			var link = this;
			e.preventDefault();

			var msg = '<p>' + objectL10n.submitLogToDevWarning + '</p>';

			var sltdwDialog = $("<div />").html(msg).dialog({
				modal: true,
				title: objectL10n.submitLogToDevTitle,
				width: 500,
				position:{my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.affirmative,
						icons: {primary: 'ui-icon-check'},
						click: function() {
							window.location = link.href;
							$(this).html('<div style="text-align: center">' + objectL10n.wait + '<br />' + '<img src="' + abdPaths.abdRootUrl + 'assets/images/ajax-loader.gif" /></div>');
						}
					},
					{
						text: objectL10n.nevermind,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],
				open: function() {
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});
			sltdwDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});



		//////////////////////////////////////////////////////////
		//	Output Detection Results To TextArea on Debug Tab	//
		//////////////////////////////////////////////////////////
		var txtarea = $('#abd-results-textarea');
		if( txtarea.length ) {	//	TextArea exists
			$(document).on("abd_status_change", function(event, data) {
				if(data.blockerDetected) {
					txtarea.append('Blocker Detected:&#13;&#10; &nbsp; &nbsp;' + data.blockerDetected);
					txtarea.append('&#13;&#10;&#13;&#10;Last Successful Detection Method:&#13;&#10; &nbsp; &nbsp; &nbsp;' + data.detectionMethod);
				}
				else {
					txtarea.append('Blocker Detected: ' + data.blockerDetected);
				}

				//	Output debug messages
				var msgs = Abd_Detector_Debug_Messages;

				txtarea.append( '&#13;&#10;&#13;&#10;Ad Block Detection Script Log:&#13;&#10;' );
					
				if(Array.isArray(msgs)) {
					for( var i = 0; i < msgs.length; i++ ) {
						txtarea.append(' &nbsp; &nbsp; &nbsp; [' + i + '] ' + msgs[i] + '&#13;&#10;&#13;&#10;');
					}
				}
				else {
					txtarea.append( ' &nbsp; &nbsp; &nbsp; Error retrieving debug messages.&#13;&#10;&#13;&#10;' );
				}
			});
		}




		//////////////////////////////////////////////////////////////////////////////////////
		//	Try and Prevent ERROR: Option does not exist errors on Add New Shortcode tab	//
		//////////////////////////////////////////////////////////////////////////////////////
		//	These occur when the page sits too long after loading and before submission.
		//	So, let's notify the user and refresh the page after an hour of idling.

		if( getUrlParameter('page') == 'ad-blocking-detector' && getUrlParameter('tab') == 'new-shortcodes' ) {
			setTimeout(function() {
				var warning = objectL10n.idlingForceRefreshWarning;
				var title   = objectL10n.idlingForceRefreshTitle;

				var idleDialog = $("<div />").html(warning).dialog({
					modal: true,
					title: title,
					width: 400,
					position: {my: 'right bottom', at: 'middle'},
					buttons: [
						{
							text: objectL10n.close,
							icons: {primary: 'ui-icon-closethick'},
							click: function() {
								$(this).dialog("destroy");
								document.location.reload();
							}
						}
					],				
					open: function() {
						//	Fix Random jQuery UI CSS Scoping Issues	//
						$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
					}
				});			
				idleDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
			}, 3600000);
		}




		//////////////////////////
		//	Session Log Clock 	//
		//////////////////////////
		var dateField = $('#abd-js-date-time');		
		var textNoDate = $('#abd-js-date-time').html();
		
		function abdLogTime() {
			setTimeout( function() {				
				dateField.html( textNoDate + sessionLogTime() );
				abdLogTime();
			}, 500 );
		}
		
		if( dateField.length !== 0 ) {
			abdLogTime();
		}




		//////////////////////////////////
		//	Statistics Charts & Buttons	//
		//////////////////////////////////
		//	Load Google Charts
		//	We only need to do this on the statistics page... if the chart containers are
		//	present.  Let's check if the chart containers are present by picking one and
		//	seeing what we get.
		if( $('#abd-stats-chart-page-load').length ) {
			//	Yay!  We're on stats page and containers are present.		
			google.load('visualization', '1.0', {
				'packages':['corechart'],
				callback: function() {
					//	This is here to keep page from being blank
					//	http://stackoverflow.com/a/24980290
				}
			});
			google.setOnLoadCallback(drawCharts);
		}

		//	Reset Statistics Button Handling
		$('#abd-statistics-reset-button').click(function(e) {
			e.preventDefault();
			var targetUrl = $(this).attr("href");

			var warning = '<p>' + objectL10n.resetStatisticsWarning + '</p>';

			var dsDialog = $("<div/>").html(warning).dialog( {
				modal: true,
				title: objectL10n.resetStatisticsTitle,
				width: 400,
				position: {my: 'right bottom', at: 'middle'},
				buttons: [
					{
						text: objectL10n.affirmative,
						icons: {primary: 'ui-icon-check'},
						click: function() {
							window.location.href = targetUrl;
						}
					},
					{
						text: objectL10n.nevermind,
						icons: {primary: 'ui-icon-closethick'},
						click: function() {
							$(this).dialog("destroy");
						}
					}
				],
				open: function() {
					//	Fix Random jQuery UI CSS Scoping Issues	//
					$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="ffs abd-jqui" />');
				}
			});
			dsDialog.parent('.ui-dialog').addClass('abd-jqui');	//	jQuery UI theme scope
		});
		
		function drawCharts() {
			drawPageLoadChart('#336699', '#a93912');
			drawUserChart('#336699', '#a93912');
			drawDisableChart('#33CC33', '#990099');
			drawEnableChart('#33CC33', '#990099');
		}

		function drawPageLoadChart(goodColor, badColor, neutralColor) {
			var data = new google.visualization.DataTable();
			data.addColumn( 'string', 'Category' );
			data.addColumn( 'number', 'The Number' );
			data.addRows([
				['Ad Blockers Enabled (' + ABDStats.pageLoadAdblocker + ')', ABDStats.pageLoadAdblocker],
				['Ad Blockers Disabled (' + ABDStats.pageLoadNoAdblocker + ')', ABDStats.pageLoadNoAdblocker],
				['Other (' + ABDStats.pageLoadOther + ')', ABDStats.pageLoadOther]
			]);

			var options = {
				'title': 'Ad Blockers By Page Loads',
				'colors': [badColor, goodColor, neutralColor],
				'backgroundColor': '#f1f1f1',
				'fontSize': 18,
				'fontName': 'sans-serif',
				'legend': {position: 'bottom', textStyle: {fontSize: 15}}
			};

			var chart = new google.visualization.PieChart(document.getElementById('abd-stats-chart-page-load'));
			chart.draw(data, options);
		}

		function drawUserChart(goodColor, badColor, neutralColor) {
			var data = new google.visualization.DataTable();
			data.addColumn( 'string', 'Category' );
			data.addColumn( 'number', 'The Number' );
			data.addRows([
				['Ad Blockers Enabled (' + ABDStats.uniqueUsersAdBlocker + ')', ABDStats.uniqueUsersAdBlocker],
				['Ad Blockers Disabled (' + ABDStats.uniqueUsersNoAdBlocker + ')', ABDStats.uniqueUsersNoAdBlocker],
				['Other (' + ABDStats.uniqueUsersOther + ')', ABDStats.uniqueUsersOther]
			]);

			var options = {
				'title': 'Ad Blockers By Unique User',
				'colors': [badColor, goodColor, neutralColor],
				'backgroundColor': '#f1f1f1',
				'fontSize': 18,
				'fontName': 'sans-serif',
				'legend': {position: 'bottom', textStyle: {fontSize: 15}}
			};

			var chart = new google.visualization.PieChart(document.getElementById('abd-stats-chart-unique-user'));
			chart.draw(data, options);
		}

		function drawDisableChart(goodColor, badColor) {
			if( ABDStats.numUsersToDisable > 0 ) {
				var to = ABDStats.numUsersToDisable;
				var lo = ABDStats.uniqueUsersAdBlocker - ABDStats.numUsersToDisable;
			}
			else {
				var to = 0;
				var lo = 0;
			}

			var data = new google.visualization.DataTable();
			data.addColumn( 'string', 'Category' );
			data.addColumn( 'number', 'The Number' );
			data.addRows([
				['Turned Off Ad Blockers (' + to + ')', to],
				['Left On Ad Blockers (' + lo + ')', lo]
			]);

			var options = {
				'title': 'Users Who Toggled Off Ad Blocker',
				'colors': [goodColor, badColor],
				'backgroundColor': '#f1f1f1',
				'fontSize': 18,
				'fontName': 'sans-serif',
				'legend': {position: 'bottom', textStyle: {fontSize: 15}}
			};

			var chart = new google.visualization.PieChart(document.getElementById('abd-stats-chart-change-disable'));
			chart.draw(data, options);
		}

		function drawEnableChart(goodColor, badColor) {
			if( ABDStats.numUsersToEnable > 0 ) {
				var to = ABDStats.numUsersToEnable;
				var lo = ABDStats.uniqueUsersNoAdBlocker - ABDStats.numUsersToEnable;
			}
			else {
				var to = 0;
				var lo = 0;
			}

			var data = new google.visualization.DataTable();
			data.addColumn( 'string', 'Category' );
			data.addColumn( 'number', 'The Number' );
			data.addRows([
				['Turned On Ad Blockers (' + to + ')', to],
				['Left Off Ad Blockers (' + lo + ')', lo]
			]);

			var options = {
				'title': 'Users Who Toggled On Ad Blocker',
				'colors': [goodColor, badColor],
				'backgroundColor': '#f1f1f1',
				'fontSize': 18,
				'fontName': 'sans-serif',
				'legend': {position: 'bottom', textStyle: {fontSize: 15}}
			};

			var chart = new google.visualization.PieChart(document.getElementById('abd-stats-chart-change-enable'));
			chart.draw(data, options);
		}








		//////////////
		//	Toolbox	//
		//////////////

		//	http://stackoverflow.com/a/21903119
		function getUrlParameter(sParam)
		{
		    var sPageURL = window.location.search.substring(1);
		    var sURLVariables = sPageURL.split('&');
		    for (var i = 0; i < sURLVariables.length; i++) 
		    {
		        var sParameterName = sURLVariables[i].split('=');
		        if (sParameterName[0] == sParam) 
		        {
		            return sParameterName[1];
		        }
		    }
		}

		function sessionLogTime() {
			var today = new Date();

			var month = today.getUTCMonth() + 1;	//	January = 0
			var day   = today.getUTCDate();
			var year  = today.getUTCFullYear();
			var hour  = today.getUTCHours();
			var mins  = today.getUTCMinutes();
			var secs  = today.getUTCSeconds();

			//	Format numbers into two digits
			if( hour < 10 ) {
				hour = '0' + hour;
			}
			if( mins < 10 ) {
				mins = '0' + mins;
			}
			if( secs < 10 ) {
				secs = '0' + secs;
			}
			if( month < 10 ) {
				month = '0' + month;
			}
			if( day < 10 )  {
				day = '0' + day;
			}
			year = year.toString().slice(-2);	//	Two digit year

			
			//	Return string
			return month + '/' + day + '/' + year.toString().slice(-2) + ' @ ' + hour + ':' + mins + ':' + secs + ' (+00:00 GMT)';
		} 

	});	//	end $(document).ready(function() {
})(jQuery);