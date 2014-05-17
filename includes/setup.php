<?php
/**
 * This file contains a static-esque class with all necessary setup, enqueue,
 * register, and other related WordPress items.
 */

if ( !class_exists( 'ABD_Setup' ) ) {
	class ABD_Setup {
		protected static $db_version = '052014';

		/**
		 * Registers and enqueues all CSS and JavaScript.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function enqueue() {
			//	Enqueue admin CSS
			add_action( 'admin_enqueue_scripts', 
				array(&$this, 'enqueue_helper_admin_css') );

			//	Enqueue admin JS
			add_action( 'admin_enqueue_scripts', 
				array( &$this, 'enqueue_helper_admin_js' ) );

			//	Enqueue public facing JS
			add_action( 'wp_enqueue_scripts',
				array( &$this, 'enqueue_helper_public_js' ) );
		}
			protected static function enqueue_helper_admin_css() {
				wp_register_style( 'abd-admin-css', 
					ABD_ROOT_URL . 'assets/css/admin.css' );

				wp_enqueue_style( 'abd-admin-css' );
			}
			protected static function enqueue_helper_admin_js() {
				// @todo put in admin js
			}
			protected static function enqueue_helper_public_js() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'abd-adblock-detector', 
					ABD_ROOT_URL . 'assets/js/adblock-detector.js' );
				wp_enqueue_script( 'abd-fake-ad', 
					ABD_ROOT_URL . 'assets/js/advertisement.js' );
			}

		/**
		 * Registers and defines all WordPress hooks. (e.g. activation / 
		 * deactivation)
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function hooks() {
			register_activation_hook( ABD_PLUGIN_FILE, 
				array( &$this, 'hooks_helper_activation' ) );

			register_deactivation_hook( ABD_PLUGIN_FILE, 
				array( &$this, 'hooks_helper_deactivation' ) );

			register_uninstall_hook( ABD_PLUGIN_FILE,
				array( &$this, 'hooks_helper_uninstall' ) );
		}
			protected static function hooks_helper_activation() {
				global $wpdb;

				//	We're going to be using the dbDelta function, but we need
				//	the appropriate file included to do so.
				require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
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



				//	If $cur_db_version equals the self::$db_version value,
				//	then we shouldn't need to create tables. If not, then we need
				//	to create the database tables and add default settings
				if ( get_option( 'abd_cur_db_version') != self::$db_version ) {
					$tables_to_create[] = "CREATE TABLE " . ABD_Db_Manip::get_table_name() . " (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						name text NOT NULL,
						noadblock text NOT NULL,
						adblock text NOT NULL,
						network_wide boolean NOT NULL DEFAULT 0,
						blog_id mediumint(9) DEFAULT NULL,
						noadblock_count bigint(20) DEFAULT 0,
						adblock_count bigint(20) DEFAULT 0,
						PRIMARY KEY (id)
					);";

					//	Okay, now we create the tables
					foreach ( $tables_to_create as $sql ) {
						dbDelta( $sql );
					}

					//	And we update the option in the database to reflect new
					//	db version
					update_option( 'abd_cur_db_version', self::$db_version );
				}


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
						'network_wide' => false 
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
			protected static function hooks_helper_deactivation() {
				
			}
			protected static function hooks_helper_uninstall() {
				nuke_plugin();
			}
				protected static function nuke_plugin() {
					//	Drop the table
					$sql = "DROP TABLE IF EXISTS " . ABD_Database::get_table_name() . ";";
					$wpdb->query( $sql );

					//	Clear all the options
					delete_option( 'abd_defaults_input' );
					delete_site_option( 'abd_defaults_input' );
				}


		/**
		 * Registers and defines all WordPress admin menus.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */				
		protected static function menus() {
			add_action( 'admin_menu', array( &$this, 'menus_helper' ) );
		}
			protected static function menus_helper() {
				add_menu_page( 
					'Ad Blocking Detector',	//	Title tag value
					'Ad Blocking Detector',	//	Menu Text
					'administrator',	//	Required privileges/capability
					'adblock-detector',	//	Menu Slug
					array( 'ABD_Admin_Views', 'initialize' ), // Content Function
					'dashicons-forms'	//	Menu Icon (http://goo.gl/vN3FjZ)
				);
			}

		/**
		 * Registers and defines the WordPress shortcodes.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function shortcodes() {
			//	Old shortcode for backwards compatibility
			add_shortcode( 'adblockdetector', 
				array( &$this, 'shortcodes_helper' ) );

			//	New shortcode (complies with plugin name blocking, not block)
			add_shortcode( 'adblockingdetector',
				array( &$this, 'shortcodes_helper' ) );
		}
			protected static function shortcodes_helper( $atts ) {
				extract( 
					shortcode_atts( 
						array( 'id'=>null ) 
					),
					$atts
				);

				//	Get the database entry for that shortcode
				require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );
				$res = ABD_Database::get_shortcode_by_id( $id );

				//	Was it successful?
				if ( $res ) {
					//	Good!
					//	Process any shortcodes used within the ABD shortcode
					$noab = do_shortcode( $res['noadblock'] );
					$ab = do_shortcode( $res['adblock'] );

					//	And now create the return value;
					$retval = '<div class="ABD_display ABD_display_noadblock">' . $noab . '</div>';
					$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';
				}
				else {
					// Uh-Oh. This means the query failed or, more likely,
					// their is no shortcode with that ID in the database.
					// Let's return a generic error message.
					$retval = '<div class="ABD_error"><b>Ad Blocking Detector Error</b><br /><em>Could not find a shortcode with specified ID#. Please check your configuration!</em></div>';
				}

				return $retval;
			}

		/**
		 * This function runs all the setup functions as needed. Call this
		 * function in the main plugin file: ABD_Setup::initialize() 
		 */
		public static function initialize() {
			self::menus();
			self::hooks();
			self::enqueue();
			self::shortcodes();
		}
	}	//	end class
}	//	end if( !class_exists( ...