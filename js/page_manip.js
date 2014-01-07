(function($) {
	$(document).ready(function() {
		//	Get the form to make life simple
		var newForm = $('#ABD_new_input_form_wrapper');
		var editForm = $('#ABD_edit_input_form_wrapper');
			

		//	Hide unimportant junk
		newForm.hide();
		editForm.hide();

		//////////////////////
		//	click listeners
		//////////////////////		
		//	New Shortcode
		$('a.ABD_new_button').click(function(event) {
			event.preventDefault();

			console.log("New Shortcode");

			
			//	Clear any entries in the form
			resetForm(newForm);

			//	Show the form if it is hidden
			newForm.show(1000)

			//	Scroll to shortcode form
			scrollTo(newForm);			
		});


		//	Submit Shortcode
		$('a.ABD_submit_button').click('clickSubmit');
	});



	////////////////////////
	//	Initialize
	////////////////////////
	//	Load existing shortcodes
	$.post(ajaxurl, {action: 'abd_ajax', abd_action: 'get_all_shortcodes'}, function(response) {
		var msgrow = $('#ABD_shortcode_list_message');

		var data = $.parseJSON(response);

		if (typeof(data) == 'object') {	//	then loop through array and output rows in table
			$.each(data, function(index, value) {
				addShortcodeListRow(value.id, value.name);
				msgrow.hide();
			});
		}
		else {	//	Something went wrong... no shortcodes or error
			msgrow.text("No shortcodes found!");
		}
	});




	/////////////////////////
	//	Click Functions
	/////////////////////////
	function clickNew(event) {
		
	}

	function clickEdit() {

	}

	function clickSubmit() {
		event.preventDefault();

		var form = $('#ABD_new_input_form');
		var nameField = $('#ABD_new_input_form_name');
		var noadblockField = $('#ABD_new_input_form_noadblock');
		var adblockField = $('#ABD_new_input_form_adblock');
		var idField = $('#ABD_new_input_form_id');
		
		var data = {
			name: nameField.val(),
			noadblock: noadblockField.val(),
			adblock: adblockField.val()
		};

		if (idField) {
			data.id = idField.val();
		}

		console.log(data);
	}

	function clickDelete() {

	}



	/////////////////////
	// Helper Functions
	/////////////////////
	function scrollTo(x) {
		$('html, body').animate({
			scrollTop: x.offset().top - 75
		}, 1000);
	}

	function resetForm(form) {
		form.find('input:text, input:password, input:file, select, textarea').val('');
	    form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
	}

	function addShortcodeListRow(id, name) {
		var table = $('table.ABD_shortcode_list');

		var row = $("<tr class='ABD_shortcode'><td>" + name + "</td><td>[adblock-detector id=\"" + id + "\"]</td><td><a id='ABD_edit_button_" + id + "' class='ABD_button ABD_edit_button' data-id='" + id + "'>Edit</a> &nbsp; | &nbsp; <a id='ABD_delete_button_" + id + "' class='ABD_button ABD_delete_button' data-id='" + id + "'>Delete</a></td></tr>");

		table.append(row);
		row.hide().show(1000);


		// Click listeners on new buttons
		//	Edit Existing Shortcode
		$('#ABD_edit_button_' + id).click({id: id}, function(event) {
			event.preventDefault();

			var id = event.data.id;

			$.post(ajaxurl, {action: 'abd_ajax', abd_action: 'get_shortcode_by_id', id: id}, function(response) {
				var data = $.parseJSON(response);

				var idField = $('#ABD_edit_input_form_id');
				var idFieldMsg = $('#ABD_edit_input_form_id_feedback');
				var nameField = $('#ABD_edit_input_form_name');
				var nameMsg = $('#ABD_edit_input_form_name_feedback');
				var noadblockField = $('#ABD_edit_input_form_noadblock');
				var noadblockMsg = $('#ABD_edit_input_form_noadblock_feedback');
				var adblockField = $('#ABD_edit_input_form_adblock');
				var adblockMsg = $('#ABD_edit_input_form_adblock_feedback');
				var submitButton = $('#ABD_edit_input_form_submit');

				var form = $('#ABD_edit_input_form');				


				if (typeof(data) == 'object') {	//	then fill table
					idField.val(id);

					if ($.trim(data.name) != '') {
						nameField.val(data.name);
					}
					else {
						nameMsg.text('Your shortcode did not have a value for this field.');
					}

					if ($.trim(data.noadblock) != '') {
						noadblockField.val(data.noadblock);
					}
					else {
						noadblockMsg.text('Your shortcode did not have a value for this field.');
					}

					if ($.trim(data.adblock) != '') {
						adblockField.val(data.adblock);
					}
					else {
						adblockMsg.text('Your shortcode did not have a value for this field.');
					}


					//	Add the id to the form so it is accessible upon form submission
					form.append("<input id='ABD_new_input_form_id' type='hidden' value='" + data.id + "' />");

					//	Reregister click listener to capture new data
					submitButton.click('clickSubmit');
				}
				else {	//	Something went wrong...
					$('#ABD_notification').text('Error loading your shortcode for editing!');
				}
			});


			//	Show the form if it is hidden
			$('#ABD_edit_input_form_wrapper').show(1000);

			//	Scroll to shortcode form
			scrollTo($('#ABD_edit_input_form_wrapper'));
		});



		//	Delete Existing Shortcode
		$('#ABD_delete_button_' + id).click(function(event) {
			event.preventDefault();
			
			console.log("Delete Shortcode");
		});
	}
}(jQuery));