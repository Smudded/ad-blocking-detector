<?php
/**
 * This file contains any and all output for the public facing sections of the
 * site.
 */
require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );

if ( !class_exists( 'ABD_Public_Views' ) ) {
	class ABD_Public_Views {
		/**
		 * Returns the public facing result of a shortcode with the given ID#.
		 * @param  [int] $id The ID# of the shortcode in the database.
		 * @return [string] A string containing what the shortcode should output.
		 */
		public static function get_shortcode_output( $id ) {
			//	Get the database entry for that shortcode
			$res = ABD_Database::get_shortcode_by_id( $id );

			//	Was it successful?
			if ( $res ) {
				//	Good!
				//	Process any shortcodes used within the ABD shortcode
				$noab = do_shortcode( $res['noadblock'] );
				$ab = do_shortcode( $res['adblock'] );

				//	And now create the return value
				$retval = '<div class="ABD_display_wrapper ABD_shortcode_' . $id . '">';
					$retval .= '<div class="ABD_display ABD_display_noadblock">' . $noab . '</div>';
					$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';
				$retval .= '</div>';
			}
			else {
				// Uh-Oh. This means the query failed or, more likely,
				// their is no shortcode with that ID in the database.
				// Let's return a generic error message.
				$retval = '<div class="ABD_display_wrapper ABD_shortcode_' . $id . '">';
					$retval = '<div class="ABD_error"><b>Ad Blocking Detector Error</b><br /><em>No shortcode with that ID# ( ' . $id . ').</em></div>';
				$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';
			}

			return $retval;
		}
	}	//	end class
}	//	end if( !class_exists( ...
