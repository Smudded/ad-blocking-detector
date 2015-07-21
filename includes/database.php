<?php
/**
 * This file contains functions useful for managing the data stored in the database.
 */
if ( !class_exists( 'ABD_Database' ) ) {
	class ABD_Database {
		protected static $our_shortcode_prefix = 'abd_shortcode_';
		protected static $our_shortcode_cache_option = 'abd_sc_cache';	
		protected static $our_shortcode_cache = null;

		/**
		 * Returns an array of WordPress database option values for each shortcode.
		 *
		 * @param    int   $site_id              The ID# of the multisite website to filter the results by.
		 * @param    bool  $force_cache_refresh  Whether to ignore cached values and hit the database again.
		 *
		 * @return   ARRAY_A   An assosciative array of every shortcode. The key is the ID, the value is the database option value (an assosciative array).
		 */
		public static function get_all_shortcodes( $force_cache_refresh = false ) {
			/**
			*     )     (                    (     (                      )     ) (     
			*	 ( /(     )\ )        (        )\ )  )\ )   (     (      ( /(  ( /( )\ )  
			*	 )\())(  (()/((     ( )\ (    (()/( (()/(   )\    )\ )   )\()) )\()|()/(  
			*	((_)\ )\  /(_))\    )((_))\    /(_)) /(_)|(((_)( (()/(  ((_)\ ((_)\ /(_)) 
			*	 _((_|(_)(_))((_)  ((_)_((_)  (_))_ (_))  )\ _ )\ /(_))_  ((_) _((_|_))   
			*	| || | __| _ \ __|  | _ ) __|  |   \| _ \   /_\  / __|/ _ \| \| / __|  
			*	| __ | _||   / _|   | _ \ _|   | |) |   /  / _ \| (_ | (_) | .` \__ \  
			*	|_||_|___|_|_\___|  |___/___|  |___/|_|_\ /_/ \_\\___|\___/|_|\_|___/  
			*
			* The problem is that I'm trying to milk more functionality from the WordPress
			* Settings API than it is really meant to do.  In a previous version I used a 
			* dedicated database table for this plugin, but it made code maintenance absolutely
			* dreadful.  Using a Settings API wrapper I have sitting in my code toolbox, 
			* maintenance is much simpler as far
			* as adding features and tweaking user interfaces is concerned (where 90% of the
			* work is done).
			*
			* However, the trade-off for that is some fancy footwork must be done in certain
			* areas.  Here, that footwork is some special naming conventions for database options that 
			* map to each shortcode (a prefix followed by the shortcode ID#).  Next is
			* to do some homebrew caching and performance optimization to keep things from
			* grinding to a halt.  Especially with regards to this function which is called
			* for damn near everything this plugin does.
			*
			* If you touch the code below, you should ensure caching works appropriately, and
			* performance doesn't take a hit.  You can uncomment some logging lines of code
			* below to output more information to the Session Log that will aid in checking
			* this.  They're commented out because they spam the hell out of the Session Log.
			*/

			//		This function gets called a lot... for information purposes, let's record
			//		how long it takes.
			$start_time = microtime(true);

			//		Get cache results... if any
			self::$our_shortcode_cache = get_transient( self::$our_shortcode_cache_option );
			if( self::$our_shortcode_cache	=== false ) {
				self::$our_shortcode_cache = null;		//		No transient... make this null to be more clear about this.
			}

			
			//		First, let's do some state logging
			$max_cache_age = 300;		//		5 minutes in seconds
			if( is_null( self::$our_shortcode_cache ) ) {
				ABD_Log::info( 'No shortcode cache. Querying database for shortcodes.' );
			}
			else if( $force_cache_refresh ) {
				ABD_Log::info( 'Forced ignore of shortcode cache. Querying database for shortcodes.' );
			}



			//		Now, do we use the cached value?
			if( !is_null( self::$our_shortcode_cache ) && !$force_cache_refresh ) {
				//		Yes, we can use the cache...				
				$options = self::$our_shortcode_cache;
				$cache_status = true;
			}
			else {	
				//		Damn... we can't use the cache...  Gotta filter ALL options			
				$options = wp_load_alloptions();	//	Get all WP options
				$cache_status = false;	//	Flag indicating there was no cache
			}


			//	Filter options and extract the desired stuff
			$prefix_used = self::get_shortcode_prefix();
			$abd_scs = array();
			
			//	$substr_cutoff is the position of the first non-prefix character in
			//	the shortcode strings. This will be used to separate the shortcode ID
			//	from the prefix.
			$substr_cutoff = strlen( $prefix_used );


			foreach( $options as $key=>$o ) {
				//	All ABD shortcode options are prefixed by $prefix_used
				//	So, if this came straight from the database, all we need to do 
				//	is check for the prefix string at the beginning of the key.  If it is cached,
				//	then the key has already been filtered out.
				if( $cache_status ) {
					//	$options came from cache... no filtering needed
					$sc_id = $key;
				}
				else {
					//	$options came from database... need to strip out the $prefix_used
					$sc_id = substr( $key, $substr_cutoff );
				}

				if( $cache_status || strpos( $key, $prefix_used ) === 0 ) {	//	If cache, always a valid SC... otherwise, use strpos to check $prefix_used
					//	Okay, this matches our shortcode criteria.					

					//	Unexpected failure check. If there's no $sc_id, this isn't a valid
					//	shortcode. So, make sure we have an $sc_id
					if( !empty( $sc_id ) ) {
						//	Okay, we have the ID

						//	Get the SC as a normal value
						$sc = maybe_unserialize( $o );

						//	Store result in array
						$abd_scs[$sc_id] = $sc;
					}
					else {
						//	Hmm... This shouldn't be happening... Let's log it
						ABD_Log::error( 'Failure parsing ID out of shortcode option name. Retrieved option name: ' . $key );
					}
				}
			}

			//wp_die( print_r( $abd_scs, true ) );

			//		Update the cache
			if( !$cache_status ) {
				set_transient( self::$our_shortcode_cache_option, $abd_scs, $max_cache_age );
				ABD_Log::info( 'Updated shortcode cache with results from database query.' );
				//ABD_Log::debug( 'Cache update value: ' . print_r( $abd_scs, true ) );
			}

			//		Performance log
			$total_time = round( (microtime(true) - $start_time) * 1000, 3 );		//		milliseconds to 3 decimal places
			//ABD_Log::info( 'Retrieved ' . count($abd_scs) . ' shortcode(s) from database in ' . $total_time . ' milliseconds.' );

			//		Return our results
			return $abd_scs;
		}

		public static function delete_shortcode( $id ) {
			$res = delete_option( self::get_shortcode_prefix() . $id );

			if( $res ) {
				ABD_Log::info( 'Shortcode with ID=' . $id . ' deleted from database.' );
			}
			else {
				ABD_Log::info( 'Attempt to delete shortcode with ID=' . $id . ' from database unsuccessful. Query failure or no corresponding option in database.' );
			}

			//		Make sure we update the cache when we retrieve shortcodes next
			self::nuke_shortcode_cache();
			ABD_Log::info( 'Forcing shortcode cache update after shortcode deletion by deleting existing cache.' );
			

			return $res;
		}

		public static function get_shortcode( $id, $muffle_output = false ) {
			$res = get_option( self::get_shortcode_prefix() . $id );

			if( !$muffle_output ) {				
				if($res) {
					ABD_Log::info( 'Retrieved 1 shortcode(s) from database.' );
				}
				else {
					ABD_Log::info( 'Failed to retrieve shortcode with ID=' . $id . ' from database. Query failure or no corresponding option in database.' );
				}
			}
			
			return $res;
		}

		public static function get_next_id( $force_new_transient = false ) {
			$id = get_transient( 'abd_next_shortcode_id' );

			$id_in_use = self::get_shortcode( $id, true ) ? true : false;

			if( $force_new_transient || $id_in_use ) { //	If we're forcing a new one, or this one is in use, try again.
				$id = false; 
			}

			//	Is there an ID already primed and ready, and if so, are we sure it hasn't already
			//	been used?
			if ( empty( $id ) || get_site_option( self::get_shortcode_prefix() . $id ) ) {
				//	Damn, no automatically generated ID, or the ID has already been used.
				//	Generate a new one.
				set_transient( 'abd_next_shortcode_id', uniqid(), 86400 );

				return self::get_next_id();
			}

			return $id;
		}

		public static function get_settings( $json_array = false ) {
			$abd_settings = get_option( 'abd_user_settings', array(
					'user_defined_selectors' => '',
					'enable_iframe'          => 'yes',
					'enable_div'             => 'yes',
					'enable_js_file'         => 'yes'
				)
			);

			if( $json_array ) {
				//	Turn user defined selectors into JSON array
				$abd_settings['user_defined_selectors'] = json_encode( 
					array_map( 'trim', explode( ';', $abd_settings['user_defined_selectors'] ) )
				);
			}			

			return $abd_settings;
		}


		public static function nuke_all_options() {
			$options = wp_load_alloptions();	//	Get all WP options

			foreach( $options as $key=>$o ) {
				if( stristr( $key, 'abd_' ) ) {
					delete_site_option( $key );					
				}				
			}

			//		Make sure we update the cache when we retrieve shortcodes next
			self::nuke_shortcode_cache();			
		}


		public static function nuke_shortcode_cache() {
			delete_transient( self::$our_shortcode_cache_option );			
		}

		/**
		 * Extracts value of array entry with given key if it exists.
		 *
		 * @param    mixed    $needle              Array key.
		 * @param    array    $haystack            The array.
		 * @param    mixed    $no_such_key_value   What to return if the array doesn't have the key. Defaults to empty string.
		 *
		 * @return   mixed                         The value in $haystack[$needle] if it exists, otherwise $no_such_key_value
		 */
		public static function array_value( $needle, $haystack, $no_such_key_value='' ) {
			if( is_array( $haystack ) && array_key_exists( $needle, $haystack ) ) {
				return $haystack[$needle];
			}

			return $no_such_key_value;
		}

		//	Accessors and modifiers
		public static function get_shortcode_prefix() {
			return self::$our_shortcode_prefix;
		}










		//////////////////////////////
		//	Update compatibility	//
		//////////////////////////////
		public static function v2_to_v3_database_transfer() {
			/**
			*     )     (                    (     (                      )     ) (     
			*	 ( /(     )\ )        (        )\ )  )\ )   (     (      ( /(  ( /( )\ )  
			*	 )\())(  (()/((     ( )\ (    (()/( (()/(   )\    )\ )   )\()) )\()|()/(  
			*	((_)\ )\  /(_))\    )((_))\    /(_)) /(_)|(((_)( (()/(  ((_)\ ((_)\ /(_)) 
			*	 _((_|(_)(_))((_)  ((_)_((_)  (_))_ (_))  )\ _ )\ /(_))_  ((_) _((_|_))   
			*	| || | __| _ \ __|  | _ ) __|  |   \| _ \   /_\  / __|/ _ \| \| / __|  
			*	| __ | _||   / _|   | _ \ _|   | |) |   /  / _ \| (_ | (_) | .` \__ \  
			*	|_||_|___|_|_\___|  |___/___|  |___/|_|_\ /_/ \_\\___|\___/|_|\_|___/  
			*
			* There's all sorts of ugliness below.  We're transferring a single database table
			* to WordPress options where the option name has very special formatting, and to
			* keep old shortcodes from no longer displaying, we must be even more careful.
			* We've got to deal with multisite, which used to be easy, with the table, but
			* is now a major pain-in-the-ass because options can be stored all over hell and 
			* back.  And, some old features are deprecated, so we need to do something about
			* that.
			*
			* This code seems to work.  It's run once, during plugin updates.  Touch it
			* at your own risk.  And, for the love of God, back up what's here first.
			*/

			//	Versions 1 and 2 used their own database tables to store shortcodes.
			//	Version 3 uses WordPress options to take advantage of the Settings API.
			//	
			//	This function is to extract the shortcodes from the old database, turn them
			//	into values, and store them as an option.

			//	Get old shortcodes
			global $wpdb;

			$prefix = $wpdb->base_prefix;
			$table = $prefix . 'abd_shortcodes';

			$sql = "SELECT * FROM $table";
			$old_scs = $wpdb->get_results( $sql, ARRAY_A );

			if( empty( $old_scs ) ) {
				//	Nothing to do... return
				return;
			}


			//	Loop through old shortcodes and make it a new shortcode
			$nwflag = false;	//	Will be set to true if any network wide shortcodes are detected.
			foreach( $old_scs as $osc ) {
				ABD_Log::info( 'Found version 2 shortcode. Initiating transfer.' );
				ABD_Log::debug( 'Old Shortcode Contents: ' . json_encode( $osc ) );
				$nsc = array(
					'display_name' => $osc['name'],
					'noadblocker'  => $osc['noadblock'],
					'adblocker'    => $osc['adblock'],
					'blog_id'      => $osc['blog_id']
				);

				//	Make formerly network wide shortcodesappear on all blogs and be uneditable
				if( $osc['network_wide'] && ABD_Multisite::is_this_a_multisite() ) {
					$nwflag = true;	//	Set our flag to indicate we've stumbled upon a network wide shortcode in a network
					ABD_Log::info( 'Detected multisite "Network Wide" shortcode. Version 3 no longer supports "Network Wide" shortcodes. Copying this shortcode to each site in network and marking it readonly.' );
					//	Damn... we need to add this to EVERY site's options table...
					//	First, let's do that uneditable bit
					$nsc['blog_id'] = -1;
					$nsc['readonly'] = true;

					//	Now, get all blog IDs
					$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND deleted='0' AND archived='0'", ARRAY_A );					
					if( !empty( $blogs ) ) {
						ABD_Log::debug( 'Found ' . count( $blogs ) . ' multisite sites to add shortcode to.' );
						
						//	Loop through all blogs and update_blog_option
						foreach( $blogs as $blog ) {
							$id = $blog['blog_id'];
							$res = ABD_Multisite::update_blog_option( $id, self::get_shortcode_prefix() . $osc['id'], $nsc );

							if( $res ) {
								ABD_Log::info( 'Successfully copied network wide shortcode "' . $osc['name'] . '" to multisite site "' . ABD_Multisite::get_blog_option( $id, 'blogname' ) );
							}
							else {
								ABD_Log::error( 'Unknown failure transferring shortcode "' . $osc['name'] . '" to multisite site "' . ABD_Multisite::get_blog_option( $id, 'blogname' ) . '"' );
								ABD_Log::debug( 'Site ID: ' . $id . ', Failed shortcode option value: ' . print_r( $nsc, true ) );
							}
						}
					}
				}
				else {
					//	Not a network wide shortcode or not a network... this is easier
					$res = ABD_Multisite::update_blog_option( $osc['blog_id'], self::get_shortcode_prefix() . $osc['id'], $nsc );

					if( $res ) {
						ABD_Log::info( 'Successfully transferred shortcode "' . $osc['name'] . '" from version 2 database table to version 3 WordPress option.' );
					}
					else {
						ABD_Log::error( 'Unknown failure transferring shortcode "' . $osc['name'] . '" from version 2 database table to version 3 WordPress option.' );
						ABD_Log::debug( 'Failed shortcode option value: ' . print_r( $nsc, true ) );
					}
				}				
			}

			//	Warn users about use of any deprecated features (network wide shortcodes)
			if( $nwflag ) {
				add_action( 'network_admin_notices',
					array( 'ABD_Admin_Views', 'deprecated_network_wide_shortcodes_notice' ) );
			}
		}

		public static function drop_v2_table() {
			global $wpdb;
			

			$prefix = $wpdb->base_prefix;
			$table = $prefix . 'abd_shortcodes';

			$sql = "DROP TABLE IF EXISTS abd_shortcodes;";
			$wpdb->query( $sql );

			ABD_Log::info( 'Dropped version 2 table, abd_shortcodes, from the database.' );
		}

	}	//	end class ABD_Database
}	//	end if( !class_exists( ...