(function($) {
	var settings = {
		logLevel: 2,	//	0 = no logging, 1 = log important only, 2 = log everything
	}

	$(document).ready(function() {
		/**
		 * Create some variables pointing to the jQuery selectors
		 * that are accessible in every scope beneath this point.
		 */
		//	Get all the form fields
		var newForm = $('#ABD_new_input_form');
		var newFormWrapper = $('#ABD_new_input_form_wrapper');
		var newNameField = $('#ABD_new_input_form_name');
		var newAdblockField = $('#ABD_new_input_form_adblock');
		var newNoAdblockField = $('#ABD_new_input_form_noadblock');
		var editForm = $('#ABD_edit_input_form');
		var editFormWrapper = $('#ABD_edit_input_form_wrapper');
		var editNameField = $('#ABD_edit_input_form_name');
		var editAdblockField = $('#ABD_edit_input_form_adblock');
		var editNoAdblockField = $('#ABD_edit_input_form_noadblock');
		var editIdField = $('#ABD_edit_input_form_id');

		//	Get Feedback Fields
		var globalFeedback = $('#ABD_notification');
		var newNameFeedback = $('#ABD_new_input_form_name_feedback');
		var newAdblockFeedback = $('#ABD_new_input_form_adblock_feedback');
		var newNoAdblockFeedback = $('#ABD_new_input_form_noadblock_feedback');
		var editNameFeedback = $('#ABD_edit_input_form_name_feedback');
		var editAdblockFeedback = $('#ABD_edit_input_form_adblock_feedback');
		var editNoAdblockFeedback = $('#ABD_edit_input_form_noadblock_feedback');
		

		//	Get Buttons
		var newSubmitButton = $('#ABD_new_input_form_submit');
		var editSubmitButton = $('#ABD_edit_input_form_submit');
		var createNewButton = $('.ABD_new_button');

		//	Get Display Table
		var shortcodeTable = $('#ABD_shortcode_list');
		var shortcodeTableMsg = $('#ABD_shortcode_list_message');

		//	Get anything else
		var tips = $('.ABD_tip');			
		

		/**
		 * Click Listener Actions
		 */
		function clickCreateNew (event) {
			log("Create new shortcode button click event caught. Event handler function fired.");

			event.preventDefault();

			//	Clear any old form values from new shortcode form
			resetForm(newForm);

			//	If the form is hidden, show it
			newFormWrapper.show(1000);

			//	Show tips too
			tips.show(1000);

			//	Scroll the page to the new form
			scrollTo(newFormWrapper);
		}

		function clickEditButton (event) {
			event.preventDefault();

			var passedData = event.data;
			var retrievedData;

			log("Edit existing shortcode button clicked (ID# = " + passedData.id + "). Event handler function fired.");

			displayNotification('notice', 'Loading...');
			$.post(ajaxurl, {action: 'abd_ajax', abd_action: 'get_shortcode_by_id', id: passedData.id}, function(response) {
				retrievedData = $.parseJSON(response);

				log("Edit button AJAX request completed.  Result: " + var_dump(retrievedData), true);	//	true = important


				//	Fill in the fields in the edit form with the retrieved data
				editNameField.val(retrievedData.name);
				editAdblockField.val(retrievedData.adblock);
				editNoAdblockField.val(retrievedData.noadblock);
				editIdField.val(passedData.id);

				//	Clear any old feedback messages
				clearFeedbackMessages();

				//	If any of the fields are empty, display a notice so that we don't look like we didn't do anything.
				if ($.trim(retrievedData.name) == '') {
					editNameFeedback.text('No stored value for this field!');
				}

				if ($.trim(retrievedData.adblock) == '') {
					editAdblockFeedback.text('No stored value for this field!');
				}

				if ($.trim(retrievedData.noadblock) == '') {
					editNoAdblockFeedback.text('No stored value for this field!');
				}

				hideNotification(function() {
					scrollTo(editFormWrapper);
				});

				editFormWrapper.show(1000);

				//	Show tips too
				tips.show(1000);

				//	If a "Add Shortcode" section is open, clear the form and close it.
				resetForm(newForm);
				newFormWrapper.hide();
			});
		}

		function clickDeleteButton (event) {
			event.preventDefault();

			var passedData = event.data;

			log("Delete existing shortcode button clicked (ID# = " + passedData.id + "). Event handler function fired.");

			displayNotification('notice', 'Deleting shortcode...');
			$.post(ajaxurl, {action: 'abd_ajax', abd_action: 'delete_shortcode_by_id', id: passedData.id}, function(response) {
				retrievedData = $.parseJSON(response);

				log("Delete button AJAX request completed.  Result: " + var_dump(retrievedData), true);	//	true = important


				if (retrievedData.status == false) {
					displayNotification( 
						'error', 
						'Uh oh. Something went wrong. Try deleting the item again or refreshing the page.',
						'<strong>Failed Action:</strong> ' + retrievedData.action + '<br /><strong>Failure Reason:</strong> ' + retrievedData.reason + '<br /><strong>Contextual Data:</strong> ' + retrievedData.data
					);
					scrollTo(globalFeedback);
				}
				else {
					displayNotification('success', 'Shortcode deleted!');
					setTimeout(function() {
						hideNotification();
					}, 3000);

					//	Remove applicable row from table
					deleteRow(passedData.id);
				}
			});
		}

		function clickSubmitButton (event) {
			event.preventDefault();

			var passedData = event.data;

			//	This function handles the submit button for both the new and edit forms
			//	To allow this abstraction, we must decide which form to pull the field
			//	data from.  Fortunately, this information should have been passed by the
			//	click handler.
			if (passedData.form == 'new') {
				log("Submit button clicked on new shortcode form.  Event handler fired.");

				var theForm = newForm;
				var theFormName = newNameField;
				var theFormNoAdblock = newNoAdblockField;
				var theFormAdblock = newAdblockField;
				var theFormNameFeedback = newNameFeedback;
				var theFormNoAdblockFeedback = newNoAdblockFeedback;
				var theFormAdblockFeedback = newAdblockFeedback;

				var id = null;

				var abd_action = 'submit_new_shortcode';
			}
			else if (passedData.form == 'edit') {
				log("Submit button clicked on edit shortcode form.  Event handler fired.");

				var theForm = editForm;
				var theFormName = editNameField;
				var theFormNoAdblock = editNoAdblockField;
				var theFormAdblock = editAdblockField;
				var theFormNameFeedback = editNameFeedback;
				var theFormNoAdblockFeedback = editNoAdblockFeedback;
				var theFormAdblockFeedback = editAdblockFeedback;
				
				var id = editIdField.val();

				var abd_action = 'submit_edit_shortcode_by_id';
			}
			else {
				log("Submit button click handler called without necessary data. Please provide an event data object with a 'form' value of 'new' or 'edit'.", true);
				return;
			}


			//	Now let's do some basic validation.
			//	First, let's make sure we remove any old validation messages.			
			clearFeedbackMessages();

			//	Now run the validation
			//	The name field is required, make sure it isn't empty.
			var e = false;	//	a flag to indicate whether something didn't validate
			if ($.trim(theFormName.val()).length < 1) {
				theFormNameFeedback.text('You must provide a name!');
				theFormName.addClass('ABD_input_form_highlight');
				e = true;
			}
			//	Now let's put some max length limits on the name.
			if (theFormName.val().length > 40) {
				theFormNameFeedback.text('This name is too long! It must be 40 characters or less.');				
				theFormName.addClass('ABD_input_form_highlight');
				e = true;
			}

			//	Now, if something did not validate, log it, throw a notification, and quit.
			if (e) {
				log("Errors detected in submission.");
				displayNotification(
					'error',
					'Uh oh! I found some problems with your submission. Please resolve the errors and try again.'
				);
				return;
			}

			//	If we're here, then the fields are okay to submit.
			//	Okay, we have our form fields and context, now encode the form values for sending to AJAX handler
			encodedData = theForm.serialize();


			//	Notify everyone
			log("Submitting the following data via AJAX: " + encodedData);
			displayNotification('notice', 'Saving shortcode...', function() {
				scrollTo('top');
			});

			//	Submit that bad boy!
			$.post(ajaxurl, {action: 'abd_ajax', abd_action: abd_action, id: id, data: encodedData}, function(response) {
				retrievedData = $.parseJSON(response);

				log("Submit button AJAX request completed.  Result: " + var_dump(retrievedData), true);

				if ( retrievedData.status === false ) {
					//	The operation failed.  Throw up a notification.
					displayNotification( 
						'error', 
						'Uh oh. Something went wrong. Try submitting again or refreshing the page.',
						'<strong>Failed Action:</strong> ' + retrievedData.action + '<br /><strong>Failure Reason:</strong> ' + retrievedData.reason + '<br /><strong>Contextual Data:</strong> ' + retrievedData.data
					);
					scrollTo(globalFeedback);
				}
				else {
					//	Refresh the table
					populateTable();

					//	Clear and hide forms
					resetForm(newForm);
					resetForm(editForm);
					newFormWrapper.hide(1000);
					editFormWrapper.hide(1000);

					//	Hide tips too
					tips.hide(1000);

					//	Scroll back to the table
					scrollTo('top');
					displayNotification('success', 'Shortcode saved successfully!', function() {
						setTimeout(function() {
							hideNotification();
						}, 5000);				
					});
				}		
			});			
		}



		/**
		 * Helper Functions
		 */
		function log(msg, important) {
			//	Is this important?  Are we logging everything?
			if (important !== true && settings.logLevel < 2) {
				return;
			}
			else if (settings.logLevel == 0) {
				return;
			}

			//	Okay, since we are here, we do want to log this message
			//	Let's format it a little, then write it to the console.
			console.log("Ad Block Detector Log Message:   " + msg);
		}

		function scrollTo(x) {
			if (x == 'top') {
				$('html, body').animate({
					scrollTop: 0
				}, 1000);

				return;
			}

			$('html, body').animate({
				scrollTop: x.offset().top - 75
			}, 1000);
		}

		function resetForm(form) {
			form.find('input:text, input:password, input:file, select, textarea').val('');
		    form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
		}

		function deleteRow(id) {
			var cSel = 'tr[data-id="' + id + '"]';
			var row = shortcodeTable.find(cSel);

			if (row) {
				row.hide(1000, function() {
					$(this).remove();
				});
			}
		}

		function populateTable(initial) {
			log("Request to populate the shortcode table with all existing shortcodes.");

			if (!initial) {
				clearTable();			
				shortcodeTableMsg.text('Reloading shortcodes... please wait...');
			}

			$.post(ajaxurl, {action: 'abd_ajax', abd_action: 'get_all_shortcodes'}, function(response) {
				var data = $.parseJSON(response);

				log("Retrieve all shortcodes AJAX completed.  Result: " + var_dump(data), true);

				if (typeof(data) == 'object') {	//	then loop through array and output rows in table
					$.each(data, function(index, value) {
						shortcodeTableMsg.hide();
						addShortcodeListRow(value.id, value.name);						
					});
				}
				else {	//	Something went wrong... no shortcodes or error
					shortcodeTableMsg.text("No shortcodes found!");
				}
			});
		}

		function clearTable() {
			shortcodeTable.find('tr.ABD_shortcode').each(function() {
				$(this).hide(1000, function() {
					$(this).remove();
				});
			});
		}

		function addShortcodeListRow(id, name) {
			var row = $("<tr class='ABD_shortcode' data-id='" + id + "'><td>" + name + "</td><td>[adblockdetector id=\"" + id + "\"]</td><td><a id='ABD_edit_button_" + id + "' class='ABD_button ABD_edit_button' data-id='" + id + "'>Edit</a> &nbsp; | &nbsp; <a id='ABD_delete_button_" + id + "' class='ABD_button ABD_delete_button' data-id='" + id + "'>Delete</a></td></tr>");
			
			shortcodeTable.append(row);

			$('#ABD_edit_button_' + id).click({id: id}, clickEditButton);
			$('#ABD_delete_button_' + id).click({id: id}, clickDeleteButton);

			row.hide().show(1000);		
		}

		function setupInitialClickListeners() {
			newSubmitButton.click({form: 'new'}, clickSubmitButton);
			editSubmitButton.click({form: 'edit'}, clickSubmitButton);

			createNewButton.click(clickCreateNew);
		}

		function var_dump(varToDump) {
			return JSON.stringify(varToDump);
		}

		function displayNotification(type, msg, data, runAfterShow) {
			globalFeedback.removeClass('ABD_notification_error ABD_notification_warning ABD_notification_success ABD_notification_notice');				

			if (type == 'error') {
				globalFeedback.addClass('ABD_notification_error');
			}
			else if (type =='warning') {
				globalFeedback.addClass('ABD_notification_warning');
			}
			else if (type == 'success') {
				globalFeedback.addClass('ABD_notification_success');
			}
			else {
				globalFeedback.addClass('ABD_notification_notice');
			}

			//	Create HTML to input into display
			var html = '<span class="ABD_notification_message">' + msg + '</span>';
			if ( typeof(data) == 'string' ) {
				html += '<span class="ABD_notification_supplemental">' + data + '</span>';
			}

			globalFeedback.html(html);
			globalFeedback.show(1000, function() {
				if (typeof(runAfterShow) == 'function') {
					runAfterShow();
				}
			});
		}
		function hideNotification(runAfterHide) {
			globalFeedback.hide(1000, function() {
				globalFeedback.removeClass('ABD_notification_error ABD_notification_warning ABD_notification_success ABD_notification_notice');
				globalFeedback.html('');

				if (typeof(runAfterHide) == 'function') {
					runAfterHide();
				}
			});
		}

		function clearFeedbackMessages() {
			newNameField.removeClass('ABD_input_form_highlight');
			newAdblockField.removeClass('ABD_input_form_highlight');
			newNoAdblockField.removeClass('ABD_input_form_highlight');
			editNameField.removeClass('ABD_input_form_highlight');
			editAdblockField.removeClass('ABD_input_form_highlight');
			editNoAdblockField.removeClass('ABD_input_form_highlight');

			newNameFeedback.text('');
			newAdblockFeedback.text('');
			newNoAdblockFeedback.text('');
			editNameFeedback.text('');
			editAdblockFeedback.text('');
			editNoAdblockFeedback.text('');
		}



		//	Initialize everything...
		populateTable(true);
		setupInitialClickListeners();

		newFormWrapper.hide();
		editFormWrapper.hide();	
		globalFeedback.hide();
		tips.hide();
	});
}(jQuery))