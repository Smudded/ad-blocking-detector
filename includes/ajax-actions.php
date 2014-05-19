<?php
/**
 * This file contains the handlers for all AJAX requests. 
 * IT MUST ONLY OUTPUT JSON STRINGS FOR RECOGNITION DURING AJAX CALLS
 */

require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );

if ( !class_exists( 'ABD_Ajax_Actions' ) ) {
	class ABD_Ajax_Actions {
		/**
		 * Determines which AJAX handler function need to be called based on
		 * the abd_action POST parameter passed in the AJAX call.
		 */
		public static function navigate() {
			switch( $_POST['abd_action'] ) {
				case 'get_shortcode_by_id':
					self::get_shortcode_by_id( $_POST['id'] );
					break;
				case 'get_all_shortcodes':
					self::get_all_shortcodes();
					break;
				case 'submit_edit_shortcode_by_id':
					self::submit_edit_shortcode_by_id( $_POST['id'] );
					break;
				case 'submit_new_shortcode':
					self::submit_new_shortcode();
					break;
				case 'delete_shortcode_by_id':
					self::delete_shortcode_by_id( $_POST['id'] );
					break;
				default:
					echo json_encode( 
						array( 
							'status' => false, 
							'action' => 'default'
						) 
					);
			}	//	end switch

			exit;
		}	//	end function navigate( ...


		/////////////////////////
		/// Handler Functions ///
		/////////////////////////

		//	All handler functions are middle-men between the AJAX submitted data
		//	in $_POST and the appropriate database function. For more 
		//	information on each function, see the ABD_Database class defined
		//	in includes/db-manip.php.  Particularly, pay attention to the class
		//  method called in each function below.
		//  
		//  All functions either output the results of the corresponding
		//  database manipulation function as a JSON encoded string, an
		//  error array as a JSON encoded string, or a success array as a JSON
		//  encoded string.
		//  
		//  Error/Success arrays have the following format:
		//  	array(
		//  		'status' => Boolean true for success or false for failure,
		//  		'action' => Developer meaningful explanation of what process 
		//  					was tried or accomplished,
		//  		'reason' => (optional) Developer meaningful reason 
		//  					for any failures.
		//  		'data' => (optional) String representation of any relevant
		//  					data.
		//  		
		//  	)
		
		protected static function get_shortcode_by_id( $id ) {
			$res = ABD_Database::get_shortcode_by_id( $id );

			if ( is_null( $res ) ) {	// error
				echo json_encode( array(
					'status' => false,
					'action' => "SQL query on database to retrieve a single row from shortcode table.",
					'reason' => "Query returns no result.",
					'data' => "ID# = " . $_POST['id']
				) );

				return;
			}

			//	success
			echo json_encode( $res );
			return;
		}
		
		protected static function get_all_shortcodes() {
			$res = ABD_Database::get_all_shortcodes( true, true );

			if ( is_null( $res ) ) {	//	error
				echo json_encode( array(
					'status' => false,
					'action' => "SQL query on database to retrieve all rows from shortcode table.",
					'reason' => "Query returns no result.",
					'data' => ""
				) );
				return;
			}

			//	success
			echo json_encode( $res );
			return;
		}
		
		protected static function submit_edit_shortcode_by_id( $id ) {
			global $wpdb;

			//	The data to insert is in the $_POST array.
			//	However, that array is full of a bunch of other junk as well
			//	Let's extract only what we need and put it in an array
			$data = self::extractData( 'ABD_edit_input_form_' );

			//	Before we do anything, check the nonce!
			if ( wp_verify_nonce( $data['nonce'], 'ABD_edit_input_form' ) === false ) {
				$gen = wp_create_nonce( 'ABD_edit_input_form' );
				echo json_encode( array( 
					'status' => false, 
					'action' => 'Verify nonce from update shortcode submission.', 
					'reason' => 'Nonce did not validate.',
					'data' => 'Submitted Nonce: ' . $data['nonce'] . ' :: Expected Nonce: ' . $gen
				) );

				return;
			}
			else {
				//	We don't want to submit the nonce to the database
				unset( $data['nonce'] );
			}


			//	Okay, now let's do the database manip
			$res = ABD_Database::update_shortcode_by_id( $_POST['id'], $data );

			//	$res is either an integer idicating how many rows were updated 
			//	or FALSE. 
			if ( $res === false ) {	//	"=== false" (boolean false) is important... 
									//  if data isn't changed and submit clicked, 
									//  0 is returned, and 0 == false, but doesn't === false
									//	(http://codex.wordpress.org/Class_Reference/wpdb#UPDATE_rows)
				echo json_encode( array(
					'status' => false,
					'action' => "SQL query on database to update a single row in shortcode table.",
					'reason' => "Query failed.",
					'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
				) );
				return;
			}

			//	Okay, update was successful. Let's return a positive status message
			echo json_encode( array(
				'status' => true,
				'action' => "Update a single row in shortcode table.",
			) );
			return;
		}

		protected static function submit_new_shortcode() {
			global $wpdb;

			//	The data to insert is in the $_POST array.
			//	However, that array is full of a bunch of other junk as well
			//	Let's extract only what we need and put it in an array
			$data = self::extractData( 'ABD_new_input_form_' );

			//	Before we do anything, check the nonce!
			if ( wp_verify_nonce( $data['nonce'], 'ABD_new_input_form' ) === false ) {
				$gen = wp_create_nonce( 'ABD_new_input_form' );
				echo json_encode( array( 
					'status' => false, 
					'action' => 'Verify nonce from new shortcode submission.', 
					'reason' => 'Nonce did not validate.',
					'data' => 'Submitted Nonce: ' . $data['nonce'] . ' :: Expected Nonce: ' . $gen
				) );

				return;
			}
			else {
				//	We don't want to submit the nonce to the database
				unset( $data['nonce'] );
			}

			
			//	Okay, now let's do the database manip
			$res = ABD_Database::insert_shortcode( $data, true );

			//	$res is either FALSE or an associative array. (see ABD_Database::insert_shortcode())
			if ( !$res ) {	//	error
				echo json_encode( array(
					'status' => false,
					'action' => "SQL query on database to insert new row in shortcode table.",
					'reason' => "Query failed.",
					'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
				) );

				return;
			}

			else {	//	success
				//	Return a positive status message with the new id in the 
				//	data element
				echo json_encode( array(
					'status' => true,
					'action' => 'SQL query on database to insert new row in shortcode table.',
					'data' => $res['id']
				) );

				return;
			}
		}

		protected static function delete_shortcode_by_id( $id ) {
			global $wpdb;

			$res = ABD_Database::delete_shortcode_by_id( $_POST['id'] );

			//	$res is either an integer indicating how many rows were updated 
			//	or FALSE.
			if ( !$res ) {	//	error
				echo json_encode( array(
					'status' => false,
					'action' => "Attempt to delete a single row in shortcode table.",
					'reason' => "Query failed.",
					'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
				) );
				return;
			}

			//	success
			echo json_encode( array(
				'status' => true,
				'action' => "Delete a single row in shortcode table.",
			) );

			return;
		}












		////////////////////////
		/// Helper Functions ///
		////////////////////////
		
		/**
		 * This function pulls out, cleans, and prepares data for use in 
		 * database manipulation. Pulls from $_POST if the $arr parameter is
		 * omitted or null.
		 * @param  string  $prefix       Prefix on form field names. (e.g. If 
		 * field name is "ABD_new_input_form_field1", "ABD_new_input_form_" is 
		 * the prefix.)
		 * @param  ARRAY_A/string  $arr         The array, or a JSON encoded 
		 * string of the array, to extract data from. If omitted or null, 
		 * $_POST['data'] is used.
		 * @param  boolean $stripslashes Whether the $arr needs to have 
		 * stripslashes function run on every field. 99.999999% of the time, the
		 * answer is no (false). Occasionally WordPress does some funky escaping
		 * that needs undoing when extracting data (http://goo.gl/PCba3G). This 
		 * means nothing if $arr parameter is omitted.
		 * @return ARRAY_A                The data formatted as an associative
		 * array labeled and prepped for insertion in database manipulation 
		 * functions
		 */
		public static function extractData( 
			$prefix = 'ABD_new_input_form_', 
			$arr = null,
			$stripslashes = false ) {

			if ( $arr == null ) {
				//	We use $_POST instead... if it's not empty.
				if ( !empty($_POST['data'] ) ) {
					//	WordPress escapes $_POST data oddly... 
					//	Unless we strip it, quotes will be escaped.
					//	http://goo.gl/PCba3G
					$stripslashes = true;

					//	Turn the $_POST data into an array and store in $arr for
					//	processing further down.
					parse_str( $_POST['data'], $arr );
				}
				else {	//	error, no data to extract!
					echo json_encode( array( 
						'status' => false, 
						'action' => 'Extracting form data from POST.', 
						'reason' => 'No form data available.', 
						'data' => $_POST)
					);
					return;
				}
			}


			//	Okay, come hell or high water, we should have some data in $arr
			//	now.  
			//	
			//	If $arr isn't already an array, then it is a JSON string
			//	We need an array to continue processsing
			if ( !is_array( $arr ) ) {
				$arr = json_decode( $arr );
			}

			//	Okay, now that array should have 4 fields that we need: 
			//	$prefix.name, $prefix.noadblock, and $prefix.adblock and _wpnonce
			$data = array(
				'name' => $arr[$prefix . 'name'],
				'noadblock' => $arr[$prefix . 'noadblock'],
				'adblock' => $arr[$prefix . 'adblock'],
				'nonce' => $arr['_wpnonce']
			);

			//	If we are supposed to strip out slashes, do so
			if ( $stripslashes ) {
				foreach ( $data as $key => $value ) {
					$data[$key] = stripslashes( $value );
				}
			}
			
			//	Return the data array
			return $data;
		}
	}	//	end class
}	//	end if ( !class_exists( ...