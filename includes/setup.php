<?php
/**
 * This file contains a static-esque class with all necessary setup, enqueue,
 * register, and other related WordPress items.
 */

require_once( ABD_ROOT_PATH . "includes/ajax-actions.php" );
require_once ( ABD_ROOT_PATH . 'views/public-views.php' );


if ( !class_exists( 'ABD_Setup' ) ) {
	class ABD_Setup {
		protected static $version = '2.1.1';

		/**
		 * Registers and enqueues all CSS and JavaScript.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function enqueue() {
			//	Enqueue admin CSS
			add_action( 'admin_enqueue_scripts', 
				array('ABD_Setup', 'enqueue_helper_admin_css') );

			//	Enqueue admin JS
			add_action( 'admin_enqueue_scripts', 
				array( 'ABD_Setup', 'enqueue_helper_admin_js' ) );

			//	Enqueue public facing JS
			add_action( 'wp_enqueue_scripts',
				array( 'ABD_Setup', 'enqueue_helper_public_js' ) );

			//	Add AJAX listeners
			add_action( 'wp_ajax_abd_ajax',
				array( 'ABD_Ajax_Actions', 'navigate' ) );


			//	Add fake ads to footer
			add_action( 'wp_footer', 
				array( 'ABD_Setup', 'enqueue_helper_footer' ), 501 );
		}
			public static function enqueue_helper_admin_css() {
				wp_register_style( 'abd-admin-css', 
					ABD_ROOT_URL . 'assets/css/admin.css' );

				wp_enqueue_style( 'abd-admin-css' );
			}
			public static function enqueue_helper_admin_js() {
				
			}
			public static function enqueue_helper_public_js() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'abd-adblock-detector', 
					ABD_ROOT_URL . 'assets/js/adblock-detector.js' );
				wp_enqueue_script( 'abd-fake-ad', 
					ABD_ROOT_URL . 'assets/js/advertisement.js' );
			}
			public static function enqueue_helper_footer() {
				?>
				
				<div 
					id='abd-ad-iframe-wrapper'
					style="position: fixed !important; bottom: -999em !important; left: -999em !important; width: 0 !important; height: 0 !important; overflow: hidden !important;">
					
					<iframe id="abd-ad-iframe" src="http://exadwese.us/adserver/adlogger_tracker.php" style="height: 728px; width: 90px;"></iframe>
				</div>

				<div 
					id="abd-ad-div" 
					style="position: fixed !important; bottom: -999em !important; left: -999em !important; display: block !important; visibility: visible !important; width: 0 !important; height: 0 !important;">

					Advertisment ad adsense adlogger
				</div>
				<?php
			}

		/**
		 * Registers and defines all WordPress hooks. (e.g. activation / 
		 * deactivation)
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function hooks() {
			//	Activation
			register_activation_hook( ABD_PLUGIN_FILE, 
				array( 'ABD_Setup', 'hooks_helper_activation' ) );

			//	Deactivation
			register_deactivation_hook( ABD_PLUGIN_FILE, 
				array( 'ABD_Setup', 'hooks_helper_deactivation' ) );

			//	Uninstall
			register_uninstall_hook( ABD_PLUGIN_FILE,
				array( 'ABD_Setup', 'hooks_helper_uninstall' ) );

			//	New Multisite Blog Created
			//add_action('wpmu_new_blog', 
			//	array( 'ABD_Setup', 'hooks_helper_new_multisite_blog' );
		}
			public static function hooks_helper_activation() {
				global $wpdb;

				//	We're going to be doing some database manipulation, include
				//	the database class
				require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );


				//	Will contain SQL queries for any tables we need to create
				//	ARRAY_N: each value = SQL query
				$tables_to_create = array();

				//	Will contain SQL queries to add default values to the table
				//	ARRAY_N: each value = SQL query
				$shortcodes_to_add = array();

				//	Will contain options to save in the database
				//	ARRAY_A: 'option_name'=>'option_value'
				$settings_to_add = array();



				//	All right, now we need to create some default shortcodes if
				//	we haven't before. If we have, then there should be an 
				//	option set.
				if ( get_option( 'abd_defaults_input' ) != true ) {
					$shortcodes_to_add[] = array(
						'name' => 'Sample Shortcode',
						'noadblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 300px; height: 250px"><p><b>Ad Blocking Detector</b></p><p>Right now, the plugin is not detecting any ad blocking browser plugin/extension/add-on.</p></div>',
						'adblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 300px; height: 250px"><p><b>Ad Blocking Detector</b></p><p>CAUGHT! The plugin has detected an ad blocking browser plugin/extension/add-on.</p></div>',
						
						// Must set network_wide explicitly so that it isn't 
						// auto-determined when we insert the shortcode (see 
						// ABD_Database::insert_shortcode in db-manip.php).
						'network_wide' => true 
					);

					//	Now we insert the sample shortcodes
					foreach ( $shortcodes_to_add as $data ) {
						ABD_Database::insert_shortcode( $data );
					}

					//	And we update the option in the database to reflect new
					//	defaults
					update_option( 'abd_defaults_input', true );
				}	


				//	And lastly, if we need to put in any default settings, do
				//	so now.  Here is the format:
				//	$settings_to_add['setting_name'] = setting_value;
				


				//	And now loop through and input them
				foreach ( $settings_to_add as $name=>$value ) {
					//	Let's use add_option so that we don't clobber any 
					//	settings already in the database. Also, if this is 
					//	network-wide in a multisite, we need to put it in the
					//	main network database.  Otherwise, it can just go in the
					//	normal one.
					if ( is_network_admin() ) {
						add_site_option( $name, $value );
					}
					else {
						add_option( $name, $value );
					}
				}
			}
			public static function hooks_helper_deactivation() {
				
			}
			public static function hooks_helper_uninstall() {
				self::nuke_plugin();
			}
				protected static function nuke_plugin() {
					global $wpdb;

					//	Drop the table
					$sql = "DROP TABLE IF EXISTS " . ABD_Database::get_table_name() . ";";
					$wpdb->query( $sql );

					//	Clear all the options
					delete_option( 'abd_defaults_input' );
					delete_site_option( 'abd_defaults_input' );

					delete_option( 'abd_cur_db_version' );
					delete_site_option( 'abd_cur_db_version' );

					delete_option( 'abd_current_version' );
					delete_site_option( 'abd_current_version' );
				}
			public static function hooks_helper_new_multisite_blog( $blog_id ) {
				//	$blog_id is passed automatically for wpmu_new_blog
				//	it contains the new blogs/sites id.
			}


		/**
		 * Registers and defines all WordPress admin menus.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */				
		protected static function menus() {
			add_action( 'admin_menu', 
				array( 'ABD_Setup', 'menus_helper' ) );
			
			//	Network wide menu for multisite if plugin is active network wide
			//	First, we need to make sure the checking function exists, and, if
			//	not, include its file.
			if ( !function_exists( 'is_plugin_active_for_network' ) ) {
				include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			//	Now we can try adding the menu
			if ( function_exists( 'is_plugin_active_for_network' ) &&
				is_plugin_active_for_network( ABD_SUBDIR_AND_FILE ) ) {
				add_action( 'network_admin_menu', 
					array( 'ABD_Setup', 'menus_helper' ) );
			}
		}
			public static function menus_helper() {
				//	We need the ABD_Admin_Views class
				require_once (ABD_ROOT_PATH . 'views/admin-views.php');

				add_menu_page( 
					'ABD Dashboard',	//	Title tag value
					'Ad Blocking',	//	Menu Text
					'administrator',	//	Required privileges/capability
					'adblock-detector',	//	Menu Slug
					array( 'ABD_Admin_Views', 'output_main' ), // Content Function
					'dashicons-forms'	//	Menu Icon (http://goo.gl/vN3FjZ)
				);
			}

		/**
		 * Registers and defines the WordPress shortcodes.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		public static function shortcodes() {
			//	Old shortcode for backwards compatibility
			add_shortcode( 'adblockdetector', 
				array( 'ABD_Setup', 'shortcodes_helper' ) );

			//	New shortcode (complies with plugin name blocking, not block)
			add_shortcode( 'adblockingdetector',
				array( 'ABD_Setup', 'shortcodes_helper' ) );
		}
			public static function shortcodes_helper( $atts ) {
				extract( shortcode_atts( array( 
					'id' => null
				 ), $atts ) );

				return ABD_Public_Views::get_shortcode_output( $id );
			}


		/**
		 * Adds links under entry in plugins listing.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_filter call: http://goo.gl/pZnUYV
		 */
		public static function plugin_list_links( ) {
			$plugin_file = ABD_SUBDIR_AND_FILE;

			add_filter( "plugin_action_links_{$plugin_file}", 
				array( 'ABD_Setup', 'plugin_list_links_helper' ) );

			add_filter( "network_admin_plugin_action_links_{$plugin_file}", 
				array( 'ABD_Setup', 'plugin_list_links_helper' ) );
		}
			public static function plugin_list_links_helper( $old_links ) {
				$new_links = array(
					//	Settings
					'<a href="' . admin_url( 'options-general.php?page=adblock-detector' ) .'">Settings</a>'
				);

				return array_merge( $new_links, $old_links );
			}


		/**
		 * Checks to see if plugin has been updated and runs any necessary
		 * upgrade code.
		 */
		public static function upgrade() {
			//	Does the stored plugin version equal the current version?
			//	If so, then we shouldn't need to do anything.
			//	If not, then we have to run through any upgrade processes.			
			if ( get_site_option( 'abd_current_version') != self::$version ) {
				//	We're going to be using the dbDelta function, but we need
				//	the appropriate file included to do so.
				require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
				//	We're going to be doing some database manipulation, include
				//	the database class
				require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );



				//	Make sure the database configuration matches the needed
				//	configuration by using dbDelta().
				$tables_to_create[] = "CREATE TABLE `" . ABD_Database::get_table_name() . "` (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					name text NOT NULL,
					noadblock text NOT NULL,
					adblock text NOT NULL,
					network_wide boolean NOT NULL DEFAULT 0,
					blog_id mediumint(9) DEFAULT 1,
					noadblock_count bigint(20) DEFAULT 0,
					adblock_count bigint(20) DEFAULT 0,
					noadblock_wpautop boolean NOT NULL DEFAULT 1,
					adblock_wpautop boolean NOT NULL DEFAULT 1,
					PRIMARY KEY (id)
				);";
				
				//	Okay, now we create the tables
				foreach ( $tables_to_create as $sql ) {
					dbDelta( $sql );
				}

				//	And we update the option in the database to reflect new
				//	db version
				update_site_option( 'abd_current_version', self::$version );
			}
		}

		/**
		 * This function runs all the setup functions as needed. Call this
		 * function in the main plugin file: ABD_Setup::initialize() 
		 */
		public static function initialize() {
			//	Upgrade function runs every time plugin loads. It determines
			//	what, if anything needs to be done.
			self::upgrade();

			self::menus();
			self::hooks();
			self::enqueue();
			self::shortcodes();	
			self::plugin_list_links();		
		}
	}	//	end class
}	//	end if( !class_exists( ...