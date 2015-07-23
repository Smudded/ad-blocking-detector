<?php
/**
 * This file containst the 'ABD_Anti_Adblock' class definition which provides tools for foiling
 * adblocker blocking of this plugin.
 */

if ( !class_exists( 'ABD_Anti_Adblock' ) ) {
	class ABD_Anti_Adblock {
		protected static $our_bcc_plugin_filename = 'ad-blocking-detector-block-list-countermeasure.php';

		/**
		 * Generates a random directory name that shouldn't seem like an ad blocker at all.
		 * Will be a cute mashup of an adjective and a noun if text files are readable. Otherwise,
		 * will be alphanumeric string.
		 *
		 * @return   string   A random string suitable for a directory name.
		 */
		public static function generate_new_dir_name() {
			$path_to_adj = ABD_ROOT_PATH . 'assets/list-of-adjectives.txt';
			$path_to_noun = ABD_ROOT_PATH . 'assets/list-of-nouns.txt';

			if( is_readable( $path_to_adj ) && is_readable( $path_to_noun ) ) {
				$adjectives = file( ABD_ROOT_PATH . 'assets/list-of-adjectives.txt', FILE_IGNORE_NEW_LINES );
				$nouns = file( ABD_ROOT_PATH . 'assets/list-of-nouns.txt', FILE_IGNORE_NEW_LINES );
			}
			else {
				$adjectives = null;
				$nouns = null;
			}

			if( empty( $adjectives ) || empty( $nouns ) ) {
				//	Well, we couldn't get creative names... try something random
				return uniqid( '', true );
			}

			$nme = $adjectives[array_rand( $adjectives )] . '-' . $nouns[array_rand( $nouns )];

			//	Make sure it's a safe name
			$nme = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $nme);

			ABD_Log::info( 'Generated new automatic Block List Countermeasure plugin directory name: ' . $nme );

			return $nme;
		}

		//	Gets the directory name of 
		public static function get_bcc_plugin_dir_name() {
			//	If we have a stored one, get it.
			$dn = get_site_option( 'abd_blc_dir' );

			return $dn;
		}

		public static function create_bcc_plugin() {
			//	Path to fallback dir is one directory up from this plugin's directory, then
			//	down to the bcc_plugin_dir_name
			$dir_only = self::get_bcc_plugin_dir_name();
			if( !$dir_only ) {
				$dir_only = self::generate_new_dir_name();
			}

			$fp_dir = ABD_ROOT_PATH . '../' . $dir_only;
			$fp_plugin_path = ABD_ROOT_PATH . 'assets/anti-adblock/plugin-files/';

			//	If the manual plugin exists, we don't want to do this automatic plugin
			$st = self::bcc_plugin_status();
			if( $st['manual_plugin_exists'] ) {
				ABD_Log::error( 'Attempted to install automatic Block List Countermeasure plugin when manual version already exists. Stopping install.' );
				add_action( 'admin_notices',
					array( 'ABD_Admin_Views', 'manual_plugin_exists_notification' ) );
				return;
			}

			//	Remove any old crap
			self::delete_dir( $fp_dir );

			//	Add directories
			mkdir( $fp_dir );

			//	Okay, we need to copy the the files in /assets/anti-adblock, and the entire
			//	directory /assets/ to the root of our fallback plugin directory.  If any process
			//	fails or errors out, return false.
			if( !copy( $fp_plugin_path . self::$our_bcc_plugin_filename, $fp_dir . '/' . self::$our_bcc_plugin_filename ) ) {
				ABD_Log::error( 'Block List Countermeasure plugin creation failed: Could not copy main plugin file.' );
				return false;
			}if( !copy( $fp_plugin_path . 'readme.txt', $fp_dir . '/readme.txt' ) ) {
				ABD_Log::error( 'Block List Countermeasure plugin creation failed: Could not copy plugin readme file.' );
				return false;
			}
			if( !self::copy_dir( ABD_ROOT_PATH . 'assets/', $fp_dir . '/assets/' ) ) {
				ABD_Log::error( 'Block List Countermeasure plugin creation failed: Could not recursively copy /assets/ directory.' );
				return false;
			}

			//	If we've reached this point, everything was successful
			//	Store a flag in the DB so we know the BLC plugin is automatic, and store its dir name.
			update_site_option( 'abd_blc_plugin_type', 'auto' );
			update_site_option( 'abd_blc_dir', $dir_only );
			ABD_Log::info( 'Successfully created Block List Countermeasure plugin in directory ' . $dir_only );
			return true;
		}

		public static function delete_bcc_plugin() {
			$do = self::get_bcc_plugin_dir_name();
			if( !$do ) {
				ABD_Log::error( 'Attempt to delete automatic Block List Countermeasure plugin, but no directory stored.  Plugin already deleted?' );
				return true;
			}

			$fp_dir = ABD_ROOT_PATH . '../' . $do;

			//		Deactivate the plugin
			if( defined( 'ABDBLC_SUBDIR_AND_FILE' ) ) {
				deactivate_plugins( ABDBLC_SUBDIR_AND_FILE );
			}

			if( !is_dir( $fp_dir ) ) {
				//	There's nothing to delete... log it and report YAY
				ABD_Log::debug( 'Attempt to delete non-existent automatic Block List Countermeasure plugin.' );
				return true;
			}

			$res = self::delete_dir( $fp_dir );

			if( $res ) {
				//	Remove DB flag
				delete_site_option( 'abd_blc_plugin_type' );
				delete_site_option( 'abd_blc_dir' );
				ABD_Log::info( 'Deleted Block List Countermeasure plugin successfully from ' . $do );
			}

			return $res;
		}

		public static function bcc_plugin_status() {
			$status = array( 'auto_plugin_exists' => 0, 'auto_plugin_activated' => 0, 'manual_plugin_exists' => -1, 'manual_plugin_activated' => 0 );

			$dn = get_site_option( 'abd_blc_dir' );
			$pt = get_site_option( 'abd_blc_plugin_type' );

			if( $dn ) {//	Plugin should exist				
				//	Let's make sure
				$plugin_path = ABD_ROOT_PATH . '../' . $dn;
				
				if( !is_dir( $plugin_path ) ) {
					ABD_Log::error( 'Block List Countermeasure plugin does not exist at the location it is expected to: ' . $plugin_path );

					//	The directory option is obviously out of sync...
					delete_site_option( 'abd_blc_dir' );

					//	The plugin does not exist... therefore it isn't activated either... so, return our vanilla $status
					return $status;
				}

				//	If we're here, the plugin exists.  Now we just need to know whether it's automatic or manual
				if( $pt == 'auto' ) {
					//	It's automatic
					$status['auto_plugin_exists'] = 1;
					$status['manual_plugin_exists'] = 0;
				}
				else {
					//	It's manual
					$status['manual_plugin_exists'] = 1;
					$status['auto_plugin_exists'] = 0;
				}

				//	Let's check if the plugin is activated.  The simplest way to do that is check whether
				//	one of it's crucial constants is defined.
				if( defined( 'ABDBLC_ROOT_PATH' ) ) {
					//	Yes, it is activated
					if( $pt == 'auto' ) {
						$status['auto_plugin_activated'] = 1;
						$status['manual_plugin_activated'] = 0;
					}
					else {
						$status['manual_plugin_activated'] = 1;
						$status['auto_plugin_activated'] = 0;
					}
				}
			}
			else {	//	Plugin does not exist
				$status['manual_plugin_exists'] = 0;
				$status['auto_plugin_exists'] = 0;
			}

			return $status;
		}









		public static function get_bcc_manual_plugin_dir_name( $force_file_read = false ) {
			$status = self::bcc_plugin_status();

			if( $status['manual_plugin_exists'] && !$force_file_read ) {
				$pn = get_site_option( 'abd_blc_dir' );
				if( $pn === false ) {
					ABD_Log::error( 'Could not retrieve Block List Countermeasure plugin directory name from database. It should exist, but doesn\'t seem to.' );
					return false;
				}
			}
			else {
				$pn = file_get_contents( ABD_ROOT_PATH . 'assets/anti-adblock/zip-name.txt' );
				if( $pn === false ) {
					ABD_Log::error( 'Could not read in name of manual Block List Countermeasure plugin directory from file.' );
					return false;
				}
			}		

			return $pn;
		}


		public static function get_bcc_manual_plugin_relative_path( $force_file_read = false ) {
			$pn = self::get_bcc_manual_plugin_dir_name( $force_file_read );

			if( !$pn ) {
				ABD_Log::error( 'Could not construct manual Block List Countermeasure plugin relative path. get_bcc_manual_plugin_dir_name() failed.' );
				return false;
			}

			return 'assets/anti-adblock/' . $pn . '.zip';
		}

		public static function get_bcc_manual_plugin_url( $force_file_read = false ) {
			$pn = self::get_bcc_manual_plugin_relative_path( $force_file_read );

			if( !$pn ) {
				ABD_Log::error( 'Could not construct manual Block List Countermeasure plugin URL. get_bcc_manual_plugin_relative_path() failed.' );
				return false;
			}

			return ABD_ROOT_URL . $pn;
		}

		public static function get_bcc_manual_plugin_absolute_path( $force_file_read = false ) {
			$pn = self::get_bcc_manual_plugin_relative_path( $force_file_read );

			if( !$pn ) {
				ABD_Log::error( 'Could not construct manual Block List Countermeasure plugin absolte path. get_bcc_manual_plugin_relative_path() failed.' );
				return false;
			}

			return ABD_ROOT_PATH . $pn;
		}

		public static function delete_bcc_manual_plugin() {
			$fp_dir = ABD_ROOT_PATH . '../' . self::get_bcc_manual_plugin_dir_name();

			//		Deactivate the plugin
			if( defined( 'ABDBLC_SUBDIR_AND_FILE' ) ) {
				deactivate_plugins( ABDBLC_SUBDIR_AND_FILE );
			}

			if( !is_dir( $fp_dir ) ) {
				//	There's nothing to delete... log it and report YAY
				ABD_Log::debug( 'Attempt to delete non-existent manual Block List Countermeasure plugin.' );
				return true;
			}

			$res = self::delete_dir( $fp_dir );

			if( $res ) {
				ABD_Log::info( 'Deleted manual Block List Countermeasure plugin successfully from ' . self::get_bcc_manual_plugin_dir_name() );
				delete_site_option( 'abd_blc_dir' );
				delete_site_option( 'abd_blc_plugin_type' );
			}

			return $res;
		}










		/**
		 * Recursively copys the $source_dir to the $destination_dir.
		 *
		 * @param    string   $source_dir        Path to the source directory to copy.
		 * @param    string   $destination_dir   Path to the destination directory.
		 *
		 * @return   boolean                      true on success, false on failure.
		 */
		public static function copy_dir( $source_dir, $destination_dir ) {
			try {
				$dir = opendir( $source_dir );
				if(  !mkdir( $destination_dir ) ) {
					//	Failure
					ABD_Log::debug( 'ABD_Anti_Adblock::copy_dir() failure point: mkdir' );
					throw new Exception( 'Error creating directory.' );
				}

				while( false !== ( $file = readdir( $dir ) ) ) {
					if( $file != '.' && $file != '..' ) {
						if( is_dir( $source_dir . '/' . $file ) ) {
							if( !self::copy_dir( $source_dir . '/' . $file, $destination_dir . '/' . $file ) ) {
								ABD_Log::debug( 'ABD_Anti_Adblock::copy_dir() failure point: recursive ABD_Anti_Adblock::copy_dir() call' );
								throw new Exception( 'Recursive copy failed.' );
							}
						}
						else {
							if( !copy( $source_dir . '/' . $file, $destination_dir . '/' . $file ) ) {
								//	Failure
								ABD_Log::debug( 'ABD_Anti_Adblock::copy_dir() failure point: copy()' );
								throw new Exception( 'Copy operation failed.' );
							}
						}
					}
				}
				closedir( $dir );
			}
			catch ( Exception $e ) {
				return false;
			}

			return true;
		}

		/**
		 * Recursively deletes the $directory and all of its contents
		 *
		 * @param    string   $directory   Path to the directory to delete.
		 *
		 * @return   boolean                true on success, false on failure.
		 */
		public static function delete_dir( $directory ) {
			try {
				if( !is_dir( $directory ) ) {
					//	WTF? That's not a directory! Log it and return happily.
					ABD_Log::debug( 'Request to delete directory that does not exist: ' . $directory );
					return true;
				}

				foreach( glob( "{$directory}/*" ) as $file ) {
					if( is_dir( $file ) ) {
						if( !self::delete_dir( $file ) ) {
							ABD_Log::debug( 'ABD_Anti_Adblock::delete_dir() failure point: recursive ABD_Anti_Adblock::delete_dir() call on ' . $file );
							throw new Exception( 'Delete operation failed.' );
						}
					}
					else if ( is_file( $file ) ) {
						if( !unlink( $file ) ) {
							ABD_Log::debug( 'ABD_Anti_Adblock::delete_dir() failure point: unlink() call on ' . $file );
							throw new Exception( 'Delete operation failed.' );
						}
					}
					else {
						ABD_Log::debug( 'ABD_Anti_Adblock::delete_dir() failure point: PHP does not recognize ' . $file . ' as a directory, or a file. Can not delete/unlink it.' );
						throw new Exception( 'Delete operation failed' );
					}
				}

				if( !rmdir( $directory ) ) {
					ABD_Log::debug( 'ABD_Anti_Adblock::delete_dir() failure point: final directory deletion' );
					throw new Exception( 'Delete operation failed.' );
				}
			}
			catch( Exception $e ) {
				return false;
			}

			return true;
		}


		public static function check_plugin_dir() {
			$p_dir = ABD_ROOT_PATH . '../';

			if( is_dir( $p_dir ) && is_writable( $p_dir ) ) {
				return 1;
			}

			return 0;
		}

		public static function check_php_version( $min = '5.3.0' ) {
			if( version_compare( phpversion(), '5.3.0' ) >= 0 ) {
				return 1;
			}

			return 0;
		}

		public static function check_safe_mode(  ) {
			if( version_compare( phpversion(), '5.4.0' ) >= 0 ) {
				//	PHP 5.4+ doesn't support safe_mode... so no safe_mode
				return 1;
			}
			else if( !ini_get('safe_mode') ) {
				return 1;
			}
			else {
				return 0;
			}
		}

		public static function check_mkdir() {
			$p_dir = ABD_ROOT_PATH . '../';

			$test_dir = $p_dir . 'joyous-pelican-mkdir-test';	//	Just a random name for testing

			//	Does directory already exist?
			if( is_dir( $test_dir ) ) {	//	Yes
				return -1;	//	Inconclusive result
			}

			//	Try creating directory.
			try {
				mkdir( $test_dir, 0777 );

				if( is_dir( $test_dir ) ) {	//	Success
					//	Cleanup
					try { rmdir( $test_dir ); } catch( Exception $e ) {}

					return 1;
				}

				return 0;	//	Failure
			} 
			catch( Exception $e ) {
				return 0;	//	Failure
			}
		}

		public static function check_all( $omit_php_version_check = true ) {
			if( $omit_php_version_check ) {
				$php = true;
			}
			else {
				$php = self::check_php_version();
			}

			return self::check_plugin_dir() && self::check_safe_mode() && self::check_mkdir() && $php;
		}
	}	//	end class
}	//	end if ( !class_exists( ...