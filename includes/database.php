<?php
/**
 * This file contains functions useful for managing the data stored in the database.
 */
if ( !class_exists( 'ABD_Database' ) ) {
	class ABD_Database {
		protected static $our_shortcode_prefix = 'abd_shortcode_';
		protected static $our_shortcode_cache_option = 'abd_sc_cache';	
		protected static $our_shortcode_cache = null;
		protected static $our_stats_table = 'abd_adblocker_stats';

		protected static $our_list_of_options = array(
			'abd_event_log',
			'abd_blc_dir',
			'abd_blc_plugin_type',
			'abd_user_settings',
			'abd_list_of_shortcodes',
			'abd_feedback_nag_time',
			'abd_current_version'
		);
		protected static $our_list_of_transients = array(
			'abd_sc_cache',
			'abd_next_shortcode_id',
			'abd_statistics_cache'
		);

		/**
		 * Returns an array of WordPress database option values for each shortcode.
		 *
		 * @param    int   $site_id              The ID# of the multisite website to filter the results by.
		 * @param    bool  $force_cache_refresh  Whether to ignore cached values and hit the database again.
		 * @param    bool  $ignore_list          Whether to search all options for shortcode matches or use the list stored in the database
		 *
		 * @return   ARRAY_A   An assosciative array of every shortcode. The key is the ID, the value is the database option value (an assosciative array).
		 */
		public static function get_all_shortcodes( $force_cache_refresh = false, $ignore_list = false ) {
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

			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

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
				$update_list_flag = false;
			}
			else {	
				//		Damn... we can't use the cache...
				$sclist = get_option( 'abd_list_of_shortcodes' );
				$update_list_flag = false;
				if( !$sclist ) {
					ABD_Log::error( 'No list of shortcodes available.  Searching all wp_options for matching shortcodes.' );
					$update_list_flag = true;
				}
				if( $ignore_list ) {
					ABD_Log::info( 'Forced ignore of shortcode list option. Searching all wp_options for matching shortcodes.' );
				}
				if( $ignore_list || !$sclist ) {
					//	Get every database option, which we'll search through later
					//	This is slow and takes a crap-ton of memory if the options table is
					//	big with lots of objects and arrays.
					$options = wp_load_alloptions();	//	Get all WP options
				}
				else {
					//	Okay, we can use the list of shortcodes, so get the list, loop
					//	through it, and get each individual shortcode.
					if( !$sclist ) { $sclist = array(); }

					$options = array();
					foreach( $sclist as $scon ) {
						$sc = get_option( $scon );
						if( $sc ) {
							$options[$scon] = $sc;
						}
					}
				}
				$cache_status = false;	//	Flag indicating there was no cache
			}


			//	Filter options and extract the desired stuff
			$prefix_used = self::get_shortcode_prefix();
			$abd_scs = array();
			
			//	$substr_cutoff is the position of the first non-prefix character in
			//	the shortcode strings. This will be used to separate the shortcode ID
			//	from the prefix.
			$substr_cutoff = strlen( $prefix_used );
			$abdsclist = array();

			$time_bfl = microtime( true );
			$mem_bfl = memory_get_usage( true );
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

						//	Add to list of shortcodes
						$abdsclist[] = $prefix_used . $sc_id;
					}
					else {
						//	Hmm... This shouldn't be happening... Let's log it
						ABD_Log::error( 'Failure parsing ID out of shortcode option name. Retrieved option name: ' . $key );
					}
				}
			}
			ABD_Log::perf_summary( 'ABD_Database::get_all_shortcodes() // foreach( $options as $key=>$o ){ ... }', $time_bfl, $mem_bfl, true );

			//wp_die( print_r( $abd_scs, true ) );

			//		Update the cache
			if( !$cache_status ) {
				set_transient( self::$our_shortcode_cache_option, $abd_scs, $max_cache_age );
				ABD_Log::info( 'Updated shortcode cache with results from database query.' );
				//ABD_Log::debug( 'Cache update value: ' . print_r( $abd_scs, true ) );
			}

			//	If we're supposed to update the shortcode list, do so
			if( $update_list_flag ) {
				update_option( 'abd_list_of_shortcodes', $abdsclist );
			}

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::get_all_shortcodes()', $start_time, $start_mem );

			//		Return our results
			return $abd_scs;
		}

		public static function count_shortcodes() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );
			
			$sclist = get_option( 'abd_list_of_shortcodes', array() );

			$count = count( $sclist );

			ABD_Log::perf_summary( 'ABD_Database::count_shortcodes()', $start_time, $start_mem );

			return $count;
		}

		public static function delete_shortcode( $id ) {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			$scname = self::get_shortcode_prefix() . $id;
			$res = delete_option( $scname );

			if( $res ) {
				ABD_Log::info( 'Shortcode with ID=' . $id . ' deleted from database.' );
			}
			else {
				ABD_Log::info( 'Attempt to delete shortcode with ID=' . $id . ' from database unsuccessful. Query failure or no corresponding option in database.' );
			}

			//	Delete the shortcode from the list of shortcodes
			$scs = get_option( 'abd_list_of_shortcodes', array() );
			$key = array_search( $scname, $scs );
			if( $key !== false ) {
				unset( $scs[$key] );				
				ABD_Log::info( 'Removing ' . $sc . ' from list of shortcodes option.' );
			}
			update_option( 'abd_list_of_shortcodes', $scs );

			//		Make sure we update the cache when we retrieve shortcodes next
			self::nuke_shortcode_cache();
			ABD_Log::info( 'Forcing shortcode cache update after shortcode deletion by deleting existing cache.' );

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::delete_shortcode()', $start_time, $start_mem );

			
			return $res;
		}

		public static function get_shortcode( $id, $muffle_output = false ) {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			$res = get_option( self::get_shortcode_prefix() . $id );

			if( !$muffle_output ) {				
				if($res) {
					ABD_Log::info( 'Retrieved 1 shortcode(s) from database.' );
				}
				else {
					ABD_Log::info( 'Failed to retrieve shortcode with ID=' . $id . ' from database. Query failure or no corresponding option in database.' );
				}
			}

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::get_shortcode()', $start_time, $start_mem );
			
			return $res;
		}

		public static function get_next_id( $force_new_transient = false ) {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

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

				$id =  self::get_next_id();
			}

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::get_next_id()', $start_time, $start_mem );

			return $id;
		}

		public static function get_settings( $json_array = false ) {
			$abd_settings = get_option( 'abd_user_settings', array(
				//	Load in some default values that will throw dreadful errors in the middle
				//	of JavaScript code if somebody (*cough* John *cough*) forgets to run an
				//	array_key_exists(), or it is really ugly and inelegant to do so, when 
				//	debug errors are enabled.
				//	
				//	See includes/setup.php > ABD_Setup::enqueue_helper_footer() for a really
				//	rough area for these errors at the time of this writing.
				'enable_iframe'          => 'yes',
				'enable_div'             => 'yes',
				'enable_js_file'         => 'yes',
				'user_defined_selectors' => '',
				'enable_statistics'      => 'yes',
				'stats_ignore_registered'=> 'no',
				'stats_ignore_ips'       => ''
			) );

			if( $json_array && array_key_exists( 'user_defined_selectors', $abd_settings ) ) {
				//	Turn user defined selectors into JSON array
				$abd_settings['user_defined_selectors'] = json_encode( 
					array_map( 'trim', explode( ';', $abd_settings['user_defined_selectors'] ) )
				);
			}
			return $abd_settings;
		}

		public static function get_specific_setting( $setting_name, $json_array = false ) {
			$abd_settings = self::get_settings( $json_array );

			if( array_key_exists( $setting_name, $abd_settings ) ) {
				return $abd_settings[$setting_name];
			}

			return null;
		}


		public static function nuke_all_options() {
			//		Do not collect start state for performance logging
			//		Performance logging gets put in database, and we're trying to delete that!
			

			$sc_options = self::get_all_shortcodes();

			foreach( $sc_options as $id=>$val ) {
				$oname = self::$our_shortcode_prefix . $id;

				delete_option( $oname );
			}
			foreach( self::$our_list_of_options as $o ) {
				delete_option( $o );			
			}
			foreach( self::$our_list_of_transients as $t ) {
				delete_transient( $t );
			}

			//		Do not log performance!
			//		Performance logging gets put in database, and we're trying to delete that!	
		}


		public static function nuke_shortcode_cache() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			delete_transient( self::$our_shortcode_cache_option );

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::nuke_shortcode_cache()', $start_time, $start_mem );			
		}

		public static function drop_tables() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			global $wpdb;
			

			$prefix = $wpdb->base_prefix;
			$table1 = $prefix . 'abd_shortcodes';

			$sql = "DROP TABLE IF EXISTS $table1;";
			$wpdb->query( $sql );

			ABD_Log::info( 'Dropped version 2 table, abd_shortcodes, from the database.' );

			$table2 = $prefix . 'abd_adblocker_stats';

			$sql = "DROP TABLE IF EXISTS $table2;";
			$wpdb->query( $sql );

			ABD_Log::info( 'Dropped version 3 table, abd_adblocker_stats, from the database.' );

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::drop_tables()', $start_time, $start_mem );
		}





		////////////////////////////
		//	Ad Blocker Statistics //
		////////////////////////////
		/**
		 * Unlike shortcodes, statistics are collected into their own database table.
		 * This is because the Settings API, which makes certain tasks easier and faster,
		 * is not suited for an every page load kind of interaction. To use the Settings API,
		 * we'd need to have a new option for every page load, or build an array, which needs
		 * to be retrieved, updated, and put back in the database. This is way too much 
		 * database traffic!
		 *
		 * However, we're only doing a handful of specific jobs for these stats, so a CRUD
		 * setup for the custom table isn't too hard.
		 */
		public static function update_stats_table_structure() {
			global $wpdb;
			$charset = $wpdb->get_charset_collate();
			$table = $wpdb->prefix . self::$our_stats_table;

			$sql = "CREATE TABLE $table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				blog_id mediumint(9) NOT NULL DEFAULT 1,
				adblocker tinyint(1) NOT NULL,
				date_time datetime NOT NULL,
				label text DEFAULT '',
				ip varchar(39),
				PRIMARY KEY  (id),
				KEY adblocker (adblocker),
				KEY blog_id (blog_id),
				KEY ip (ip)
			) $charset;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		/**
		 * Insert a row in the statistics database table.
		 *
		 * @param    int   $adblocker_code   The codified integer adblocker status (-1 = unknown, 0 = no adblocker, 1 = adblocker)
		 * @param    int   $blog_id          The ID of the multisite blog this statistic is collected for. Defaults to 1.  Non-multisite is always 1.
		 * @param    int   $user_ip          The IP address of the user.
		 * @param    string   $date             The date and time this statistic is for. Ommitted = now. Should be a UNIX timestamp.
		 * @param    string   $label            Some descriptive text for the entry.
		 */
		public static function insert_stat( $adblocker_code, $blog_id = 1, $user_ip  = '', $date = 'now', $label = '' ) {
			global $wpdb;

			$table = $wpdb->prefix . self::$our_stats_table;

			//	Get date in right format
			if( is_string( $date ) ) {
				if( $date == 'now' ) {
					$date = date( 'Y-m-d H:i:s' );
				}
				else {
					$date = date( 'Y-m-d H:i:s', $date );
				}
			}
			else {
				ABD_Log::error( 'Invalid date/time value provided when inserting adblocker statistic. Using current date/time.' );
				$date = date( 'Y-m-d H:i:s' );
			}

			//	Insert row
			$res = $wpdb->insert( $table, 
				array( 'adblocker'=>$adblocker_code, 'ip'=> $user_ip, 'date_time'=>$date, 'label'=>$label, 'blog_id' => $blog_id )
			 );

			if( !$res ) {	//	no updated rows or fail
				ABD_Log::error( 'Unknown failure inserting adblocker statistic.' );
				self::log_last_query_debug_info();
			}

			return $res;
		}

		/**
		 * Deletes rows from statistics database.
		 */
		public static function delete_all_stats() {
			global $wpdb;

			$table = $wpdb->prefix . self::$our_stats_table;

			$sql = "DELETE FROM $table WHERE blog_id=" . ABD_Multisite::get_current_blog_id();

			$res = $wpdb->query( $sql );

			if( $res === false ) {
				ABD_Log::error( 'Unknown failure deleting adblocker statistics.' );
				self::log_last_query_debug_info();
			}
			else {
				ABD_Log::info( 'Deleted ' . $rows . ' rows from statistics table in database.' );				
			}
		}

		/**
		 * Returns all rows from statistics database that match provided WHERE and LIMIT clauses
		 *
		 * @param    string/array    $where   WHERE clause string, or named array of WHERE clauses (in column -> value pairs).  Defaults to empty WHERE clause thus not limiting SELECT query.
		 * @param    integer   $limit   A limit on rows returned. 0 or negative number will not limit rows. Defaults to -1.
		 *
		 * @return   array             Associative array containing query results.  Array will be empty if query fails or no rows match query.
		 */
		public static function select_all_stats( $where = '', $limit = -1 ) {
			global $wpdb;

			$table = $wpdb->prefix . self::$our_stats_table;

			//	Construct WHERE clause
			if( is_array( $where ) ) {
				if( !array_key_exists( 'blog_id', $where ) ) {
					$where['blog_id'] = ABD_Multisite::get_current_blog_id();
				}

				$where = self::where_from_array( $where );
			}
			else if( !is_string( $where ) ) {
				ABD_Log::error( 'Invalid $where parameter fed to ABD_Database::select_all_stats(). Defaulting to empty clause.' );
				ABD_Log::debug( '$where parameter data dump = ' . print_r( $where, true ), true );
				$where = ' WHERE blog_id=' . ABD_Multisite::get_current_blog_id();
			}
			else {
				if( empty( $where ) ) {
					$where = ' WHERE blog_id=' . ABD_Multisite::get_current_blog_id();
				}
				else {
					$where .= ' AND blog_id=' . ABD_Multisite::get_current_blog_id();
				}
			}

			//	Construct LIMIT clause
			if( is_numeric( $limit) && $limit > 0 ) {
				$limit = ' LIMIT ' . $limit;
			}
			else {
				$limit = '';
			}

			//	Run query
			$res = $wpdb->get_results( 'SELECT * FROM ' . $table . $where . $limit, ARRAY_A );

			if( !is_array( $res ) || count( $res ) < 1 ) {
				ABD_Log::info( 'SELECT query on statistics database returned empty set.' );
				ABD_Log::debug( 'Empty set query = ' . $wpdb->last_query );

				return array();
			}

			return $res;
		}

		/**
		 * Returns all rows from statisitics database that fall in the date/time range specified. This is basically
		 * a wrapper around ABD_Database::select_all_stats() which will add appropriate date range to WHERE clause
		 * automatically.
		 *
		 * @param    string    $start_date   English textual datetime description of start date filter parsable by PHP's DateTime constructor.
		 * @param    string    $end_date     English textual datetime description fo end date filter parsable by PHP's DateTime constructor.
		 * @param    string/array    $where        WHERE clause string, or named array of WHERE clauses (in column -> value pairs).  Defaults to empty WHERE clause thus not limiting SELECT query.
		 * @param    integer   $limit        A limit on rows returned. 0 or negative number will not limit rows. Defaults to -1.
		 *
		 * @return   array             Associative array containing query results.  Array will be empty if query fails or no rows match query.
		 */
		public static function select_stats_by_date( $start_date, $end_date = 'now', $where = null, $limit = -1 ) {	
			//	Get start and end dates in MySQL acceptable forms
			try {
				$start_date = new DateTime( $start_date );
				$end_date = new DateTime( $end_date );

				$s = $start_date->format( 'Y-m-d H:i:s' );
				$e = $end_date->format( 'Y-m-d H:i:s' );
			}
			catch( Exception $e ) {
				ABD_Log::error( 'Invalid $start_date or $end_date parameter provided in ABD_Database::select_stats_by_date().' );
				ABD_Log::debug( 'DateTime exception = ' . $e->getMessage(), true );

				return array();
			}


			//	Construct WHERE clause without start and end dates
			if( is_array( $where ) ) {
				if( !array_key_exists( 'blog_id', $where ) ) {
					$where['blog_id'] = ABD_Multisite::get_current_blog_id();
				}
				
				$where = self::where_from_array( $where );
			}
			else if( !is_string( $where ) ) {
				ABD_Log::error( 'Invalid $where parameter fed to ABD_Database::select_stats_by_date(). Defaulting to empty clause.' );
				ABD_Log::debug( '$where parameter data dump = ' . print_r( $where, true ), true );
				$where = ' WHERE blog_id=' . ABD_Multisite::get_current_blog_id();
			}
			else {
				if( empty( $where ) ) {
					$where = ' WHERE blog_id=' . ABD_Multisite::get_current_blog_id();
				}
				else {
					$where .= ' AND blog_id=' . ABD_Multisite::get_current_blog_id();
				}
			}


			//	Add start and end dates
			if( empty( $where ) ) {
				$where = ' WHERE ';
			}
			else {
				$where .= ' AND ';
			}

			$where .= 'date > "' . $s . '" AND date < "' . $e . '"';

			return self::select_all_stats( $where, $limit );
		}

		/**
		 * Returns a count of rows in statistics database where the adblocker column contains the given $status_code.
		 *
		 * @param    integer   $status_code   adblocker column status code (adblocker=1, no adblocker=0, other=-1)
		 * @param    string    $custom_query  A custom SQL query to run. The last clause must be a WHERE clause! If no WHERE clause, use "WHERE 1=1".  Use template tag {{table}} in place of table name.
		 *
		 * @return   integer                  A count of matching rows.
		 */
		public static function stats_status_count( $status_code = 1, $custom_query = "SELECT adblocker FROM {{table}} WHERE 1=1" ) {
			global $wpdb;

			//	Check cached value
			$stats_cache = get_transient( 'abd_statistics_cache' );
			if( is_array( $stats_cache ) ) {
				foreach( $stats_cache as $key=>$cache_row ) {
					if( $cache_row['status'] == $status_code && $cache_row['query'] == $custom_query ) {
						if( $cache_row['expires'] > time() ) {
							return $cache_row['count'];
						}
						else {
							unset( $stats_cache[$key] );
						}
					}
				}
			}
			else {	//	Cache has expired, delete its row
				$stats_cache = array();
			}

			//	If here, no cached value is present, so run the database query.
			$table = $wpdb->prefix . self::$our_stats_table;

			$custom_query = str_replace( '{{table}}', $table, $custom_query );

			$sql = $custom_query . " AND adblocker = $status_code AND blog_id=" . ABD_Multisite::get_current_blog_id();
			$wpdb->get_results( $sql );

			$count = intval( $wpdb->num_rows );

			//	Cache results
			$stats_cache[] = array(
				'status'=>$status_code,
				'query'=>$custom_query,
				'count'=>$count,
				'expires'=>time()+86400  // 86400 is 24 hours in seconds
			);
			set_transient( 'abd_statistics_cache', $stats_cache, 86400 );

			return $count;
		}
			public static function stats_status_count_blocker_change( $change_type = 'disable' ) {
				global $wpdb;

				//	Check cached value
				$stats_cache = get_transient( 'abd_statistics_cache' );
				$cache_key = 'blocker_cahnge_'.$change_type;
				if( is_array( $stats_cache ) && array_key_exists( $cache_key, $stats_cache ) ) {
					$cache_row = $stats_cache[$cache_key];
					if( $cache_row['expires'] < time() ) {
						return $cache_row['count'];
					}
				}

				//	If here, no cached value is present, so run database query.
				$table = $wpdb->prefix . self::$our_stats_table;

				$count = 0;

				//	Where multiple rows for distinct ip and multiple values in adblocker for some of those rows
				//	Row returned is first occurrence... meaning starting ad blocker state...
				$sql = "SELECT DISTINCT adblocker, date_time FROM $table WHERE blog_id=" . ABD_Multisite::get_current_blog_id() . " GROUP BY ip HAVING COUNT(DISTINCT adblocker) > 1";
				$res = $wpdb->get_results( $sql, ARRAY_A );

				if( !is_array( $res ) ) {
					ABD_Log::error( 'Unknown query failure in ABD_Database::stats_status_count_blocker_change(). Returning 0.' );
					self::log_last_query_debug_info();
					return 0;
				}

				foreach( $res as $r ) {
					//	Rows contain beginning adblocker state. If we want disabled type, we want
					//	enabled ad blocker in row.  Otherwise, we want disabled type
					if( $change_type == 'disable' && $r['adblocker'] == 1 ) {
						$count++;
					}
					else if( $change_type == 'enable' && $r['adblocker'] == 0 ) {
						$count++;
					}
				}

				//	Cache results
				$stats_cache[$cache_key] = array(
					'status' => $change_type,
					'query'  => $sql,
					'count'  => $count,
					'expires'=> time()+86400	//	86400 is 24 hours in seconds
				);
				set_transient( 'abd_statistics_cache', $stats_cache, 86400 );

				return $count;
			}


		protected static function log_last_query_debug_info( $indented = true ) {
			global $wpdb;

			ABD_Log::debug( 'Failed MYSQL Query = ' . $wpdb->last_query, $indented );
			ABD_Log::debug( 'Failed MYSQL Error = ' . $wpdb->last_error, $indented );
		}

		protected static function where_from_array( $named_array_of_where_clauses ) {
			if( !is_array( $named_array_of_where_clauses ) ) {
				return false;
			}

			$first = true;
			$wc = '';
			foreach( $named_array_of_where_clauses as $field=>$value ) {
				if( $first ) {
					$wc .= ' WHERE ';
					$first = false;
				}
				else {
					$wc .= ' AND ';
				}

				$wc .= $field . '="' . $value . '"';
			}

			ABD_Log::debug( 'Constructed WHERE clause = ' . $wc );

			return $wc;
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







		//	Stats gathering
		public static function size_of_wp_options_table() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			global $wpdb;
			$prefix = $wpdb->base_prefix;

			$res = $wpdb->get_results( 'SELECT COUNT(*) FROM ' . $prefix . 'options', ARRAY_N );
			if( $res) {
				return $res[0][0];
			}
			else {
				return -1;
			}

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::size_of_wp_options_table()', $start_time, $start_mem );
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

			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

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
			//	Before we do, let's reset the script timeout to account for people with a ton
			//	of shortcodes.
			set_time_limit( 60 );
			$nwflag = false;	//	Will be set to true if any network wide shortcodes are detected.
			$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND deleted='0' AND archived='0'", ARRAY_A );
			foreach( $old_scs as $osc ) {
				$loop_start_time = microtime( true );
				$loop_start_mem = memory_get_usage( true );

				ABD_Log::info( 'Found version 2 shortcode. Initiating transfer.' );
				//ABD_Log::debug( 'Old Shortcode Contents: ' . json_encode( $osc ) );
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
					$scns = ABD_Multisite::get_blog_option( $osc['blog_id'], 'abd_list_of_shortcodes', array() );
					$scns[] = self::get_shortcode_prefix() . $osc['id'];
					ABD_Multisite::update_blog_option( $osc['blog_id'], 'abd_list_of_shortcodes', $scns );

					if( $res ) {
						ABD_Log::info( 'Successfully transferred shortcode "' . $osc['name'] . '" from version 2 database table to version 3 WordPress option.' );
					}
					else {
						ABD_Log::error( 'Unknown failure transferring shortcode "' . $osc['name'] . '" from version 2 database table to version 3 WordPress option.' );
						ABD_Log::debug( 'Failed shortcode option value: ' . print_r( $nsc, true ) );
					}
				}

				ABD_Log::perf_summary( 'ABD_Database::v2_to_v3_database_transfer() // transfer loop iteration', $loop_start_time, $loop_start_mem, true );
			}

			//	Warn users about use of any deprecated features (network wide shortcodes)
			if( $nwflag ) {
				add_action( 'network_admin_notices',
					array( 'ABD_Admin_Views', 'deprecated_network_wide_shortcodes_notice' ) );
			}

			//	Delete list of shortcodes so it's reconstructed 
			foreach( $blogs as $blog ) {
				 ABD_Multisite::delete_blog_option( $blog['blog_id'], 'abd_list_of_shortcodes' );
			}

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::v2_to_v3_database_transfer()', $start_time, $start_mem );
		}

		public static function v31_to_v32_database_update() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			//	Versions 3.0.0 and 3.0.1 did not utilize the list of shortcodes database
			//	option which improves performance and reduces memory usage. This was introduced
			//	in version 3.0.2.  So, if we're using versions 3.0.0 and 3.0.1, we need to look
			//	for shortcodes the old way, and add them to the list.
			ABD_Log::info( 'Update Progress - Initializing search of wp_options table for existing shortcodes.' );
			$of_scs = self::get_all_shortcodes( true, true );
			$list = get_option( 'abd_list_of_shortcodes', array() );

			foreach( $of_scs as $key=>$of_sc ) {
				if( array_search( $key, $list ) === false ) {	//	Not in list
					ABD_Log::info( 'Update Progress - Found unlisted shortcode in database, adding ' . self::$our_shortcode_prefix . $key . ' to list.' );
					$list[] = self::$our_shortcode_prefix . $key;
				}
			}

			update_option( 'abd_list_of_shortcodes', $list );

			ABD_Log::info( 'Update Progress - Finished search of wp_options table for existing shortcodes.' );

			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::v31_to_v32_database_update()', $start_time, $start_mem );
		}

		

	}	//	end class ABD_Database
}	//	end if( !class_exists( ...