<?php
/**
 * This file contains a static-esque class with all necessary setup, enqueue,
 * register, and other related WordPress items.
 */

require_once( ABD_ROOT_PATH . 'includes/log.php' );
require_once( ABD_ROOT_PATH . 'includes/wpsm/settings-manager.php' );
require_once( ABD_ROOT_PATH . 'includes/anti-adblock.php' );
require_once( ABD_ROOT_PATH . 'views/admin-views.php' );
require_once( ABD_ROOT_PATH . 'views/public-views.php' );
require_once( ABD_ROOT_PATH . "includes/widget.php" );
require_once( ABD_ROOT_PATH . "includes/click-handler.php" );

if ( !class_exists( 'ABD_Setup' ) ) {
	class ABD_Setup {
		/**
		 * Registers and enqueues all CSS and JavaScript. 
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

			//	Add fake ads to footer
			add_action( 'wp_footer',
				array( 'ABD_Setup', 'enqueue_helper_footer' ), 501 );
			add_action( 'admin_footer',
				array( 'ABD_Setup', 'enqueue_helper_footer' ), 501 );

			//	Add admin notices as appropriate
			self::enqueue_helper_admin_notices();
		}
			public static function enqueue_helper_admin_css() {
				//	Our anti-adblock plugin may serve as a backup to prevent ad blockers
				//	from blocking our assets. If it exists, use those files. Otherwise,
				//	use ours.
				if( defined( 'ABDBLC_ROOT_URL' ) ) {
					//	Then our plugin is loaded because it defines this constant.
					$prefix = ABDBLC_ROOT_URL;
				}
				else {
					$prefix = ABD_ROOT_URL;
				}

				wp_register_style( 'abd-admin-css',
					$prefix . 'assets/css/admin.css' );
				wp_enqueue_style( 'abd-admin-css' );


				wp_register_style( 'abd-admin-codemirror-css',
					$prefix . 'assets/js/codemirror/lib/codemirror.css' );
				wp_enqueue_style( 'abd-admin-codemirror-css' );


				//	jQuery UI theme.
				wp_enqueue_style('abd-admin-jquery-ui-css',
	               	$prefix . 'assets/css/jquery/smoothness-theme/jquery-ui.min.css',
	               	false
	            );
			}
			public static function enqueue_helper_admin_js() {
				//	Our anti-adblock plugin may serve as a backup to prevent ad blockers
				//	from blocking our assets. If it exists, use those files. Otherwise,
				//	use ours.
				if( defined( 'ABDBLC_ROOT_URL' ) ) {
					//	Then our plugin is loaded because it defines this constant.
					$prefix = ABDBLC_ROOT_URL;
				}
				else {
					$prefix = ABD_ROOT_URL;
				}

				//	Now do the enqueueing
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-accordion' );
				wp_enqueue_script( 'tiny_mce' );
				

				// Ad Blocking Detector
				wp_enqueue_script( 'abd-adblock-detector',
					$prefix . 'assets/js/adblock-detector.min.js', array('jquery') );
				wp_enqueue_script( 'abd-fake-ad',
					$prefix . 'assets/js/advertisement.min.js' );
				

				//	CodeMirror
				wp_enqueue_script( 'abd-codemirror',
					$prefix . 'assets/js/codemirror/lib/codemirror.js', array('jquery') );
				wp_enqueue_script( 'abd-codemirror-mode-css',
					$prefix . 'assets/js/codemirror/mode/css/css.js',
					'abd-codemirror' );
				wp_enqueue_script( 'abd-codemirror-mode-xml',
					$prefix . 'assets/js/codemirror/mode/xml/xml.js',
					'abd-codemirror' );
				wp_enqueue_script( 'abd-codemirror-mode-javascript',
					$prefix . 'assets/js/codemirror/mode/javascript/javascript.js',
					'abd-codemirror' );
				wp_enqueue_script( 'abd-codemirror-mode-htmlmixed',
					$prefix . 'assets/js/codemirror/mode/htmlmixed/htmlmixed.js',
					'abd-codemirror' );


				//	Masonry
				wp_enqueue_script( 'abd-masonry',
					$prefix . 'assets/js/jquery.masonry.min.js',
					'jquery' );

				wp_enqueue_script( 'abd-admin-view',
					$prefix . 'assets/js/admin-view.js', array('jquery') );
				wp_localize_script( 'abd-admin-view', 'objectL10n', ABD_Admin_Views::get_js_localization_array() );
			}
			public static function enqueue_helper_public_js( $prefix = ABD_ROOT_URL ) {
				//	Our anti-adblock plugin may serve as a backup to prevent ad blockers
				//	from blocking our assets. If it exists, use those files. Otherwise,
				//	use ours.
				if( defined( 'ABDBLC_ROOT_URL' ) ) {
					//	Then our plugin is loaded because it defines this constant.
					$prefix = ABDBLC_ROOT_URL;
				}
				else {
					$prefix = ABD_ROOT_URL;
				}

				//	Now do the enqueueing
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'abd-adblock-detector',
					$prefix . 'assets/js/adblock-detector.min.js', array('jquery') );
				wp_enqueue_script( 'abd-fake-ad',
					$prefix . 'assets/js/advertisement.min.js' );
			}
			public static function enqueue_helper_footer() {
				$abd_settings = ABD_Database::get_settings( true );

				//	We need a fake iframe URL.  Ideally, this is to some completely random
				//	location on a site that doesn't exist.  However, SSL users will experience
				//	content warnings on their site if we do that.  For them, it would be best if
				//	we used their own URL and thus their own SSL certificate.  However, using
				//	a nonexistent page on their own site leads to problems if 404 redirection
				//	occurs because redirects can frame bust.  So, again, it would be better to
				//	point at some other site that doesn't exist.
				//
				//	Long story short, optimally, we would use a nonexistent URL to a nonexistent
				//	site. For SSL users, however, we NEED to stay on their domain.
				if( is_ssl() ) {
					$iframe_url = get_site_url(null, 'abd/adserver/adlogger_tracker.php');
					$sec = 'security=\"restricted\" sandbox=\"\"';
				}
				else {
					$iframe_url = "http://YHrSUDwvRGxPpWyM-ad.us/adserver/adlogger_tracker.php";
					$sec = '';
				}

				if( !empty( $abd_settings['iframe_url'] ) ) {
					$iframe_url = $abd_settings['iframe_url'];					
				}



				//	Okay, our global user-defined wrapper css selectors may be empty... however, 
				//	it will come back as an JSON encoded array with one empty string... [""]
				//	We don't want this... this should mean utterly blank
				if( $abd_settings['user_defined_selectors'] == '[""]' ) {
					$abd_settings['user_defined_selectors'] = '';
				}
				
				?>
				<script type="text/javascript">
					<?php
					if( $abd_settings['enable_iframe'] == 'yes' || $abd_settings['enable_iframe'] == '' ) {
						?>
						(function() {
							//	Insert iframe only if we can prevent it from frame busting simply.
							//	We prevent frame busting using either the security="restricted" or sandbox""
							//	attributes in the iframe tag.  So, check if we can do that!
							var frm = document.createElement('iframe');
							if( 'sandbox' in frm || 'security' in frm ) {
								//	Okay, we can use the iframe... Here's the HTML we want:
								//	<div
								//		id='abd-ad-iframe-wrapper'
								//		style="position: fixed !important; bottom: -999em !important; left: -999em !important; width: 0 !important; height: 0 !important; overflow: hidden !important;">
								//
								//		<iframe id="abd-ad-iframe" src="<?php echo $iframe_url; ?>" security="restricted" sandbox="" style="height: 728px; width: 90px;"></iframe>
								//	</div>
								//
								//	So, output it using document.write()
								document.write("<div id='abd-ad-iframe-wrapper' style=\"position: fixed !important; bottom: -999em !important; left: -999em !important; width: 0 !important; height: 0 !important; overflow: hidden !important;\"><iframe id=\"abd-ad-iframe\" src=\"<?php echo $iframe_url; ?>\" <?php echo $sec; ?> style=\"height: 728px; width: 90px;\"><\/iframe><\/div>");
							}
						})();
						<?php
					}
					?>

					var ABDSettings = {
						cssSelectors: '<?php echo $abd_settings['user_defined_selectors']; ?>',
						enableIframe: "<?php echo $abd_settings['enable_iframe']; ?>",
						enableDiv:    "<?php echo $abd_settings['enable_div']; ?>",
						enableJsFile: "<?php echo $abd_settings['enable_js_file']; ?>"
					}

					//	Make sure ABDSettings.cssSelectors is an array... might be a string
					if(typeof ABDSettings.cssSelectors == 'string') {
						ABDSettings.cssSelectors = [ABDSettings.cssSelectors];
					}
				</script>

				<?php
				if( $abd_settings['enable_div'] == 'yes' || $abd_settings['enable_div'] == '' ) {
					?>
					<div
						id="abd-ad-div"
						style="position: fixed !important; bottom: -999em !important; left: -999em !important; display: block !important; visibility: visible !important; width: 0 !important; height: 0 !important;">

						Advertisment ad adsense adlogger
					</div>
					<?php
				}
			}
			public static function enqueue_helper_admin_notices( $force = false, $update_delay = true ) {
				/////////////////////////
				/// Feedback Request ///
				///////////////////////

				//	We don't want to spam the user with feedback requests.
				//	Therfore, we should setup a delay before asking.
				//	I'm thinking 1 week initially, then again every year.
				//	The timeout is stored in the database.
				$current_time = strtotime( 'now' );
				$stored_time = get_site_option( 'abd_feedback_nag_time' );

				//	Did we get anything from the database?  If not, we'll need
				//	to set a default delay later, so set a flag.
				if( $stored_time ) {
					$set_default = false;
				}
				else {
					$set_default = true;
				}

				//	Are we forcing the update?  If so, let's just set the stored
				//	time to a low number so it triggers the nag
				if( $force ) {
					$stored_time = 5;
				}

				//	Do we need to set the initial default nag date?
				if( $set_default ) {
					//	No nag date in the database. Add one for 1 week from today then return.
					if( $update_delay ) {
						update_site_option( 'abd_feedback_nag_time',
							strtotime( '+1 week' ) );
					}
					return;
				}

				//	Okay, check if we need to nag now.
				if( $current_time > $stored_time ) {
					//	It is past the stored nag date, so nag damnit.
					// add_action( 'admin_notices',
					// 	array( 'ABD_Admin_Views', 'rate_plugin_nag' ) );

					//	Now delay it a year.
					if( $update_delay ) {
						update_site_option( 'abd_feedback_nag_time',
							strtotime( '+1 year' ) );
					}
				}



				///////////////////
				/// Update News ///
				//////////////////

				//	We only want to show this once, and only for a short period
				//	of time from the update.
				$end_date_for_update_news = strtotime( '31 October 2014' );

				if( $current_time < $end_date_for_update_news ) {
					//	It's within the window to show the notice.
					//	Have we already showed it?
					$update_notice_date = get_site_option( 'abd_update_news_showed' );
					if( !$update_notice_date ) {
						//	No, we haven't already shown it.  So, show it!
						add_action( 'admin_notices',
							array( 'ABD_Admin_Views', 'plugin_update_news' ) );

						update_site_option( 'abd_update_news_showed', $current_time );
					}
				}
				else {
					//	It's not within the window. Let's make sure we remove
					//	all update notice traces so we don't interfere with future
					//	ones.
					delete_site_option( 'abd_update_news_showed' );
				}
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

			//	Widgets
			add_action( 'widgets_init',
				array( 'ABD_Setup', 'hooks_helper_widget' ) );


			//	Register WPSM Settings
			add_action( 'admin_init', 
				array( 'ABD_Admin_Views', 'wpsm_settings' ) );


			//	Button Click and Form Handlers
			add_action( 'admin_post_create_bcc_plugin', 
				array( 'ABD_Click_Handler', 'create_bcc_plugin' ) );
			add_action( 'admin_post_reset_bcc_plugin_name', 
				array( 'ABD_Click_Handler', 'reset_bcc_plugin_name' ) );
			add_action( 'admin_post_delete_bcc_plugin', 
				array( 'ABD_Click_Handler', 'delete_bcc_plugin' ) );
			add_action( 'admin_post_delete_manual_bcc_plugin',
				array( 'ABD_Click_Handler', 'delete_manual_bcc_plugin' ) );
			add_action( 'admin_post_clear_log',
				array( 'ABD_Click_Handler', 'clear_log' ) );
			add_action( 'admin_post_delete_shortcode',
				array( 'ABD_Click_Handler', 'delete_shortcode' ) );

			//	Admin notices
			add_action( 'admin_notices', 
				array( 'ABD_Admin_Views', 'add_action_notices' ) );

			
		}
			public static function hooks_helper_activation() {
				ABD_Log::info( 'Plugin activation.' );

				//	Try to create the fallback anti-adblock plugin
				ABD_Anti_Adblock::create_bcc_plugin();
			}
			public static function hooks_helper_deactivation() {
				ABD_Log::info( 'Plugin deactivation.' );
			}
			public static function hooks_helper_uninstall() {
				self::nuke_plugin();
			}
				protected static function nuke_plugin() {
					ABD_Database::nuke_all_options();
					ABD_Database::drop_v2_table();	//	Compatibility for upgraded v2 installs

					ABD_Anti_Adblock::delete_bcc_plugin();
					ABD_Anti_Adblock::delete_bcc_manual_plugin();
				}
			public static function hooks_helper_new_multisite_blog( $blog_id ) {
				//	$blog_id is passed automatically for wpmu_new_blog
				//	it contains the new blogs/sites id.
			}

			public static function hooks_helper_widget() {
				register_widget( 'ABD_Widget' );
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
				
				// add_action( 'network_admin_menu',
				// 	array( 'ABD_Setup', 'menus_helper' ) );
			}
		}
			public static function menus_helper() {
				//	We need the ABD_Admin_Views class
				require_once (ABD_ROOT_PATH . 'views/admin-views.php');

				add_menu_page(
					'ABD Dashboard',	//	Title tag value
					'Ad Blocking',	//	Menu Text
					'administrator',	//	Required privileges/capability
					'ad-blocking-detector',	//	Menu Slug
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
					'id' => null,
					'adblock' => '',
					'noadblock' => ''
				 ), $atts ) );

				return ABD_Public_Views::get_shortcode_output( $id, $noadblock, $adblock );
			}


		/**
		 * Adds links under entry in plugins listing.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_filter call: http://goo.gl/pZnUYV
		 */
		public static function plugin_list_links( ) {
			$plugin_file = ABD_SUBDIR_AND_FILE;

			//	Individual Site Plugins Page
			add_filter( "plugin_action_links_{$plugin_file}",
				array( 'ABD_Setup', 'plugin_list_links_helper' ) );

			//	Network Admin Plugins Page
			add_filter( "network_admin_plugin_action_links_{$plugin_file}",
				array( 'ABD_Setup', 'plugin_list_links_helper' ) );
		}
			public static function plugin_list_links_helper( $old_links ) {
				$new_links = array(
					//	Settings
					'<a href="' . admin_url( 'admin.php?page=ad-blocking-detector' ) .'">Settings</a>'
				);

				return array_merge( $new_links, $old_links );
			}


		/**
		 * Checks to see if plugin has been updated and runs any necessary
		 * upgrade code.
		 */
		public static function upgrade() {
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
			 * 
			 * Okay, here's the deal.  WordPress doesn't have a built-in method for detecting
			 * plugin updates, like it does activation for example.  Even if it did, that 
			 * would be problematic because it wouldn't take into account file overwrites
			 * that can effectively upgrade the plugin (e.g. FTP plugin updates)
			 *
			 * So, upgrade detection is entirely ours to manage.  The first tool used 
			 * to manage this is a constant, named ABD_VERSION, defined at the 
			 * top of the file which contains the current plugin version number (e.g. "3.0.0")
			 * as a string.  This constant MUST BE UPDATED EVERY TIME THE VERSION CHANGES or it
			 * will break this homebrew upgrade detection, as well as other parts of the plugin
			 * that depend on it.
			 *
			 * The second tool is a WordPress option, entitled 'abd_current_version' which,
			 * is updated to match the ABD_VERSION constant.  
			 * 
			 * This function is responsible for keeping this option up to date.  However, here's
			 * the cool part.  Before this function updates
			 * the database option with the new ABD_VERSION number, there will be a discrepancy 
			 * between the option and ABD_VERSION.  The option will contain the last ABD_VERSION
			 * written to the database, which is the version number of the last installed
			 * plugin version.  So, before we update the database option, we can check
			 * for this discrepancy, and, if it exists, exploit it.
			 *
			 * Based on the version number, we can take specific actions in going from one
			 * version to another, or just some generic action that occurs in every update.
			 * 
			 *
			 * ********************************NOTE**************************************** 
			 * Prior to version 3.0.0, the abd_current_version option and
			 * ABD_VERSION constant were not synchronized with the plugin version.  Instead,
			 * they represented a sort of database version, and were only changed when the 
			 * plugin needed to do something to the database.
			 *
			 * None of the versions less than 2.0.0 used this at all.
			 *
			 * As such, do not rely on precise version numbering prior to version 3.0.0.  
			 * In version 3.0.0+, these should remain in sync with the plugin version. Before
			 * that, the best you can hope for is major version detection by checking the
			 * first number.  e.g. the following pseudo-ish-code:
			 * $old_version = get_site_option( 'abd_current_version' );
			 * $major_old_version = $old_version[0];	//	First character in version string
			 * if( $major_old_version == 3 ) {
			 * 		//	Version 3
			 * 		//	Specific version comparisons are okay here
			 * }
			 * else if ( $major_old_version == 2 ) {
			 * 		//	Version 2
			 * 		//	No specific version comparisons should be relied on... v2 branch is all we know
			 * }
			 * else {
			 * 		//	Something besides Versions 2 or 3... if plugin is still in version
			 * 		//	3 branch when you're reading this, then the only possibility is Version
			 * 		//	1.  If it's in Version 4+, then you'll need another conditional. 
			 * 		//	Update the comments FFS!
			 * }
			 * ********************************END NOTE************************************
			 */

			/**
			 * An associative array where the key is a plugin version, and the value is
			 * a function, passed to admin_notices WordPress action that outputs the content
			 * of an upgrade message for that version.  This will be checked later on,
			 * and if we are upgrading, and there is a mapped function, it will be tied
			 * to an admin_notices action.
			 */
			$notification_map = array(
				'3.0.0'  => array( 'ABD_Admin_Views', 'v2_to_v3_migration_notice' )
			);	//	Maps a version number to a function to call with an upgrade notice.			
			

			//	Does the stored plugin version equal the current version?
			//	If so, then we shouldn't need to do anything.
			//	If not, then we have to run through any upgrade processes.
			$upgrading_version = get_site_option( 'abd_current_version' );
			if( !$upgrading_version ) {
				ABD_Log::info( 'Checking whether plugin upgrade is needed. No version information stored in database. Assuming upgrade from version 2.2.8 (last stable release of v2 branch) required.' );
				$upgrading_version = '2.2.8';
			}

			$upgrading_major_version = $upgrading_version[0];
			$new_version = ABD_VERSION;
			$new_major_version = $new_version[0];

			if (  $upgrading_version != $new_version ) {
				ABD_Log::info( 'Running plugin update. Old version: ' . $upgrading_version . ', new version: ' . $new_version );
				///////////////////////////////
				//	Do our updating here!!!	///
				///////////////////////////////

				///////////////////////////////
				//	MAJOR VERSION JUMPS 	//
				//////////////////////////////
				if( $upgrading_major_version != $new_major_version ) {
					ABD_Log::info( 'Detecting major version jump from v' . $upgrading_major_version	. ' branch to v' . $new_major_version . ' branch.' );
					
					
					//////////////////////////////
					//	VERSION 2 -> VERSION 3	//
					//////////////////////////////
					//	If we're updating from version 2, run the database upgrade function
					if( $upgrading_major_version == 2 ) {
						ABD_Database::v2_to_v3_database_transfer();
					}
					
					//////////////////////////////
					//	VERSION 1 -> VERSION 3	//
					//////////////////////////////
					else if( $upgrading_major_version < 2 ) {
						//	Damn... that's old! I don't plan on supporting that version.
						//	Show special notice about how terrible it is that version 2 was
						//	skipped and how nothing was transferred... they're starting
						//	with a clean slate.
						add_action( 'network_admin_notices', 
							array( 'ABD_Admin_Views', 'v1_to_v3_migration_notice' ) );
						add_action( 'admin_notices',
							array( 'ABD_Admin_Views', 'v1_to_v3_migration_notice' ) );
					}
				}

				//////////////////////////
				//	ALL VERSION JUMPS	//
				//////////////////////////
				//	Update the Block List Countermeasure Plugin if automatic
				$blcp_status = ABD_Anti_Adblock::bcc_plugin_status();
				if( !$blcp_status['manual_plugin_exists'] ) {
					//	Auto upgrade it
					ABD_Log::info( 'Attempting upgrade of automatic Block List Countermeasure plugin.' );
					ABD_Anti_Adblock::create_bcc_plugin();
				}
				else {
					ABD_Log::info( 'Manual Block List Countermeasure plugin update needed!' );

					//	Notify user
					add_action( 'admin_notices',
						array( 'ABD_Admin_Views', 'update_manual_blcp_notice' ) );

					//	Deactivate manual plugin
					if( defined( ABDBLC_SUBDIR_AND_FILE ) ) {	//	Block List Countermeasure plugin is activated
						deactivate_plugins( ABDBLC_SUBDIR_AND_FILE );
					}
				}

				
				//	And we update the option in the database to reflect that the upgrade was processed
				update_site_option( 'abd_current_version', ABD_VERSION );
			}	//	end if (  $upgrading_version != $new_version ) {


			//////////////////////////////////////
			//	UPGRADE NOTIFICATION MANAGEMENT	//
			//////////////////////////////////////
			/**
			 * Okay, here's what sucks. For non-multisite setups, this would be easy.  Just
			 * flash the message after an update.  The problem is, that in multisites, only
			 * the person updating the plugins would see a notification that we just tossed
			 * into the admin_notices action.  We want every site administrator, AND the 
			 * plugin updater to see our notices.  So, we need to get sneaky.  
			 *
			 * We're going to create an option (not a network wide option) that stores the
			 * version number that last showed a notification update.  If we have a notification
			 * in our $notification_map for this version, and this version does not match the
			 * verison in the option, then we need to display the message.
			 *
			 * After display our message, we will update the option with this version.  This
			 * means every site acts independently, and it will not splash up only once immediately
			 * after the plugin is updated for whoever happens to see it.  It will show on every
			 * site the first time somebody goes to that site's dashboard after the update.
			 */
			$last_notice_version = get_option( 'abd_last_upgrade_notice_seen', '0.0.0' );

			if( array_key_exists( ABD_VERSION, $notification_map ) && 
				version_compare( $last_notice_version, ABD_VERSION ) != 0 ) {	
				//	Version mismatch and notification message available for this version
				//	Display the notifications
				ABD_Log::info( 'Displaying upgrade notice for plugin version ' . ABD_VERSION . '.' );
				if( ABD_Multisite::is_this_a_multisite() && ABD_Multisite::is_in_network_admin() ) {	//	Multisite
					add_action( 'network_admin_notices', $notification_map[ABD_VERSION] );
				}
				add_action( 'admin_notices', $notification_map[ABD_VERSION] );

				//	Update our option
				update_option( 'abd_last_upgrade_notice_seen', ABD_VERSION );
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
