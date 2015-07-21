<?php
/**
 * This file contains any and all output for the public facing sections of the
 * site.
 */

if ( !class_exists( 'ABD_Public_Views' ) ) {
	class ABD_Public_Views {
		/**
		 * Returns the public facing result of a shortcode with the given ID#.
		 * @param  [int] $id The ID# of the shortcode in the database.
		 * @return [string] A string containing what the shortcode should output.
		 */
		public static function get_shortcode_output( $id = null, $nab = '', $ab = '' ) {
			//	Get the database entry for that shortcode if we're given and ID #
			if( !is_null( $id ) ) {
				$res = ABD_Database::get_shortcode( $id );
			}
			else {
				$res = array();
			}


			//	Are we given override data in $nab or $ab?
			if( !empty( $nab ) || !empty( $ab ) ) {
				//	We need to override some content, or make a pseudo database result
				if( empty( $res ) ) {	//	No prescribed blog_id from database, so use one for all sites
					$res['blog_id'] = -1;
				}

				if( !empty( $nab ) ) {
					$res['noadblocker'] = $nab;
				}

				if( !empty( $ab ) ) {
					$res['adblocker']   = $ab;
				}
			}

			//	If we don't have anything, make it false
			if( empty( $res ) ) {
				$res = false;
			}



			//	Do we have anything to display, and if so, is this shortcode for this site (same blog id) or all sites (-1)?
			if ( $res && ( $res['blog_id'] == ABD_Multisite::get_current_blog_id() || $res['blog_id'] == -1 ) ) {
				//	Good!
				//	Process any shortcodes used within the ABD shortcode
				$noab = do_shortcode( $res['noadblocker'] );
				$ab = do_shortcode( $res['adblocker'] );

				//	And now create the return value
				$retval = '<div class="ABD_display_wrapper ABD_shortcode_' . $id . '">';
					$retval .= '<div class="ABD_display ABD_display_noadblock">' . $noab . '</div>';
					$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';

					//	If we have custom user-defined wrappers for this shortcode, append them.
					if( !empty( $res['user_defined_selectors'] ) ) {
						//	Turn string into array
						$uds = array_map( 'trim', explode( ';', $res['user_defined_selectors'] ) );
						$uds_string = json_encode( $uds );

						//	Output JavaScript to add the selectors to the ABDSettings variable
						ob_start();
						?>

						<script type="text/javascript">
							//	Build in delay until jQuery is loaded if user is using annoying
							//	asynchrounous JS plugins... seriously, those are annoying as hell.
							//	
							//	Make sure we don't double declare our function
							if( typeof abdJqueryDefer != 'function' ) {
								var abdJqueryDefer = function( funcToRun ) {
									if( window.jQuery ) {
										funcToRun();
									}
									else {
										setTimeout(function() {
											abdJqueryDefer(funcToRun);
										}, 500);
									}
								}
							}

							abdJqueryDefer(function() {
								jQuery(document).ready(function() {								
									var newselectors = jQuery.parseJSON('<?php echo $uds_string; ?>');

									ABDSettings.cssSelectors = ABDSettings.cssSelectors.concat(newselectors);
								});
							});
						</script>

						<?php
						//	Get that JavaScript and add it to the shortcode
						$retval .= ob_get_clean();
					}
				$retval .= '</div>';
			}
			else {
				// Uh-Oh. This means the query failed, the shortcode is for a different multisite site,
				// or, more likely, their is no shortcode with that ID in the database.
				// Let's return a generic error message.
				$retval = '<div class="ABD_display_wrapper ABD_shortcode_' . $id . '">';
					$retval = '<div class="ABD_error"><b>Ad Blocking Detector Error</b><br /><em>No shortcode with that ID# ( ' . $id . ').</em></div>';
				$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';
			}

			return $retval;
		}
	}	//	end class
}	//	end if( !class_exists( ...
