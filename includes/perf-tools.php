<?php
/**
 * This file contains the class definition for ABD_Perf_Tools, which is a collection of 
 * random tools used to improve plugin performance.
 */

if( !class_exists( 'ABD_Perf_Tools' ) ) {
	class ABD_Perf_Tools {
		/**
		 * Settings API options only need registration on Ad Blocking Detector pages, and the
		 * options.php submission page.  Everywhere else, it's just superfluous overhead that
		 * can completely break WordPress sites if something is wrong with them.  This function
		 * checks whether the options need registering or not.
		 *
		 * @return   bool   Whether we need to register options or not.
		 */
		public static function need_to_load_wpsm_settings() {
			//	Wrap in try/catch so we don't break all of WordPress if something goes wrong.
			try {
				$current_page = basename( $_SERVER['PHP_SELF'] );

				//	admin.php?page='ad-blocking-detector'
				if( $current_page == 'admin.php' && $_GET['page'] == 'ad-blocking-detector' ) {
					return true;
				}

				//	options.php when submitting ad-blocking-detector
				if( $current_page == 'options.php' ) {
					$ref = $_POST['_wp_http_referer'];
					//	If ABD, this should contain /wordpress-single/wp-admin/admin.php?page=ad-blocking-detector&tab=...
					$ref_contains_abd = strpos( $ref, 'ad-blocking-detector' ) === false ? false : true;

					if( $ref_contains_abd ) {
						return true;
					}
				}
			} 
			catch( Exception $e ) {
				ABD_Log::error( 'Error in ABD_Perf_Tools::need_to_load_wpsm_settings(): ' . $e->getMessage() );
			}

			return false;
		}
	}	//	end class
}	//	end if( !class_exists( ...