<?php
require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );
//////////////////////
// Class Definition
//////////////////////
if ( !class_exists( 'ABD_Ajax_Actions' ) ) {
	class ABD_Ajax_Actions {
		public function __construct() {
			//	http://codex.wordpress.org/AJAX_in_Plugins
			add_action( 'wp_ajax_abd_ajax', array( $this, 'navigate' ) );			
		}

		public static function navigate( ) {
			switch( $_POST['abd_action'] ) {
				case 'get_shortcode_by_id':
					$res = ABD_Db_Manip::get_shortcode_by_id( $_POST['id'] );

					//	$res is either an associative array or NULL. If null,
					//	we have some work to do.
					if ( is_null( $res ) ) {
						echo json_encode( array(
							'status' => false,
							'action' => "SQL query on database to retrieve a single row from shortcode table.",
							'reason' => "Query returns no result.",
							'data' => "ID# = " . $_POST['id']
						) );
						break;
					}

					//	Okay, then we have an associative array with expected values. Let's JSON encode it for AJAX.
					echo json_encode( $res );
					break;
				case 'get_all_shortcodes':
					$res = ABD_Db_Manip::get_all_shortcodes();

					//	$res is either an array or NULL. If null, we have some work to do.
					if ( is_null( $res ) ) {
						echo json_encode( array(
							'status' => false,
							'action' => "SQL query on database to retrieve all rows from shortcode table.",
							'reason' => "Query returns no result.",
							'data' => ""
						) );
						break;
					}

					echo json_encode( $res );
					break;
				case 'submit_edit_shortcode_by_id':
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

						die();
					}
					else {
						//	We don't want to submit the nonce to the database
						unset( $data['nonce'] );
					}

					$res = ABD_Db_Manip::update_shortcode_by_id( $_POST['id'], $data );

					//	$res is either an integer idicating how many rows were updated or FALSE. If FALSE,
					//	we have some work to do.
					if ( $res === false ) {	//	"=== false" (boolean false) is important... if data isn't changed and submit clicked, 0 is returned
											//	(http://codex.wordpress.org/Class_Reference/wpdb#UPDATE_rows)
						echo json_encode( array(
							'status' => false,
							'action' => "SQL query on database to update a single row in shortcode table.",
							'reason' => "Query failed.",
							'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
						) );
						break;
					}

					//	Okay, update was successful. Let's return a positive status message
					echo json_encode( array(
						'status' => true,
						'action' => "Update a single row in shortcode table.",
					) );
					break;
				case 'submit_new_shortcode':
					//	The data to insert is in the $_POST array.
					//	However, that array is full of a bunch of other junk as well
					//	Let's extract only what we need and put it in an array
					$data = self::extractData( 'ABD_new_input_form_' );

					//	Before we do anything, check the nonce!
					if ( wp_verify_nonce( $data['nonce'], 'ABD_new_input_form' ) === false ) {
						$gen = wp_create_nonce( 'ABD_edit_input_form' );
						echo json_encode( array( 
							'status' => false, 
							'action' => 'Verify nonce from new shortcode submission.', 
							'reason' => 'Nonce did not validate.',
							'data' => 'Submitted Nonce: ' . $data['nonce'] . ' :: Expected Nonce: ' . $gen
						) );

						die();
					}
					else {
						//	We don't want to submit the nonce to the database
						unset( $data['nonce'] );
					}

					$res = ABD_Db_Manip::insert_shortcode( $data );


					//	$res is either FALSE or an associative array.  If FALSE we have work to do.
					if ( !$res ) {
						echo json_encode( array(
							'status' => false,
							'action' => "SQL query on database to insert new row in shortcode table.",
							'reason' => "Query failed.",
							'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
						) );

						break;
					}

					else {
						//	Return a positive status message with the new id in the data column
						echo json_encode( array(
							'status' => true,
							'action' => 'SQL query on database to insert new row in shortcode table.',
							'data' => $res['id']
						) );
						break;	
					}

				case 'delete_shortcode_by_id':
					$res = ABD_Db_Manip::delete_shortcode_by_id( $_POST['id'] );

					//	$res is either an integer idicating how many rows were updated or FALSE. If FALSE,
					//	we have some work to do.
					if ( !$res ) {
						echo json_encode( array(
							'status' => false,
							'action' => "Attempt to delete a single row in shortcode table.",
							'reason' => "Query failed.",
							'data' => 'Error = "' . $wpdb->last_error . '" :: Query = "' . $wpdb->last_query . '"'
						) );
						break;
					}

					//	Okay, update was successful. Let's return a positive status message
					echo json_encode( array(
						'status' => true,
						'action' => "Delete a single row in shortcode table.",
					) );

					break;

				default:
					echo json_encode( array( 'status' => false, 'action' => 'default' ) );
					break;
			}

			die();
		}

		public static function extractData( $prefix = 'ABD_new_input_form_', $arr = null, $stripslashes = false ) {
			if ( $arr == null ) {
				if ( !empty($_POST['data'] ) ) {
					//	WordPress escapes $_POST data oddly... Unless we strip it, quotes will be escaped.
					//	http://stackoverflow.com/questions/7341942/wpdb-update-or-wpdb-insert-results-in-slashes-being-added-in-front-of-quotes
					$stripslashes = true;

					parse_str( $_POST['data'], $arr );
				}
				else {
					echo json_encode( array( 
						'status' => false, 
						'action' => 'Extracting form data from POST.', 
						'reason' => 'No form data available.', 
						'data' => $_POST)
					);
					die();
				}
			}


			//	If $arr isn't already an array, then it is a JSON string
			//	We need an array to continue processsing
			if ( !is_array( $arr ) ) {
				$arr = json_decode( $arr );
			}

			//	Okay, now that array should have 3 fields that we need: $prefix.name, $prefix.noadblock, and $prefix.adblock
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
			
			return $data;
		}
	}	//	end class ABD_Ajax_Actions

	//	instantiate object to run constructor
	new ABD_Ajax_Actions();
}	//	end if ( !class_exists(