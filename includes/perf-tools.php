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

		public static function force_garbage_collection() {
			if( function_exists( 'gc_collect_cycles' ) && function_exists( 'gc_enable' ) ) {    //  PHP >= 5.3 only
                //  Force garbage collection now
                gc_enable();
                gc_collect_cycles();
            }
		}

		public static function get_readable_server_config_data( $line_endings = '&#13;&#10;' ) {
			//	Gather Data
			$blcp_status = array(
				'plugin_activated'        => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_activated' ),
				'plugin_exists'           => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_exists' )
			);

			$blcdir = ABD_Anti_Adblock::get_bcc_plugin_dir_name();
			if( !$blcdir ) { $blcdir = 'No BLC Plugin Directory'; }

			$mem_usage = memory_get_peak_usage( true );
			if( $mem_usage < 1024 ) { $mem_usage = $mem_usage . ' bytes'; }
			else if( $mem_usage < 1048576 ) { $mem_usage = round( $mem_usage/1024, 2 ) . ' KB'; }
			else { $mem_usage = round( $mem_usage/1048576, 2 ) . ' MB'; }

			$aa2d_mem = memory_get_usage();
			$aa2d = array( 
				'perftestentry1' => array( 'abcdefg', 'hijklmn' ), 
				'perftestentry2' => array( 'opqrstu', 'vwxyz12' ),
				'perftestentry3' => array( '3456789', '0abcdef' ),
				'perftestentry4' => array( 'ghijklm', 'nopqrst' ), 
				'perftestentry5' => array( 'uvwxyz1', '2345678' ),
				'perftestentry6' => array( '90abcde', 'fghijkl' ),
				'perftestentry7' => array( 'mnopqrs', 'tuvwxyz' ), 
				'perftestentry8' => array( '1234567', '890abcd' ),
				'perftestentry9' => array( 'efghijk', 'lmnopqr' )
			);
			$aa2d_mem = ABD_Log::mem_diff( $aa2d_mem );
			unset( $aa2d );

			$aa1d_mem = memory_get_usage();
			$aa1d = array( 
				'perftestentry1' => 'abcdefg', 
				'perftestentry2' => 'opqrstu',
				'perftestentry3' => '3456789',
				'perftestentry4' => '1234567', 
				'perftestentry5' => 'hijklmn',
				'perftestentry6' => 'vwxyz12',
				'perftestentry7' => '4567890', 
				'perftestentry8' => 'bcdefgh',
				'perftestentry9' => 'ijklmno'
			);
			$aa1d_mem = ABD_Log::mem_diff( $aa1d_mem );
			unset( $aa1d );


			ob_start();
			
			?>
ENVIRONMENT DATA<?php echo $line_endings; ?>==================<?php echo $line_endings; ?>==================<?php echo $line_endings; ?>
System: <?php echo php_uname(); ?><?php echo $line_endings; ?>
PHP Version: <?php echo phpversion(); ?><?php echo $line_endings; ?>
PHP/WordPress Memory Limit: <?php echo ini_get( 'memory_limit' ); ?><?php echo $line_endings; ?>
Memory Used: <?php echo $mem_usage; ?><?php echo $line_endings; ?>
PHP Max Execution Time: <?php echo ini_get( 'max_execution_time' ); ?><?php echo $line_endings . $line_endings; ?>
WordPress Version: <?php echo get_bloginfo('version'); ?><?php echo $line_endings; ?>
Total # of wp_options Entries: <?php echo ABD_Database::size_of_wp_options_table(); ?><?php echo $line_endings; ?>
Plugin Version: <?php echo ABD_VERSION; ?><?php echo $line_endings; ?>
Total # of shortcodes: <?php echo ABD_Database::count_shortcodes(); ?><?php echo $line_endings; ?>
BLC Plugin Exists?: <?php echo $blcp_status['plugin_exists'] ? 'true' : 'false'; ?><?php echo $line_endings; ?>
BLC Plugin Active?: <?php echo $blcp_status['plugin_activated'] ? 'true' : 'false'; ?><?php echo $line_endings; ?>
BLC Plugin Type?: <?php echo get_option( 'abd_blc_plugin_type' ); ?><?php echo $line_endings; ?>
BLC Plugin Dir: <?php echo $blcdir; ?><?php echo $line_endings . $line_endings; ?>
9 entry 1D Assoc. Array Used Memory: <?php echo $aa1d_mem; ?><?php echo $line_endings; ?>
9 entry 2D Assoc. Array Used Memory: <?php echo $aa2d_mem; ?><?php echo $line_endings; ?>
			<?php
			
			return ob_get_clean();
		}
	}	//	end class
}	//	end if( !class_exists( ...