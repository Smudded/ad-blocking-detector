<?php
/**
 * This file contains any and all output for the admin settings page for this
 * plugin.
 */

require_once ( ABD_ROOT_PATH . 'includes/multisite.php' );
require_once ( ABD_ROOT_PATH . 'includes/database.php' );
require_once ( ABD_ROOT_PATH . 'includes/localization.php' );


if ( !class_exists( 'ABD_Admin_Views' ) ) {
	class ABD_Admin_Views {
		/**
		 * This function outputs all content for the main plugin page in the
		 * correct order.
		 */
		
		protected static $our_message_array;

		protected static $our_links = array(
			/////		Plugin Website Articles
			//		Collect JavaScript Console Output
			'console'             => 'http://adblockingdetector.johnmorris.me/how-to-collect-javascript-console-output/',
			//		How This Plugin Works
			'howitworks'          => 'http://adblockingdetector.johnmorris.me/how-this-plugin-works/',
			//		Combining CSS and Ad Blocking Detector
			'csstargeting'        => 'http://adblockingdetector.johnmorris.me/combine-css-and-ad-blocking-detector/',
			//		Combining JS and Ad Blocking Detector
			'jstargeting'         => 'http://adblockingdetector.johnmorris.me/combine-javascript-and-ad-blocking-detector/',
			//		What is the Block List Countermeasure Plugin
			'bccpluginintro'      => 'http://adblockingdetector.johnmorris.me/what-is-the-block-list-countermeasure-plugin/',
			//		Using Ad Blocking Detector for the first time
			'pluginintro'         => 'http://adblockingdetector.johnmorris.me/using-ad-blocking-detector-for-the-first-time/',
			//		Create a simple ad shortcode
			'simpleadintro'       => 'http://adblockingdetector.johnmorris.me/how-to-display-a-simple-ad-using-alternative-content-shortcodes/',
			//		Intro to User Defined Wrapper CSS Selectors
			'userdefinedwrappers' => 'http://adblockingdetector.johnmorris.me/detection-improvement-user-defined-wrapper-css-selectors/',
			//		Intro to Manual Block List Countermeasure Plugin Management
			'manualpluginintro'   => 'http://adblockingdetector.johnmorris.me/manual-block-list-countermeasure-plugin-installation-and-management/',
			//		HTML Creator
			'htmlcreator'         => 'http://www.html.am/html-editors/html-text-editor.cfm',
			//		Demo
			'demo'                => 'http://adblockingdetector.johnmorris.me/demo/',

			/////		WordPress.org Links
			'wporgreviews'        => 'https://wordpress.org/support/view/plugin-reviews/ad-blocking-detector',
			'wporgsupport'        => 'https://wordpress.org/support/plugin/ad-blocking-detector',
			'wporglisting'        => 'https://wordpress.org/plugins/ad-blocking-detector/description/',

			///////		Dev Info
			'emaildev'            => 'mailto:johntylermorris@jtmorris.net?subject=Ad%20Blocking%20Detector%20Support',
			'devemail'            => 'mailto:johntylermorris@jtmorris.net?subject=Ad%20Blocking%20Detector%20Support',
			'devwebsite'          => 'http://cs.johnmorris.me',

			///////		Plugin Website/Repo
			'website'             => 'http://adblockingdetector.johnmorris.me',
			'github'              => 'http://github.com/jtmorris/ad-blocking-detector',

			//////		Miscellaneous
			//		Tweet love of this plugin
			'twitterlove'         => 'http://twitter.com/home?status=I%20love%20the%20Ad%20Blocking%20Detector%20WordPress%20plugin%20by%20%40jt_morris!%20http://wordpress.org/plugins/ad-blocking-detector/'
		);

		public static function add_action_notices() {
			$message_array = array(
				//	Generic messages
				'generic_success' => array( 
					'msg' => ABD_L::__( 'Success!' ), 
					'class' => 'updated' 
				),
				
				'generic_failure' => array( 
					'msg' => ABD_L::__( 'Failed!' ), 
					'class' => 'error' 
				),

				
				//	Create fallback messages
				'create_bcc_success' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure plugin created successfully.' ),
					'class' => 'updated' 
				),
				'create_bcc_failure' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure creation failed.' ),
					'class' => 'error' 
				),

				
				//	Rename fallback messages
				'rename_bcc_success' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure renamed successfully.' ),
					'class' => 'updated' 
				),
				'rename_bcc_failure' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure renaming failed.' ),
					'class' => 'error' 
				),

				
				//	Delete fallback messages
				'delete_bcc_success' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure deleted successfully.' ),
					'class' => 'updated' 
				),
				'delete_bcc_failure' => array( 
					'msg' => ABD_L::__( 'Block List Countermeasure deletion failed.' ),
					'class' => 'error' 
				),


				//	Clear log messages
				'clear_log_success' => array(
					'msg'   => ABD_L::__( 'Session log cleared successfully.' ),
					'class' => 'updated'
				),

				//	Delete shortocde messages
				'delete_shortcode_success' => array(
					'msg'   => ABD_L::__( 'Shortcode deleted successfully.' ),
					'class' => 'updated'
				),
				'delete_shortcode_failure_no_id' => array(
					'msg'   => ABD_L::__( 'Shortcode deletion failed! Invalid shortocde ID given.' ),
					'class' => 'error'
				),
				'delete_shortcode_failure_unknown' => array(
					'msg'   => ABD_L::__( 'Shortcode deletion failed!' ),
					'class' => 'error'
				)
			);

			if( array_key_exists( 'msg-code', $_GET ) && 
				!empty( $_GET['msg-code'] ) &&
				array_key_exists( $_GET['msg-code'], $message_array ) ) {

				//	Message exists and is supposed to be displayed
				$class = $message_array[$_GET['msg-code']]['class'];
				$msg   = $message_array[$_GET['msg-code']]['msg'];

				//	Echo it
				?>
				<div class='<?php echo $class; ?>'>
					<p><?php echo $msg; ?></p>
				</div>
				<?php
			}
		}

		public static function output_main() {
			?>
			<div class='wrap'>
				<?php screen_icon(); ?>

				<h2><?php ABD_L::_e( 'Ad Blocking Detector - Dashboard' ); ?></h2>
				<div id='abdwpsm_content'>
					<?php
					//////////////////////////////
					//  Page content goes here  //
					//////////////////////////////
					?>
					<div id="disable-ad-blocker-enable-js-warning" style="margin: 95px 0 120px 0;">
						<h1 style="color: #B00;"><?php ABD_L::_e( 'Hold on there! Please disable any ad blockers and enable JavaScript.' ); ?></h1>
						<p style="font-size: 1.3em;">
							<?php ABD_L::_e( 'Several popular block lists for ad blockers block crucial parts of this plugin. During configuration and setup, you will circumvent this, but for now, you need to disable your ad blockers and refresh this page. You also need to enable JavaScript if you have it disabled, or if you use a NoScript browser add-on.' ); ?>
						</p>				
					</div>
					
					<?php
					ABDWPSM_Settings_Manager::display_settings_page_content( 'ad-blocking-detector' );
					?>
				</div><!-- end <div id='wpsm_content'> -->	
			</div><!-- end <div class='wrap'> -->
		    <?php
		}

		public static function wpsm_settings() {
			/**
			 * This plugin utilizes the Settings API wrapper I, John Morris, wrote
			 * to make the Settings API less of a piece of garbage, and make it actually
			 * programmatically manipulatable (e.g. looping, contextual settings, contextual
			 * field options, et cetera).  This wrapper's code is located in the /includes/wpsm
			 * directory. Instructions for using the wrapper are available here:
			 * http://wpsm.johnmorris.me
			 *
			 * It may look a bit cluttered at first glance, but the code below is much more
			 * consistent, logical, and concise than a vanilla Settings API implementation would
			 * be.  With a little bit of familiarity, I think you'll agree that this thing is
			 * awesome.
			 */

			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			//	Remove any old messages on each page load
			ABDWPSM_Settings_Manager::add_query_arg_to_strip( 'msg-code' );
			ABDWPSM_Settings_Manager::add_query_arg_to_strip( 'result' );

			//	Tabs
			$time_bt = microtime( true );
			$mem_bt = memory_get_usage( true );
			
			$GS_Tab = new ABDWPSM_Tab( array(
				'display_name'        => ABD_L::__( 'Getting Started' ),
				'display_description' => self::getting_started_tab_header(),
				'url_slug'            => 'getting-started',
				'page'                => 'ad-blocking-detector'
			) );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs - $GS_Tab', $time_bt, $mem_bt, true );
			$time_after_tab = microtime( true );
			$mem_after_tab = memory_get_usage( true );

			$Existing_Shortcodes_Tab = new ABDWPSM_Tab( array(
				'display_name'        => ABD_L::__( 'Manage Shortcodes' ),
				'display_description' => self::manage_shortcodes_tab_header(),
				'url_slug'            => 'manage-shortcodes',
				'page'                => 'ad-blocking-detector'
			) );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs - $ES_Tab', $time_after_tab, $mem_after_tab, true );
			$time_after_tab = microtime( true );
			$mem_after_tab = memory_get_usage( true );

			$New_Shortcode_Tab = new ABDWPSM_Tab( array(
				'display_name'        => ABD_L::__( 'Add New Shortcode' ),
				'display_description' => self::add_new_shortcode_tab_header(),
				'url_slug'            => 'new-shortcodes',
				'page'                => 'ad-blocking-detector'
			) );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs - $NS_Tab', $time_after_tab, $mem_after_tab, true );
			$time_after_tab = microtime( true );
			$mem_after_tab = memory_get_usage( true );

			$Settings_Tab = new ABDWPSM_Tab( array(
				'display_name'        => ABD_L::__( 'Advanced Settings' ),
				'display_description' => self::settings_tab_header(),
				'url_slug'            => 'settings',
				'page'                => 'ad-blocking-detector'
			) );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs - $Settings_Tab', $time_after_tab, $mem_after_tab, true );
			$time_after_tab = microtime( true );
			$mem_after_tab = memory_get_usage( true );

			$Debug_Tab = new ABDWPSM_Tab( array(
				'display_name'        => ABD_L::__( 'Report a Problem / Debug' ),
				'display_description' => self::debug_tab_header(),
				'url_slug'            => 'debug',
				'page'                => 'ad-blocking-detector'
			) );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs - $Debug_Tab', $time_after_tab, $mem_after_tab, true );
			$time_after_tab = microtime( true );
			$mem_after_tab = memory_get_usage( true );

			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // tabs', $time_bt, $mem_bt, true );

			//	Options Groups
			$time_bt = microtime( true );
			$mem_bt = memory_get_usage( true );
			
			//	New Shortcode Tab
			$NST_OG = new ABDWPSM_Options_Group( array(
				'db_option_name' => ABD_Database::get_shortcode_prefix() . ABD_Database::get_next_id(),
				'validation_callback'  => array( 'ABD_Admin_Views', 'cache_handler_validator' )						
			) );
			$NST_OG->add_to_tab( $New_Shortcode_Tab );

			//	Settings Tab
			$AS_OG = new ABDWPSM_Options_Group( array(
				'db_option_name' => 'abd_user_settings'
			) );
			$AS_OG->add_to_tab( $Settings_Tab );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // options groups', $time_bt, $mem_bt, true );



			//	Sections
			$time_bt = microtime( true );
			$mem_bt = memory_get_usage( true );
			
			$NST_Basic_Section = new ABDWPSM_Section( array(
				'id'                  => 'nst_og-basic_options',
				'display_name'        => ABD_L::__( 'Basic Shortcode Settings' ),
				'display_description' => self::add_new_basic_section_header()
			) );
			$NST_Basic_Section->add_to_options_group( $NST_OG );

			$AS_UDS_Section = new ABDWPSM_Section( array(
				'id'                  => 'as_uds-user_defined_selectors',
				'display_name'        => ABD_L::__( 'Improved Detection: User-Defined Wrapper CSS Selectors' ),
				'display_description' => self::settings_user_defined_selectors_section_header()
			) );
			$AS_UDS_Section->add_to_options_group( $AS_OG );

			$AS_Disable_Section = new ABDWPSM_Section( array(
				'id'                  => 'as_uds-disable_detection_methods',
				'display_name'        => ABD_L::__( 'Performance Improvement: Disable Detection Methods' ),
				'display_description' => self::settings_disable_detection_method_section_header()
			) );
			$AS_Disable_Section->add_to_options_group( $AS_OG );

			$AS_Iframe_Section = new ABDWPSM_Section( array(
				'id'                  => 'as_uds-customize_iframe',
				'display_name'        => ABD_L::__( 'Customize Iframe Detection Method' ),
				'display_description' => self::settings_customize_iframe_section_header()
			) );
			$AS_Iframe_Section->add_to_options_group( $AS_OG );			
			
			$AS_Log_Section = new ABDWPSM_Section( array(
				'id'                  => 'as_uds-log_management',
				'display_name'        => ABD_L::__( 'Log Options' ),
				'display_description' => self::settings_customize_log_section_header()
			) );
			$AS_Log_Section->add_to_options_group( $AS_OG );
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // sections', $time_bt, $mem_bt, true );


			//	Fields
			$time_bt = microtime( true );
			$mem_bt = memory_get_usage( true );
			
			$NST_Basic_Fs = array();
			$AS_UDS_Fs = array();
			//	Basic Section
			$NST_Basic_Fs['display_name'] = new ABDWPSM_Field( array(  
				'field_name'           => 'display_name',
				'type'                 => 'text',
				'display_name'         => ABD_L::__( 'Name / Description' ),
				'display_description'  => ABD_L::__( 'Give this shortcode an identifying name for readability.' ),
				'example_entry'        => ABD_L::__( 'Google AdSense Sidebar Ad' ),
				'field_options_array'  => array( 'required' => true )
			) );
			$NST_Basic_Fs['noadblock'] = new ABDWPSM_Field( array(
				'field_name'           => 'noadblocker',
				'type'                 => 'textarea',
				'display_name'         => ABD_L::__( 'No Ad Blocker Detected Content' ),
				'display_description'  => sprintf( ABD_L::_x( 'Optional. The content to display to users with no detected ad blocker. Supports plain text or HTML. If you aren\'t familiar with HTML, you can use %sthis tool%s to generate the needed HTML for you.', 'No Ad Blocker Detected field description.' ), '<a href="' . self::$our_links['htmlcreator'] . '" target="_blank">', '</a>' )
			) );
			$NST_Basic_Fs['adblock'] = new ABDWPSM_Field( array(
				'field_name'           => 'adblocker',
				'type'                 => 'textarea',
				'display_name'         => ABD_L::__( 'Ad Blocker Detected Content' ),
				'display_description'  => sprintf( ABD_L::_x( 'Optional. The content to display to users with a detected ad blocker. Supports plain text or HTML. If you aren\'t familiar with HTML, you can use %sthis tool%s to generate the needed HTML for you.', 'Ad Blocker Detected field description.' ), '<a href="' . self::$our_links['htmlcreator'] . '" target="_blank">', '</a>' )
			) );
			$NST_Basic_Fs['user_defined_selectors'] = new ABDWPSM_Field( array(
				'field_name'           => 'user_defined_selectors',
				'type'                 => 'text',
				'display_name'         => ABD_L::__( 'User-Defined Wrapper CSS Selectors' ),
				'display_description'  => sprintf( ABD_L::__( 'Optional. Improve ad blocker detection results by specifying the wrapping element around your advertisement that is not removed by ad blockers. This is required to detect privacy browser extensions like Ghostery, but not required for most ad blocking extensions like AdBlock Plus. Separate multiple selectors using semicolons.  %sRead this article%s for more information, detailed instructions, and examples.' ), '<a href="' . self::$our_links['userdefinedwrappers'] . '" target="_blank">', '</a>' ),
				'example_entry'        => 'ins.adsbygoogle; #myadwrapper'
			) );
			$NST_Basic_Fs['blog_id'] = new ABDWPSM_Field( array(
				'field_name'           => 'blog_id',
				'type'                 => 'hidden',
				'field_options_array'  => array( 'default' => ABD_Multisite::get_current_blog_id() )
			) );

			//	Advanced Section
			$enabled_text = ABD_L::__( 'Enabled (default)' );
			$disabled_text = ABD_L::__( 'Disabled' );
			$choices_array = array();
			$choices_array[$enabled_text] = 'enabled';
			$choices_array[$disabled_text] = 'disabled';
					

			foreach( $NST_Basic_Fs as $F ) {
				$F->add_to_section( $NST_Basic_Section );
			}



			$AS_UDS_Field = new ABDWPSM_Field( array(
				'field_name'          => 'user_defined_selectors',
				'type'                => 'text',
				'display_name'        => ABD_L::__( 'Global CSS Selectors' ),
				'display_description' => ABD_L::__( 'A list of CSS selectors for the wrapping elements. Separate multiple selectors with semicolons.' ),
				'example_entry'       => 'ins.adsbygoogle; #myawesomeadserverwrapper',
				'field_options_array' => array(
					'default'    => 'ins.adsbygoogle',
					'style'      => 'min-width: 350px'
				)
			) );
			$AS_UDS_Field->add_to_section( $AS_UDS_Section );

			$AS_Disable_Iframe_Field = new ABDWPSM_Field( array(
				'field_name'          => 'enable_iframe',
				'type'                => 'radio',
				'display_name'        => ABD_L::__( 'Iframe Detection Method' ),
				'field_options_array' => array(
					'choices' => array( 'Enabled'=>'yes', 'Disabled'=>'no' ),
					'default' => 'yes'
				)
			) );
			$AS_Disable_Iframe_Field->add_to_section( $AS_Disable_Section );

			$AS_Disable_Div_Field = new ABDWPSM_Field( array(
				'field_name'          => 'enable_div',
				'type'                => 'radio',
				'display_name'        => ABD_L::__( 'Div Element Detection Method' ),
				'field_options_array' => array(
					'choices' => array( 'Enabled'=>'yes', 'Disabled'=>'no' ),
					'default' => 'yes'
				)
			) );
			$AS_Disable_Div_Field->add_to_section( $AS_Disable_Section );

			$AS_Disable_JS_Field = new ABDWPSM_Field( array(
				'field_name'          => 'enable_js_file',
				'type'                => 'radio',
				'display_name'        => ABD_L::__( 'JavaScript File Detection Method' ),
				'field_options_array' => array(
					'choices' => array( 'Enabled'=>'yes', 'Disabled'=>'no' ),
					'default' => 'yes'
				)
			) );
			$AS_Disable_JS_Field->add_to_section( $AS_Disable_Section );




			$AS_Iframe_URL = new ABDWPSM_Field( array(
				'field_name'          => 'iframe_url',
				'type'                => 'text',
				'display_name'        => ABD_L::__( 'URL of Iframe' ),
				'display_description' => ABD_L::__( 'The bait iframe\'s URL. This should contain ad or advertisement keywords. I recommend a URL that doesn\'t exist to keep loading times down. Leave empty to allow automatic an automatic URL choice.' ),
				'example_entry'       => 'http://YHrSUDwvRGxPpWyM-ad.us/adserver/adlogger_tracker.php',
				'field_options_array' => array(
					'default' => '',
					'style'   => 'width: 50%; max-width: 500px; min-width: 170px;'
				)
			) );
			$AS_Iframe_URL->add_to_section( $AS_Iframe_Section );


			$AS_Log_enable_perf = new ABDWPSM_Field( array(
				'field_name'          => 'enable_perf_logging',
				'type'                => 'radio',
				'display_name'        => ABD_L::__( 'Performance Statistics Logging' ),
				'display_description' => ABD_L::__( 'Whether to record execution times and memory usage in the Session Log. This helps tracking down performance related plugin bugs, but generates a lot of log entries and uses slightly more overhead and database traffic.' ),				
				'field_options_array' => array(
					'choices' => array( 'Enabled'=>'yes', 'Disabled'=>'no' ),
					'default' => 'yes'
				)
			) );
			$AS_Log_perf_filtering = new ABDWPSM_Field( array(
				'field_name'          => 'perf_logging_only_above_limits',
				'type'                => 'radio',
				'display_name'        => ABD_L::__( 'Filter Performance Summary Log Entries' ),
				'display_description' => ABD_L::__( 'Whether to limit performance log entries to those exceeding the time and memory limits.' ),				
				'field_options_array' => array(
					'choices' => array( 'Enabled'=>'yes', 'Disabled'=>'no' ),
					'default' => 'no'
				)
			) );
			$AS_Log_perf_time_limit = new ABDWPSM_Field( array(
				'field_name'          => 'perf_logging_time_limit',
				'type'                => 'number',
				'display_name'        => ABD_L::__( 'Peformance Summary Log Entry Time Limit' ),
				'display_description' => ABD_L::__( 'The time threshold, in milliseconds, at which log entries are highlighted and cut off if performance entry filtration is enabled.' ),
				'field_options_array' => array(
					'default' => 100
				)
			) );
			$AS_Log_perf_mem_limit = new ABDWPSM_Field( array(
				'field_name'          => 'perf_logging_mem_limit',
				'type'                => 'number',
				'display_name'        => ABD_L::__( 'Peformance Summary Log Entry Memory Limit' ),
				'display_description' => ABD_L::__( 'The used memory threshold, in bytes, at which log entries are highlighted and cut off if performance entry filtration is enabled.' ),
				'field_options_array' => array(
					'default' => 1048576
				)
			) );
			$AS_Log_enable_perf->add_to_section( $AS_Log_Section );
			$AS_Log_perf_filtering->add_to_section( $AS_Log_Section );
			$AS_Log_perf_time_limit->add_to_section( $AS_Log_Section );
			$AS_Log_perf_mem_limit->add_to_section( $AS_Log_Section );


			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // fields', $time_bt, $mem_bt, true );


			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings() // before ogize_existing_shortcodes()', $start_time, $start_mem );

			//	Deal with Existing Shortcodes Tab which is all programmatic, not hard coded
			self::ogize_existing_shortcodes( $Existing_Shortcodes_Tab );


			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::wpsm_settings()', $start_time, $start_mem );
		}

		protected static function ogize_existing_shortcodes( &$The_Tab_To_Add_To ) {			
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );

			$enabled_text                  = ABD_L::__( 'Enabled (default)' );
			$disabled_text                 = ABD_L::__( 'Disabled' );
			$choices_array                 = array();
			$choices_array[$enabled_text]  = 'enabled';
			$choices_array[$disabled_text] = 'disabled';

			//	This site's shortcodes
			$scs = ABD_Database::get_all_shortcodes();	//	All shortcodes stored in DB for this site
			if( !is_array( $scs ) ) { $scs = array(); }

			if( !empty( $scs) ) {
				foreach( $scs as $sc_id => $sc ) {

					//	Determine if we'll be disabling everything because shortcode is readonly
					if( ABD_Database::array_value( 'readonly', $sc ) == true ) {
						$readonly = true;
					}
					else {
						$readonly = false;
					}

					$OG = new ABDWPSM_Options_Group( array( 
						'db_option_name'       => ABD_Database::get_shortcode_prefix() . $sc_id,
						'validation_callback'  => array( 'ABD_Admin_Views', 'cache_handler_validator' ),
						'section_object_array' => array(
							new ABDWPSM_Section( array(  
								'id'                 => 'shortcode_section_' . $sc_id,
								'display_name'       => ABD_Database::array_value( 'display_name', $sc ),
								'display_description'=> self::manage_shortcodes_shortcode_section_header( $sc_id, $readonly ),
								'field_object_array' => array(
									new ABDWPSM_Field( array( 
										'field_name'           => 'display_name',
										'type'                 => 'text',
										'display_name'         => ABD_L::__( 'Name / Description' ),
										'display_description'  => ABD_L::_x( 'Give this shortcode an identifying name for readability.', 'Display Name field description.' ),
										'example_entry'        => ABD_L::_x('Google AdSense Sidebar Ad', 'Display Name field example entry.'),
										'field_options_array'  => array( 'required' => true, 'default' => ABD_Database::array_value( 'display_name', $sc ), 'readonly' => $readonly, 'disabled' => $readonly )
									) ),
									new ABDWPSM_Field( array( 
										'field_name'           => 'noadblocker',
										'type'                 => 'textarea',
										'display_name'         => ABD_L::__( 'No Ad Blocker Detected Content' ),
										'display_description'  => sprintf( ABD_L::_x( 'Optional. The content to display to users with no detected ad blocker. Supports plain text or HTML. If you aren\'t familiar with HTML, you can use %sthis tool%s to generate the needed HTML for you.', 'No Ad Blocker Detected field description.' ), '<a href="' . self::$our_links['htmlcreator'] . '" target="_blank">', '</a>' ),
										'field_options_array'  => array( 'default' => ABD_Database::array_value( 'noadblocker', $sc ), 'readonly' => $readonly, 'disabled' => $readonly )
									) ),
									new ABDWPSM_Field( array(  
										'field_name'           => 'adblocker',
										'type'                 => 'textarea',
										'display_name'         => ABD_L::__( 'Ad Blocker Detected Content' ),
										'display_description'  => sprintf( ABD_L::_x( 'Optional. The content to display to users with a detected ad blocker. Supports plain text or HTML. If you aren\'t familiar with HTML, you can use %sthis tool%s to generate the needed HTML for you.', 'Ad Blocker Detected field description.' ), '<a href="' . self::$our_links['htmlcreator'] . '" target="_blank">', '</a>' ),
										'field_options_array'  => array( 'default' => ABD_Database::array_value( 'adblocker', $sc ), 'readonly' => $readonly, 'disabled' => $readonly )
									) ),
									new ABDWPSM_Field( array(
										'field_name'           => 'user_defined_selectors',
										'type'                 => 'text',
										'display_name'         => ABD_L::__( 'User-Defined Wrapper CSS Selectors' ),
										'display_description'  => sprintf( ABD_L::__( 'Optional. Improve ad blocker detection results by specifying the wrapping element around your advertisement that is not removed by ad blockers. This is required to detect privacy browser extensions like Ghostery, but not required for most ad blocking extensions like AdBlock Plus. Separate multiple selectors using semicolons.  %sRead this article%s for more information, detailed instructions, and examples.' ), '<a href="' . self::$our_links['userdefinedwrappers'] . '" target="_blank">', '</a>' ),
										'example_entry'        => 'ins.adsbygoogle; #myadwrapper',
										'field_options_array'  => array( 'default' => ABD_Database::array_value( 'user_defined_selectors', $sc ), 'readonly' => $readonly, 'disabled' => $readonly )
									) ),
									new ABDWPSM_Field( array(
										'field_name'           => 'blog_id',
										'type'                 => 'hidden',
										'field_options_array'  => array( 'default' => ABD_Database::array_value( 'blog_id', $sc ), 'readonly' => $readonly, 'disabled' => $readonly )
									) )
								)	// end 'field_object_array'
							) )	//	end new ABDWPSM_Section
						)	//	end 'section_object_array'
					) );	//	end new ABDWPSM_Options_Group
					
					$OG->add_to_tab( $The_Tab_To_Add_To );
				}	//	end foreach(...
			}	//	end if( is_array( $scs ) )
			else {
				//	No shortcodes in array.
				$The_Tab_To_Add_To->set_display_description( '<br /><h3>' . ABD_L::__( 'No shortcodes yet! Click the "Add New Shortcode" tab above to create your first one.' ) . '</h3>' );
			}



			//		Performance log
			ABD_Log::perf_summary( 'ABD_Database::ogize_existing_shortcodes()', $start_time, $start_mem );
		}	//	end ogize_existing_shortcodes


		protected static function getting_started_tab_header() {
			ob_start();
			?>

			<h2><?php ABD_L::_e( 'Getting Started' ); ?></h2>

			<?php
			echo self::getting_started_tab_content();

			return ob_get_clean();
		}

		protected static function getting_started_tab_content() {
			//		Collect start state for performance logging
			$start_time = microtime( true );
			$start_mem = memory_get_usage( true );
			

			if( defined( 'ABDBLC_ROOT_URL' ) ) {
				//	Then our block list countermeasure plugin is loaded because it defines this constant.
				$prefix = ABDBLC_ROOT_URL;
			}
			else {
				$prefix = ABD_ROOT_URL;
			}

			$bfs_time = microtime( true );
			$bfs_mem = memory_get_usage( true );
			$blcp_status = array(
				'auto_plugin_activated'   => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_activated' ),
				'manual_plugin_activated' => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_activated' ),
				'auto_plugin_exists'      => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_exists' ),
				'manual_plugin_exists'    => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_exists' ),
				'plugin_activated'        => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_activated' ),
				'plugin_exists'           => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_exists' )
			);
			ABD_Log::perf_summary( 'ABD_Admin_Views::getting_started_tab_content // before $blcp_status = ABD_Anti_Adblock::bcc_plugin_status();', $bfs_time, $bfs_mem, true );

			$abd_settings = ABD_Database::get_settings();

			ob_start();

			?>
			<div id="abd_getting_started_message" class="abd-masonry-wrapper">
				<div class="abd-subtle-highlight abd-masonry-block">
					<h3><span class="abd-masonry-header-charm">!!!</span> &nbsp; <?php ABD_L::_e( 'The Ad Blockers Are Coming! '); ?></h3>

					<img class='abd-masonry-image' src="<?php echo $prefix . 'assets/images/targeted.png'; ?>" />

					<p>
						<?php ABD_L::_e( 'Ad Blocking Detector is now targeted by ad blockers. A characteristic of WordPress plugin installation makes it very simple for them to block crucial plugin files, preventing detector from working.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'Fortunately, there is a way to circumvent the ad blockers, but it requires your help.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'You need to visit the Advanced Settings tab above, and follow the instructions for creating the Block List Countermeasure plugin. Until you do this, there is a good chance Ad Blocking Detector will not work correctly!' ); ?>
					</p>
				</div>

				<div class="abd-masonry-block">
					<h3><?php ABD_L::_e( 'Set-Up & Configuration Checklist' ); ?></h3>

					<p><?php ABD_L::_e( 'Reliably detecting ad blockers is tricky business!  Simply installing this plugin isn\'t enough, you need to do a little set-up. Use the checklist below to get started.'  ); ?></p>

					<ul style="list-style-type: square">
						<?php
							if( $blcp_status['plugin_activated'] ) {
								$class = 'class="abd_success_message"';
							}
							else {
								$class = 'class="abd_failure_message"';
							}
						?>
						<li <?php echo $class; ?>>
							<strong><?php ABD_L::_e( 'Set-Up Block List Countermeasure Plugin.' ); ?></strong>
							<ul style="font-size: .85em; margin-left: 40px;">
								<li><?php ABD_L::_e( 'Visit the Advanced Settings tab above.' ); ?></li>
								<li><?php ABD_L::_e( 'Follow the instructions to create the Block List Countermeasure Plugin.' ); ?></li>
							</ul>
						</li>
						<?php
							if( ABD_Database::count_shortcodes() > 0 ) {
								$class = 'class="abd_success_message"';
							}
							else {
								$class = 'class="abd_failure_message"';
							}
						?>
						<li <?php echo $class; ?>>
							<strong><?php ABD_L::_e( 'Create an alternative content shortcode.' ); ?></strong>
							<ul style="font-size: .85em; margin-left: 40px;">
								<li><?php ABD_L::_e( 'Visit the Add New Shortcode tab above.' ); ?></li>
								<li><?php echo sprintf( ABD_L::__( 'Improve detection results by %sspecifying the wrapping element%s of your ad.' ), '<a target="_blank" href="' . self::$our_links['userdefinedwrappers'] . '">', '</a>' ); ?></li>
							</ul>
						</li>
					</ul>
				</div>

				<div class="abd-masonry-block">				
					<h3><?php ABD_L::_e( 'Basic Plugin Usage' ); ?></h3>
					<p>
						<?php ABD_L::_e( 'The primary tool Ad Blocking Detector provides is an alternative content shortcode creator.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'With this tool, you specify content to display if no ad blockers are detected, and alternative content to display if an ad blocker is detected.' ); ?>
					</p>
					<p>
						<?php echo sprintf( ABD_L::__( 'This content is then tied to a %sWordPress shortcode%s.' ), '<a href="http://www.wpbeginner.com/glossary/shortcodes/" target="_blank" title="What is a WordPress shortcode?">', '</a>' ); ?>
					</p>

					<p>
						<?php ABD_L::_e( 'You create these shortcodes in the "Add New Shortcode" tab at the top of this page. You edit or delete existing shortcodes in the "Manage Shortcodes" tab.' ); ?>
					</p>

					<p>
						<?php ABD_L::_e( 'Once you have created a shortcode, you can display it in three ways:' ); ?>
						<ol>
							<li>
								<?php echo wp_kses( ABD_L::__( '<strong>Manually copy and paste the shortcode.</strong>  Visit the "Manage Shortcodes" tab and click the "Get This Shortcode" button.  You then copy and paste the shortcode anywhere WordPress supports them, such as posts.' ), array( 'strong' => array(), 'em' => array() ) ); ?>								
							</li>
							<li>
								<?php echo wp_kses( ABD_L::__( '<strong>Use this plugin\'s widget.</strong>  Visit the "Widgets" page of your WordPress dashboard, and drag the Ad Blocking Detector widget to your desired widgets area.  In the dropdown menu for the widget, choose the shortcode you wish to display.' ), array( 'strong' => array(), 'em' => array() ) ); ?>
								
							</li>
							<li>
								<?php echo wp_kses( ABD_L::__( '<strong>Code the shortcode into your theme.</strong>  Follow the same steps as above to manually copy your shortcode.  Then, at the desired location in your theme, use WordPress\' do_shortcode() function. ' ), array( 'strong' => array(), 'em' => array() ) ); ?><br /><br />
								 
								<?php ABD_L::_e( 'Example:' ); ?><br /> <em>&lt;?php echo do_shortcode('[adblockingdetector id="1234567"]'); ?&gt;</em>
							</li>
						</ol>
					</p>

					<p>
						<?php ABD_L::_e( 'And that should be it! Ad blocker detection made easy.' ); ?>
					</p>
					<p>
						<a style="font-size: 1.15em;" target="_blank" href="<?php echo self::$our_links['pluginintro']; ?>"><?php ABD_L::_e( 'Click here to read a detailed introduction, with screenshots, on the plugin\'s website.' ); ?></a>
					</p>
					
				</div>

				<div class="abd-masonry-block">
					<h3><?php ABD_L::_e( 'Advanced Plugin Usage' ); ?></h3>

					<p>
						<?php ABD_L::_e( 'If the alternative content shortcodes aren\'t what you are looking for, then you\'ll need to turn to the advanced features of the plugin.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'The very mechanism that makes the alternative content shortcodes work also provide JavaScript and CSS hooks you can use yourself.  If you have passing familiarity with either of those web languages, you can do some crazy things with this plugin.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'The details of using JavaScript and CSS hooks are a bit too lengthy for this simple plugin introduction.  You can, however, find instructions on the plugins website:' ); ?>
						<ul>
							<li><a href="<?php echo self::$our_links['jstargeting']; ?>"  target='_blank'><?php ABD_L::_e( 'Combine JavaScript and Ad Blocking Detector' ); ?></a></li>
							<li><a href="<?php echo self::$our_links['csstargeting']; ?>" target='_blank'><?php ABD_L::_e( 'Combine CSS and Ad Blocking Detector' ); ?></a></li>
						</ul>
					</p>
					<p>
						<?php ABD_L::_e( 'If you do anything awesome with these capabilities, let me know!' ); ?>
					</p>
				</div>

				<div class="abd-stark-highlight abd-masonry-block">
					<h3><?php ABD_L::_e( 'Support This Plugin' ); ?></h3>
					<p>
						<?php ABD_L::_e( 'Is this plugin useful for you? If so, please support its ongoing development and improvement with a donation.' ); ?>
					</p>
					<p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHXwYJKoZIhvcNAQcEoIIHUDCCB0wCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYARbhzWvm3HnnsOKlP/iXUclW3g7+mC4R887cAeVbB5Al7EcdnpnThJCxOvnQeVU+/c83Zoqf1oNnEfclqGAwZv155zT9Ijx5HkLM1Ge4htiZo1VOodJxw8YMI3ey+6DXhmxmHtN8Giuu2fNUuSwewBBDwCnaBFgRmTBMbjj9a2DzELMAkGBSsOAwIaBQAwgdwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIx0ZZk+kozCKAgbg1R7kzZayZEFuR1goTxpqTwcVoCGLOjJ8A6AcRgyBQ3X4pldp/epPXtfLoL+VsQKoNfzz+Zk5kqCFKh134km2GNm8u5NJ0qOKIvgB4xjB7a2eu29Xqg9NpjmfA3WLvRlRAefvR5GUoQyjv6DPlwycUbVwz4lK5vPRh1VW+CrmiemjjJalBYZIpEMRxGDQclhxmfJGldvNs4mwOQtYxJHHyW4p0bHBqHhijuXrXWeONhCtazJGd0iAAoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTMxMjI0MDEwODEzWjAjBgkqhkiG9w0BCQQxFgQU/Qe4Q7yuJR0yriKLReY2JgLVk+EwDQYJKoZIhvcNAQEBBQAEgYART+ZC7igjQUOYcDyVyHBVpddyRsbTEdXoG+7Lv17GzN1RYvdl610lbOaRAB3VMcOo68bNV/CVkwpY5P9cpUc9D1ksVTgearcIllltLdCScfbXMX5sdSuDTFg0xCrRXBj5nqNP9l58HNvG2oZVfERUcsC37fHKAGzW1WHhZ9vFOw==-----END PKCS7-----">
							<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</p>
					<p>
						<?php ABD_L::_e( 'Or, if you are short on funds, there are other ways to help out:' ); ?>
					</p>
					<ul>
						<li>
							<?php
							$link = self::$our_links['wporgreviews'];
							echo sprintf( 
								wp_kses( 
									ABD_L::__( 'Leave a positive review on the plugin\'s <a href="%s" target="_blank">WordPress.org listing</a>.' ), 
									array( 
										'a' => array( 
											'href' => array(), 
											'target' => array() 
										)
									)
								),	//	end wp_kses
								esc_url( $link )
							);	//	end sprintf
							?>
						</li>
						<li>
							<?php
							$link = self::$our_links['twitterlove'];
							echo sprintf( 
								wp_kses( 
									ABD_L::__( '<a href="%s" target="_blank">Share your thoughts on Twitter</a> and other social media sites.' ), 
									array( 
										'a' => array( 
											'href' => array(), 
											'target' => array() 
										)
									)
								),	//	end wp_kses
								esc_url( $link )
							);	//	end sprintf
							?>
							
						</li>
						<li>
							<?php
							$link = self::$our_links['github'];
							echo sprintf( 
								wp_kses( 
									ABD_L::__( 'Improve this plugin on <a href="%s" target="_blank">GitHub</a>.' ), 
									array( 
										'a' => array( 
											'href' => array(), 
											'target' => array() 
										)
									)
								),	//	end wp_kses
								esc_url( $link )
							);	//	end sprintf
							?>
							
						</li>
					</ul>
				</div>
			</div>

			<?php
			$ob = ob_get_clean();
			ABD_Log::perf_summary( 'ABD_Admin_Views::getting_started_tab_content()', $start_time, $start_mem );

			return $ob;
		}

		protected static function manage_shortcodes_tab_header() {
			ob_start();
			?>

			<h2><?php ABD_L::_e( 'Manage Shortcodes' ); ?></h2>
			<p><?php ABD_L::_e( 'Each of your created shortcodes will be listed below.  Unless otherwise indicated, you can edit any shortcode using the fields, delete any shortcode using the Delete This Shortcode button, and get the shortcode to insert on your website using the Get This Shortcode button.' ); ?></p>

			<?php
			return ob_get_clean();
		}

		protected static function manage_shortcodes_tab_description() {
			ob_start();
			?>



			<?php
			return ob_get_clean();
		}

		protected static function add_new_shortcode_tab_header() {
			ob_start();
			?>

			<h2><?php ABD_L::_e( 'Add New Shortcode' ); ?></h2>
			<p><?php ABD_L::_e( 'Use the fields below to create an alternative content shortcode. Once created, visit the Manage Shortcodes tab to copy the shortcode and use it on your website.' ); ?></p>
			<p><strong style="font-size: 1.15em;"><a target="_blank" href="<?php echo self::$our_links['simpleadintro']; ?>"><?php ABD_L::_e( "Read This Article For More Information" ); ?></a></strong></p>

			<?php
			return ob_get_clean();
		}

		protected static function add_new_shortcode_tab_description() {
			ob_start();
			?>

			

			<?php
			return ob_get_clean();
		}

		protected static function debug_tab_header() {
			ob_start();
			?>

			<h2><?php ABD_L::_e( 'Report a Problem' ); ?></h2>

			<?php
			echo self::debug_tab_description();

			return ob_get_clean();
		}

		protected static function debug_tab_description() {
			global $wpdb;
			$db_prefix = $wpdb->base_prefix;	//	Used for some Support Request Notes

			ob_start();
			?>

			<div id="abd-debug-tab-masonry" class="abd-masonry-wrapper">
				<div class="abd-masonry-block" style="width: 100%;">
					<h3><?php ABD_L::_e( 'Support Request Notes' ); ?></h3>

					<p>
						<?php ABD_L::_e( 'This plugin is very complex. Fixing problems and providing adequate support often requires a lot of information.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'To expedite the support process, I have provided some intial guidelines for common types of issues below, along with a few examples of effective support requests. Simply click on the heading that most closely matches your topic for some explanation and a preliminary list of questions I would likely ask.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'I provide these guidelines and samples to make the process simpler and faster.  It\'s not a requirement for support or a punitive response. If information is prohibitively difficult to access, you don\'t understand what I\'m asking, or any other problems, don\'t worry about it. Just contact me, and I\'ll walk you through anything needed.' ); ?>
					</p>





					<strong><?php ABD_L::_e( 'Problems with This Dashboard:' ); ?></strong><br />
					<div class='abd-accordion'>
						<h4><?php ABD_L::_e( 'In the dashboard, when I click ___________, nothing happens.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Repeat the procedure again. If the problem has gone away, move on and come back here if it reappears. If the problem still occurs, or happens intermittently, keep reading.' );?></li>
									<li><?php ABD_L::_e( 'Do you see an error message, or does literally nothing occur? If you see an error message, try the next section down. If literally nothing happens, keep reading.' ); ?></li>
									<li><?php ABD_L::_e( 'Temporarily disable any ad blockers you use, and try again. Some ad blockers erroneously block portions of this plugin\'s dashboard. If disabling ad blockers works, make sure you have followed the setup checklist in the Getting Started tab.  Particularly with regard to the Block List Countermeasure Plugin.  If you\'ve followed the checklist and the problem still occurs, check the entry topic below titled "The countermeasure plugin is activated, but isn\'t stopping ad blockers from breaking this plugin."  If you are not using an ad blocker or it is disabled, keep reading.' ); ?></li>
									<li><?php ABD_L::_e( 'Make sure JavaScript is enabled. If you don\'t know what that means, your JavaScript is probably enabled, and you can skip this. Otherwise, if you use a NoScript web browser add-on, disable it when using this dashboard, or if you have turned JavaScript off altogether, turn it back on.  Then, refresh the page, and try again.  If JavaScript is enabled, and a page referesh and reattempt doesn\'t work, keep reading.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'What are you clicking? Usually, the name of the tab, and the button or object you are clicking is sufficient.' ); ?></li>
									<li><?php ABD_L::_e( 'What does "nothing" mean specifically? Is it literally nothing, or does something like a page refresh occur, but no changes are observed. This will tell me whether it is the button/object that is broken, or the action triggered by the button/object.' ); ?></li>
									<li><?php ABD_L::_e( 'Have you noticed similar behavior elsewhere?  For example, if clicking one button does not work, is there another button similarly afflicted as well?' ); ?></li>
									<li><?php ABD_L::_e( 'Does this problem occur in a different web browser too?  For example, if your primary browser is Google Chrome, does it also occur in Internet Explorer or Safari?' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'The operating system and web browser you are using.  Usually something like %sMicrosoft Windows and Google Chrome%s is enough. However, versions are helpful too.' ), '<em>', '</em>' ); ?></li>									
									<li><?php ABD_L::_e( 'The contents of the Session Log on this page, after clicking the button and nothing happens.' ); ?></li>
									<li><?php echo sprintf( ABD_L::__( 'The contents of your JavaScript console immediately after clicking the broken button and nothing happens. %sClick here for information on collecting JavaScript console output%s.' ), '<a target="_blank" href="' . self::$our_links['console'] .'">', '</a>' ); ?></li>
								</ul>
							</p>
						</div>



						<h4><?php ABD_L::_e( 'In the dashboard, when I try to do ___________, I get an incorrect or uninformative error message.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Make sure the specific error is not an entry in the list of topics below. If the error message is mentioned in another topic, try that one instead. If the error is not mentioned below, keep reading.' ); ?></li>
									<li><?php ABD_L::_e( 'Repeat the procedure again. If the problem still occurs, or happens intermittently, keep reading.' );?></li>
									<li><?php ABD_L::_e( 'Temporarily disable any ad blockers you use, and try again. Some ad blockers erroneously block portions of this plugin\'s dashboard. If disabling ad blockers works, make sure you have followed the setup checklist in the Getting Started tab.  Particularly with regard to the Block List Countermeasure Plugin.  If you\'ve followed the checklist and the problem still occurs, check the entry topic below titled "The countermeasure plugin is activated, but isn\'t stopping ad blockers from breaking this plugin."  If you are not using an ad blocker or it is disabled, keep reading.' ); ?></li>
									<li><?php ABD_L::_e( 'Make sure JavaScript is enabled. If you don\'t know what that means, your JavaScript is probably enabled, and you can skip this. Otherwise, if you use a NoScript web browser add-on, disable it when using this dashboard, or if you have turned JavaScript off altogether, turn it back on.  Then, refresh the page, and try again.  If JavaScript is enabled, and a page referesh and reattempt doesn\'t work, keep reading.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'What are you clicking or trying to do that leads to the error message? The more specific you are, the better.' ); ?></li>
									<li><?php ABD_L::_e( 'What does the error message say? Copying and pasting the message is usually sufficient.' ); ?></li>
									<li><?php ABD_L::_e( 'Have you noticed similar behavior elsewhere?  For example, if clicking one button yields the error, is there another button similarly afflicted as well?' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'The contents of the Session Log on this page, after clicking the button and nothing happens.' ); ?></li>
									<li><?php echo sprintf( ABD_L::__( 'The contents of your JavaScript console immediately after reattempting the broken process. %sClick here for information on collecting JavaScript console output%s.' ), '<a target="_blank" href="' . self::$our_links['console'] . '">', '</a>' ); ?></li>
								</ul>
							</p>
						</div>



						<h4><?php ABD_L::_e( 'The dashboard layout is broken. It is ugly and looks nothing like screenshots, videos, and instructions show or describe.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Temporarily disable any ad blockers you use, and try again. Some ad blockers erroneously block portions of this plugin\'s dashboard. If disabling ad blockers works, make sure you have followed the setup checklist in the Getting Started tab.  Particularly with regard to the Block List Countermeasure Plugin.  If you\'ve followed the checklist and the problem still occurs, check the entry topic below titled "The countermeasure plugin is activated, but isn\'t stopping ad blockers from breaking this plugin."  If you are not using an ad blocker or it is disabled, keep reading.' ); ?></li>
									<li><?php ABD_L::_e( 'Make sure JavaScript is enabled. If you don\'t know what that means, your JavaScript is probably enabled, and you can skip this. Otherwise, if you use a NoScript web browser add-on, disable it when using this dashboard, or if you have turned JavaScript off altogether, turn it back on.  Then, refresh the page, and try again.  If JavaScript is enabled, and a page referesh and reattempt doesn\'t work, keep reading.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Does every tab look this way, or just one?' ); ?></li>
									<li><?php ABD_L::_e( 'Have you noticed similar behavior elsewhere?  For example, do other pages in your WordPress dashboard look messed up to?' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'The contents of your JavaScript console, when viewing one of the broken pages. %sClick here for information on collecting JavaScript console output%s.' ), '<a target="_blank" href="' . self::$our_links['console'] . '">', '</a>' ); ?></li>
								</ul>
							</p>
						</div>



						<h4><?php ABD_L::_e( 'When saving a new shortcode, I get an "ERROR: option does not exist" message or a "Your options group needs a unique identifying property..." error message.' ); ?></h4>
						<div>
							<p style="font-style: italic;">
								<?php ABD_L::_e( 'This problem can occur if you let your site idle on the "Add New Shortcode" tab for an extended period, then try to save a shortcode. Ad Blocking Detector reserves names and space in your database for a new shortcode when you visit this tab. After extended inactivity, it releases its reservation, but the Add New Shortcode tab does not know that because it has been idling. A simple refresh of the "Add New Shortcode" tab, if it sits for more than an hour or two, will prevent this error from occurring. To fix the error after you have received it, refer to below.' ); ?>
							</p>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Completely close your web browser, wait a few minutes, then reopen it and try again.' ); ?></li>
									<li><?php ABD_L::_e( '*OPTIONAL* Uninstall and reinstall Ad Blocking Detector, then try again.  Keep in mind this will delete all your shortcodes.  If you can\'t easily recreate existing ones, then skip this fix.' ); ?></li>
									<li><?php echo sprintf( ABD_L::__( '**OPTIONAL** **ADVANCED** Run the following SQL query on your WordPress database.  Note, that this query assumes the default table prefix of wp_, and that you\'re not using WordPress Multisite.  The query will not work if your table prefix is different or if you\'re using Multisite. QUERY: %sDELETE FROM wp_options WHERE option_name=\'_transient_abd_next_shortcode_id\' LIMIT 1%s' ), '<em>', '</em>' ); ?></li>
									<li><?php ABD_L::_e( '*OPTIONAL* Wait a while. The aforementioned reservation this plugin makes gets completely removed and reset every day, and the error should go away on its own.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Are you using WordPress Multisite?' ); ?></li>
									<li><?php ABD_L::_e( 'Which of the above "fixes," if any, did you not try?' ); ?></li>									
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'Your database prefix.  YOUR PREFIX: %s' . $db_prefix . '%s' ), '<em>', '</em>' ); ?></li>
									<li><?php echo sprintf( ABD_L::__( 'If using Multisite, the ID number of the site in your network with the problem.  YOUR SITE ID NUMBER: %s' . ABD_Multisite::get_current_blog_id() . '%s' ), '<em>', '</em>' ); ?></li>
								</ul>
							</p>
						</div>
					</div>






					<br /><br />







					<strong><?php ABD_L::_e( 'Problems with Ad Blocker Detection:' ); ?></strong>
					<div class='abd-accordion'>
						<h4><?php ABD_L::_e( 'The plugin doesn\'t work with the ______________ ad blocker.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'Visit the %sdemo page on the plugin\'s website%s with this ad blocker enabled. If the demo works, this is likely a problem with the content in your shortcode, not the ad blocker detection. In that case, see the topic below this one. If the demo does not work, keep reading.' ), '<a target="_blank" href="' . self::$our_links['demo'] . '">', '</a>' ); ?></li>
									<li><?php ABD_L::_e( 'Make sure JavaScript is enabled. If you don\'t know what that means, your JavaScript is probably enabled, and you can skip this. Otherwise, if you use a NoScript web browser add-on, disable it, or if you have turned JavaScript off altogether, turn it back on.  Then, refresh the page, and try the demo page again.  If JavaScript is enabled, and a page referesh and reattempt doesn\'t work, keep reading.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'What ad blocker isn\'t working? There are a lot of browser add-ons named AdBlock and similar, so be specific. A link to the product\'s website or listing in your web browsers add-on/extension repository is the most helpful.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'The operating system and web browser you are using.  Usually something like %sMicrosoft Windows and Google Chrome%s is enough. However, versions are helpful too.' ), '<em>', '</em>' ); ?></li>									
									<li><?php ABD_L::_e( 'List all of the block/filter lists (if applicable) used by the ad blocker.  For example: "EasyList, EasyPrivacy, French"' ); ?></li>
									<li><?php ABD_L::_e( 'The contents of the Ad Blocking Detection Results box on this page. Make sure the ad blocker is enabled, then refresh this page before collecting the contents to ensure accuracy.' ); ?></li>
								</ul>
							</p>
						</div>





						<h4><?php ABD_L::_e( 'My alternative content shortcode doesn\'t behave properly in the presence of an ad blocker.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'Visit the %sdemo page on the plugin\'s website%s with this ad blocker enabled. If the demo does not work, this is likely a problem detecting your ad blocker, not with your shortcode.  In that case, see the topic above this one. If the demo does work, keep reading.' ), '<a target="_blank" href="' . self::$our_links['demo'] . '">', '</a>' ); ?></li>
									<li><?php ABD_L::_e( 'Disable performance improvement (caching, asynchronous, lazy load, et cetera) plugins temporarily, and check if the shortcode works again. If disabling the performance plugin fixes the problem, let me know this and the name of the performance plugin.  If disabling the performance plugin does not fix the problem, keep reading.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Does a simple shortcode work?  Add a new shortcode that is very simple, and try that instead. For example, just write "ad blocker found" in the Ad Blocker Detected content box, and "no adblocker found" in the other, and include whether that one works or not.'  ); ?></li>
									<li><?php ABD_L::_e( 'What ad blocker are you using? There are a lot of browser add-ons named AdBlock and similar, so be specific. A link to the product\'s website or listing in your web browsers add-on/extension repository is the most helpful.' ); ?></li>
									<li><?php ABD_L::_e( 'If your theme is not custom, or is a simple modification of another non-custom theme, what theme are you using, or what theme is it based on?' ); ?></li>
									<li><?php ABD_L::_e( 'What specifically is wrong? For example: "There is an empty space where the no ad blocker detected content should show."' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'The operating system and web browser you are using.  Usually something like %sMicrosoft Windows and Google Chrome%s is enough. However, versions are helpful too.' ), '<em>', '</em>' ); ?></li>									
									<li><?php ABD_L::_e( 'The contents of the Ad Blocking Detection Results box on this page. Make sure your ad blocker is enabled, then refresh this page before collecting the contents to ensure accuracy.' ); ?></li>
									<li><?php ABD_L::_e( 'A screenshot of the offending shortcode\'s section in the Manage Shortcodes tab.  Specifically, I\'m looking for the content in the fields, but copying and pasting that can be problematic, so a screenshot is usually simpler.  This screenshot can be a link to an uploaded copy of the image, or it can be attached to an email.' ); ?></li>
								</ul>
							</p>
						</div>
					</div><!-- end <div class='abd-accordion'> -->






					<br /><br />







					<strong><?php ABD_L::_e( 'Problems with the Automatic Block List Countermeasure Plugin:' ); ?></strong>
					<div class='abd-accordion'>
						<h4><?php ABD_L::_e( 'The countermeasure plugin is activated, but isn\'t stopping ad blockers from breaking this plugin.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Use the controls on the Advanced Settings tab to reset the Block List Countermeasure Plugin directory name.' ); ?></li>
									<li><?php ABD_L::_e( 'Deactivate and reactivate the Block List Countermeasure Plugin.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php echo sprintf( ABD_L::__( 'The operating system and web browser you are using.  Usually something like %sMicrosoft Windows and Google Chrome%s is enough. However, versions are helpful too.' ), '<em>', '</em>' ); ?></li>									
									<li><?php ABD_L::_e( 'What ad blocker is causing the problem? There are a lot of browser add-ons named AdBlock and similar, so be specific. A link to the product\'s website or listing in your web browsers add-on/extension repository is the most helpful.' ); ?></li>
									<li><?php ABD_L::_e( 'What is the name of the Block List Countermeasure Plugin directory? This information can be found on the Advanced Settings tab under the Block List Countermeasure Plugin section.' ); ?></li>
								</ul>
							</p>
						</div>




						<h4><?php ABD_L::_e( 'Automatic countermeasure plugin creation fails.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Disable plugin directory protection in any security plugins (e.g. iThemes Security), then try plugin creation again using controls in the Advanced Settings tab.' ); ?></li>
									<li><?php ABD_L::_e( 'Use checklist on the Advanced Settings tab to remedy common server configuration problems.' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Questions to answer in your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Do you use any security plugins?  If so, what are their names?'  ); ?></li>
									<li><?php ABD_L::_e( 'Have you implemented any non-standard server configurations or WordPress settings that might interfere with file manipulation?' ); ?></li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'The contents of the Session Log on this page, after attempting automatic plugin creation.' ); ?></li>
									<li><?php ABD_L::_e( 'A screenshot of the Block List Countermeasure Plugin section in the Advanced Settings tab.  Specifically, I\'m looking for the status checklist.  This screenshot can be a link to an uploaded copy of the image, or it can be attached to an email.' ); ?></li>
									<li><?php ABD_L::_e( 'Information about your web server. If you use a web hosting company, the company name, and perhaps the package name should be enough. Otherwise, as much operating system, PHP, web server software, and other information you have that might impact PHP file manipulation would be helpful.' ); ?></li>
								</ul>
							</p>
							<p>
								<em><?php ABD_L::_e( 'NOTE: This failure is almost always a server configuration problem. Behind the scenes, this plugin utilizes standard PHP file manipulation functions such as mkdir(), copy(), and scandir() to accomplish its job. The Session Log on this page will show the failed operation.' ); ?></em>
							</p>
						</div>
					</div><!-- end <div class='abd-accordion'> -->






					<br /><br />







					<strong><?php ABD_L::_e( 'Problems with the Manual Block List Countermeasure Plugin:' ); ?></strong>
					<p style="font-style: italic"><?php ABD_L::_e( 'The vast majority of Manual Block List Countermeasure Plugin problems are resolved by deleting the Block List Countermeasure Plugin, and reinstalling it. In addition, many of the problems described above regarding the Automatic Block List Countermeasure Plugin also apply to the manual plugin.' ); ?></p>

					<div class='abd-accordion'>
						<h4><?php ABD_L::_e( 'I\'m receiving error messages stating the countermeasure plugin is out-of-date or versions mismatch even though I have updated the countermeasure plugin.' ); ?></h4>
						<div>
							<p>
								<strong><?php ABD_L::_e( 'Try these fixes first:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'Deactivate and delete the countermeasure plugin, then redownload, reinstall, and reactivate it again.  Make sure the downloaded ZIP file name is not renamed in any way! Web browsers will often add a number to the end of a file if one already exists with that name, and that number will break the plugin.  The name will have three sections, separated by a dash, with no numbers or symbols other than dashes and periods.  For example: ' ); ?> scared-carpenter-manual.zip</li>
								</ul>

								<br />
								
								<strong><?php ABD_L::_e( 'Information to include with your support request:' ); ?></strong>
								<ul>
									<li><?php ABD_L::_e( 'The contents of the Session Log after completing the update instructions.' ); ?></li>
									<li><?php ABD_L::_e( 'The version number of Ad Blocking Detector (available on your installed plugins page).' ); ?></li>
									<li><?php ABD_L::_e( 'The version number of the countermeasure plugin (available on your installed plugins page).' ) ?></li>
								</ul>
							</p>
						</div>
					</div><!-- end <div class='abd-accordion'> -->

					<p>
						<strong><?php ABD_L::_e( 'Examples of Great Support Requests' ); ?>:</strong>
						<ul>
							<li><a class='abd-popup' title='Example Support Request' href="<?php echo ABD_ROOT_URL; ?>assets/support-request-samples/broken-click.txt"><?php ABD_L::_e( 'In the dashboard, when I click ___________, nothing happens.' ); ?></a></li>
							<li><a class='abd-popup' title='Example Support Request' href="<?php echo ABD_ROOT_URL; ?>assets/support-request-samples/shortcode-misbehavior.txt"><?php ABD_L::_e( 'My alternative content shortcode doesn\'t behave properly in the presence of an ad blocker.' ); ?></a></li>
						</ul>
					</p>



					<p>
						<?php ABD_L::_e( 'If your support request doesn\'t match one of the above guides, don\'t be shy.  Contact me anyway, and I\'ll walk you through gathering needed information.' ); ?>
					</p>		
				</div>




				<div class="abd-masonry-block abd-subtle-highlight">
					<h3><?php ABD_L::_e( 'Contact the Developer' ); ?></h3>

					<p>
						<?php echo sprintf( ABD_L::__( 'The best ways to get of the developer is through this plugin\'s %sWordPress.org support forum%s, or by %semail%s. Either contact method is acceptable; however, asking questions on the support forum, instead of privately by email, may help others with the same problem.' ), '<a href="' . self::$our_links['wporgsupport'] . '" target="_blank">', '</a>', '<a href="' . self::$our_links['emaildev'] . '">', '</a>' ); ?>
					</p>
					<p>
						<table>
							<tr>
								<th><?php ABD_L::_e( 'Developer Name' ); ?></th>
								<td>John Morris</td>
							</tr>
							<!-- <tr>
								<th><?php ABD_L::_e( 'Website' ); ?></th>
								<td><a href="<?php echo self::$our_links['devwebsite']; ?>" target="_blank"><?php echo self::$our_links['devwebsite']; ?></a></td>
							</tr> -->
							<tr>
								<th><?php ABD_L::_e( 'Email Address' ); ?></th>
								<td><a href="<?php echo self::$our_links['emaildev']; ?>">johntylermorris@jtmorris.net</a></td>
							</tr>
						</table>
					</p>
					<p>
						<?php ABD_L::_e( 'Please note I have several WordPress plugins, and other projects, live at any given time. I generally provide support on a first-come, first-served basis, with exceptions for critical plugin bugs.' ); ?>
					</p>
					<p>
						<?php ABD_L::_e( 'I will usually respond within forty-eight hours.  However, if I have a lot of queued support requests, it can take up to several weeks.' ); ?>
					</p>
				</div>




				<div class="abd-masonry-block" style="width: 100%;">
					<h3><?php ABD_L::_e( 'Session Log' ); ?></h3>

					<p>
						<?php ABD_L::_e( 'This plugin logs noteworthy actions and errors during usage of this dashboard. If an action isn\'t working correctly, try clearing this log using the button below, reattempting the action, then checking the log for information, or pass it to the developer in a bug report or support request.' ); ?>
					</p>
					<div><textarea style='width: 100% !important'>SESSION LOG&#13;&#10;============&#13;&#10;============&#13;&#10;&#13;&#10;<?php echo ABD_Log::get_readable_log(); ?></textarea></div>

					<p><strong><?php ABD_L::_e( 'Number of Log Entries:  ' ); ?></strong><?php echo count( ABD_Log::get_all_log_entries() ); ?></p>
					<!-- Space for Date and Time -->
					<p id="abd-js-date-time"><strong><?php ABD_L::_e( 'Current Date and Time: ' ); ?></strong></p>

					<!-- Clear log button -->
					<a href='<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=clear_log' ), 'user instructed deletion of all log entries' ); ?>' class='button'>
						<?php ABD_L::_e('Clear Log'); ?>
					</a>
				</div>








				<div class="abd-masonry-block">
					<h3><?php ABD_L::_e( 'Plugin, WordPress, and Server Configuration Data' ); ?></h3>

					<?php 
						//	Gather Data
						$blcp_status = array(
							'auto_plugin_activated'   => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_activated' ),
							'manual_plugin_activated' => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_activated' ),
							'auto_plugin_exists'      => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_exists' ),
							'manual_plugin_exists'    => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_exists' ),
							'plugin_activated'        => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_activated' ),
							'plugin_exists'           => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_exists' )
						);

						$blcdir = ABD_Anti_Adblock::get_bcc_plugin_dir_name();
						if( !$blcdir ) { $blcdir = 'No BLC Plugin Directory'; }

						$mem_usage = memory_get_usage( true );
						if( $mem_usage < 1024 ) { $mem_usage = $mem_usage . ' bytes'; }
						else if( $mem_usage < 1048576 ) { $mem_usage = round( $mem_usage/1024, 2 ) . ' KB'; }
						else { $mem_usage = round( $mem_usage/1048576, 2 ) . ' MB'; }
					?>

					<textarea id="abd-server-config-textarea">
ENVIRONMENT DATA&#13;&#10;==================&#13;&#10;==================&#13;&#10;
System: <?php echo php_uname(); ?>&#13;&#10;
PHP Version: <?php echo phpversion(); ?>&#13;&#10;
PHP/WordPress Memory Limit: <?php echo ini_get( 'memory_limit' ); ?>&#13;&#10;
Memory Used: <?php echo $mem_usage; ?>&#13;&#10;
PHP Max Execution Time: <?php echo ini_get( 'max_execution_time' ); ?>&#13;&#10;&#13;&#10;
WordPress Version: <?php echo get_bloginfo('version'); ?>&#13;&#10;
Total # of wp_options Entries: <?php echo ABD_Database::size_of_wp_options_table(); ?>&#13;&#10;
Plugin Version: <?php echo ABD_VERSION; ?>&#13;&#10;
Total # of shortcodes: <?php echo ABD_Database::count_shortcodes(); ?>&#13;&#10;
BLC Plugin Exists?: <?php echo $blcp_status['plugin_exists']; ?>&#13;&#10;
BLC Plugin Active?: <?php echo $blcp_statuc['plugin_activated']; ?>&#13;&#10;
BLC Plugin Type?: <?php echo get_option( 'abd_blc_plugin_type' ); ?>&#13;&#10;
BLC Plugin Dir: <?php echo $blcdir; ?>&#13;&#10;
					</textarea>
				</div>

				







				<div class="abd-masonry-block">
					<h3><?php ABD_L::_e( 'Ad Blocking Detection Results' ); ?></h3>

					<p>
						<?php ABD_L::_e( 'Turn on the ad blocker you wish to test, or that is causing a problem, and refresh this page. The textbox below contains a report of what was found.  Use this report as reference, and as information in bug reports and support requests.' ); ?>
					</p>
					<p>
						<?php echo sprintf( ABD_L::__( 'Also note that a similar log, often with more contextually relevant details, is output to your web browser\'s JavaScript console. This is useful if you want to see the detection results on a different page on your site, or in the presence of an advertisement or shortcode. You can find instructions for accessing the JavaScript console %son the plugin\'s website.%s' ), '<a href="' . self::$our_links['console'] . '" target="_blank">', '</a>' ); ?>
					</p>

					<textarea id="abd-results-textarea">DETECTION RESULTS&#13;&#10;==================&#13;&#10;==================&#13;&#10;&#13;&#10;</textarea>
				</div>



				



				<div class="abd-masonry-block">
					<h3><?php ABD_L::_e( 'Revert to An Old Version' ); ?></h3>
					<p><?php ABD_L::_e( 'I work very hard to make sure plugin updates do not break what used to work, or removes features you may rely on.  However, if I have failed in this regard, you can revert to previous versions of this plugin.  I have included download links to some stable and mature plugin versions below, and will continue to update this list.' ); ?></p>
					<p><?php echo sprintf( ABD_L::__( 'You can also download every released version of this plugin from its %sWordPress.org plugin repository%s if you want more choices.' ), '<a href="https://wordpress.org/plugins/ad-blocking-detector/developers/" target="_blank">', '</a>' ); ?></p>
					<p><em><?php ABD_L::_e( 'Be warned, reverting to one of these plugin versions will stop update notifications, and I only provide support for the latest releases.' ); ?></em></p>

					<ul>
						<li><?php echo sprintf( ABD_L::__( '%sVersion 2.2.8%s:  The last stable release of the version 2 branch.  It was stable and effective, however, it was vulnerable to ad blocker block lists, and had some minor user-interface issues.' ), '<a href="https://downloads.wordpress.org/plugin/ad-blocking-detector.2.2.8.zip">', '</a>' ); ?></li>
					</ul>
				</div>
			</div>

			<?php
			return ob_get_clean();
		}

		protected static function settings_tab_header() {
			ob_start();
			?>

			<h2><?php ABD_L::_e( 'Advanced Settings and Configuration' ); ?></h2>

			<?php

			echo self::block_list_countermeasure_plugin_section();			

			return ob_get_clean();
		}
		
		protected static function block_list_countermeasure_plugin_section() {
			$blcp_status = array(
				'auto_plugin_activated'   => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_activated' ),
				'manual_plugin_activated' => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_activated' ),
				'auto_plugin_exists'      => ABD_Anti_Adblock::bcc_plugin_status( 'auto_plugin_exists' ),
				'manual_plugin_exists'    => ABD_Anti_Adblock::bcc_plugin_status( 'manual_plugin_exists' ),
				'plugin_activated'        => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_activated' ),
				'plugin_exists'           => ABD_Anti_Adblock::bcc_plugin_status( 'plugin_exists' )
			);
			ob_start();
			?>

			<h3><?php ABD_L::_e( 'Block List Countermeasure Plugin' ); ?></h3>

			<!--Block List Plugin-->
			<div>
				<p>
					<strong><?php ABD_L::_e( 'The Problem:' ); ?></strong> &nbsp; 
					<?php ABD_L::_e( 'Several popular block lists in use by ad blocking plugins target crucial elements of this plugin. Restrictions in the installation procedure for WordPress.org listed plugins make targeting trivial for block list maintainers.' ); ?>
				</p>
				<p>
					<strong><?php ABD_L::_e( 'The Solution:' ); ?></strong> &nbsp; 
					<?php ABD_L::_e( 'This targeting can be foiled by ensuring the files used by Ad Blocking Detector are in a randomly named plugin directory. Unfortunately, simply changing the name of this plugin\'s directory will break automatic update monitoring through WordPress.org.  To maintain automatic update checking, this plugin can dynamically create a secondary support plugin that it will keep up to date.  This secondary plugin will have the necessary naming randomness to prevent easy targeting, and will not rely on WordPress.org for updates.' ); ?>
				</p>
				<p>
					<strong><?php ABD_L::_e( 'What You Need To Do:' ); ?></strong> &nbsp;
					<?php ABD_L::_e( 'Below is a checklist to get this plugin working. The plugin must exist and be activated. For most, this blocklist countermeasure plugin will already be created automatically.  All that remains is to activate it from your Installed Plugins list.' ); ?>
				</p>
				<p>
					<?php ABD_L::_e( 'If the automatic plugin does not yet exist, or was somehow deleted, use the controls beneath the checklist to reattmept the automated procedure.  There are also options to modify and delete this automatic plugin at a later date if necessary.' ); ?>
				</p>
				<p>
					<?php ABD_L::_e( 'If automatic plugin creation is failing, and you can\'t sufficiently alter your server and website settings to get it working, you will need to manually install this plugin, and follow any update instructions you see when Ad Blocking Detector is updated in the future. You can download the ZIP file using the "Download Manual Plugin" button.  This button is only showed if the automatic plugin has failed, because that is the preferred method.' ); ?>
				</p>
				<p>
					<strong style="font-size: 1.15em;">
						<a target="_blank" href="<?php echo self::$our_links['bccpluginintro']; ?>">
							<?php ABD_L::_e( 'Read This Article For More Information' ); ?>
						</a>
					</strong>
				</p>
				<strong><?php ABD_L::_e( 'Block List Countermeasure Plugin Status:' ); ?></strong><br />
				<ul>
					<?php
					//	Does fallback plugin exist
					if( $blcp_status['plugin_exists'] ) {
						$class = 'abd_success_message';
					}
					else if ( ABD_Anti_Adblock::check_php_version() < 0 ) {
						$class = 'abd_unknown_message';
					}
					else {
						$class = 'abd_failure_message';
					}
					?>
					<li class="<?php echo $class; ?>">
						<?php ABD_L::_e( 'The Block List Countermeasure Plugin exists?' ); ?>
						
						<br /><strong style="color: #444; "><?php ABD_L::_e( 'Automatic Plugin Status' ); ?>:</strong>
						<?php
						if( !$blcp_status['plugin_exists'] ) {
							?>
							<ul>
								<li class="abd_failure_message">
									<?php ABD_L::_e( 'Automatic plugin exists?' ); ?><br />
									<strong style="color: #444; "><?php ABD_L::_e( 'Common Failure Reasons' ); ?></strong>
									<ul>
										<?php
										//	Plugin directory writable check
										if( ABD_Anti_Adblock::check_plugin_dir() > 0 ) {
											$class = 'abd_success_message';
										}
										else if ( ABD_Anti_Adblock::check_plugin_dir() < 0 ) {
											$class = 'abd_unknown_message';
										}
										else {
											$class = 'abd_failure_message';
										}
										?>
										<li class="<?php echo $class; ?>">
											<?php ABD_L::_e( 'WordPress\' plugin directory is writable?' ); ?>
										</li>
										

										<?php
										//	Safe mode off check
										if( ABD_Anti_Adblock::check_safe_mode() > 0 ) {
											$class = 'abd_success_message';
										}
										else if ( ABD_Anti_Adblock::check_safe_mode() < 0 ) {
											$class = 'abd_unknown_message';
										}
										else {
											$class = 'abd_failure_message';
										}
										?>
										<li class="<?php echo $class; ?>">
											<?php ABD_L::_e( 'PHP\'s safe_mode is disabled?' ); ?>
										</li>
										

										<?php
										//	mkdir() check
										if( ABD_Anti_Adblock::check_mkdir() > 0 ) {
											$class = 'abd_success_message';
										}
										else if ( ABD_Anti_Adblock::check_mkdir() < 0 ) {
											$class = 'abd_unknown_message';
										}
										else {
											$class = 'abd_failure_message';
										}
										?>
										<li class="<?php echo $class; ?>">
											<?php ABD_L::_e( 'Programmatic directory creation works?' ); ?>
										</li>
										

										<?php
										//	PHP version check
										if( ABD_Anti_Adblock::check_php_version() > 0 ) {
											$class = 'abd_success_message';
										}
										else if ( ABD_Anti_Adblock::check_php_version() < 0 ) {
											$class = 'abd_unknown_message';
										}
										else {
											$class = 'abd_failure_message';
										}
										?>
										<li class="<?php echo $class; ?>">
											<?php ABD_L::_e( 'You are using the recommended PHP version of 5.3.0 or higher?' ); ?>
										</li>
									</ul>
								</li>
							</ul>
							<?php
						}
						else {
							?>
							<ul>
								<li class="abd_success_message"><?php ABD_L::_e( 'Automatic plugin exists?' ); ?></li>
								<li class="abd_success_message"><?php echo ABD_L::__( 'Plugin Directory' ) . ': ' . ABD_Anti_Adblock::get_bcc_plugin_dir_name(); ?></li>
							</ul>
							<?php
						}

						if( $blcp_status['manual_plugin_exists'] || !$blcp_status['auto_plugin_exists'] ) {
							//	Does fallback plugin exist
							if( $blcp_status['manual_plugin_exists'] ) {
								$class = 'abd_success_message';
							}
							else {
								$class = 'abd_failure_message';
							}
							?>

							<br /><strong style="color: #444; "><?php ABD_L::_e( 'Manual Plugin Status' ); ?>:</strong>
							<ul>
								<li class='<?php echo $class; ?>'><?php echo ABD_L::__( 'Manual plugin exists?' ); ?></li>

								<?php
								if( $blcp_status['manual_plugin_exists'] ) {
									?>
									<li class='abd_success_message'><?php echo ABD_L::__( 'Plugin Directory' ) . ': ' . ABD_Anti_Adblock::get_bcc_manual_plugin_dir_name(); ?></li>
									<?php
								}								
								?>
							</ul>
							<?php
						}
						?>
					</li>


					<?php
					//	Is fallback plugin activated
					if( $blcp_status['plugin_activated'] ) {
						$class = 'abd_success_message';
					}
					else if ( ABD_Anti_Adblock::check_php_version() < 0 ) {
						$class = 'abd_unknown_message';
					}
					else {
						$class = 'abd_failure_message';
					}
					?>
					<li class="<?php echo $class; ?>">
						<?php ABD_L::_e( 'The Block List Countermeasure Plugin is activated?' ); 

						if( $blcp_status['plugin_exists'] && !$blcp_status['plugin_activated'] ) {

							//	Plugin exists and is NOT activated
							?>
							<ul>
								<li class="abd_unknown_message">
									<?php echo sprintf( ABD_L::__( 'Go to your installed plugins list and activate the %s plugin' ), '<em>Ad Blocking Detector - Block List Countermeasure</em>' ); ?>
								</li>
							</ul>
							<?php
						} 

						?>
					</li>
					
					<?php
					//	Version Report
					if( defined( 'ABDBLC_VERSION' ) && defined( 'ABD_VERSION' ) ) {
						if( ABDBLC_VERSION == ABD_VERSION ) {
							$class = 'abd_success_message';
						}
						else {
							$class = 'abd_failure_message';
						}
						?>
						<li class='<?php echo $class; ?>'><?php echo sprintf( ABD_L::__( 'Main Plugin Version: %s, BLC Plugin Version: %s' ), ABD_VERSION, ABDBLC_VERSION ); ?></li>
						<?php
					}
					?>
				</ul>

				<div id='abd-fallback-plugin-controls'>
					<?php
					if( !$blcp_status['auto_plugin_exists'] ) {
						$style = 'button-primary';
					}
					else {
						$style = '';
					}
					?>
					<p>
						<strong><?php ABD_L::_e( 'Automatic Plugin Controls' ); ?></strong><br />
						<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=create_bcc_plugin' ), 'user instructed anti-adblock fallback plugin creation' ); ?>" class='abd-fallback-plugin-copy-button button <?php echo $style; ?>'>
							<?php ABD_L::_e( 'Automatically Install Plugin' ); ?>
						</a> 


						<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=reset_bcc_plugin_name' ), 'user instructed anti-adblock fallback plugin rename' ); ?>" id='abd-fallback-plugin-rename-button' class='button'>
							<?php ABD_L::_e( 'Reset Plugin Directory Name' ); ?>
						</a> 


						<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=delete_bcc_plugin' ), 'user instructed anti-adblock fallback plugin deletion' ); ?>" class='abd-fallback-plugin-delete-button button'>
							<?php ABD_L::_e( 'Delete Automatically Installed Plugin' ); ?>
						</a>
					</p>
					<?php
					if( !$blcp_status['auto_plugin_exists'] ) {
						?>
						<p>
							<strong><?php ABD_L::_e( 'Manual Plugin Controls' ); ?></strong><br />
							<a class="button abd-download-manual-blc-plugin-button" href="<?php echo ABD_Anti_Adblock::get_bcc_manual_plugin_url(); ?>"><?php ABD_L::_e( 'Download Manual Plugin' ); ?></a>

							<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=delete_manual_bcc_plugin' ), 'user instructed manual anti-adblock fallback plugin deletion' ); ?>" class='abd-fallback-plugin-delete-button button'>
								<?php ABD_L::_e( 'Try to Delete Manually Installed Plugin' ); ?>
							</a>						
						</p>
						<?php
					}
					?>
				</div>
			</div>

			<?php
			return ob_get_clean();
		}
			protected static function block_list_countermeasure_plugin_manual_instructions() {
				ob_start();
				?>
				
				<p><?php ABD_L::_e( 'WRITE THESE' ); ?></p>
				
				<?php
				return ob_get_clean();
			}


		protected static function add_new_basic_section_header() {
			return "";
		}
		protected static function add_new_advanced_section_header() {
			return ABD_L::__( 'Advanced shortcode settings description.' );
		}


		protected static function manage_shortcodes_shortcode_section_header( $sc_id, $readonly = false ) {
			ob_start();
			?>
			
			<div class="abd-shortcode-header">
				<div class="abd-shortcode-header-action-buttons">
					<a class="abd-shortcode-get-button button" data-id="<?php echo $sc_id; ?>">
						<?php ABD_L::_e( 'Get This Shortcode' ); ?>
					</a> &nbsp;

					<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=delete_shortcode&id=' . $sc_id ), 'user instructed shortcode delete id equals ' . $sc_id ); ?>" class="abd-shortcode-delete-button button">
						<?php ABD_L::_e( 'Delete This Shortcode' ); ?>
					</a>
				</div>
				<?php 
				if( $readonly ) {
					?>
					<p class='abd_failure_message' style="font-size: 1.2em">
						<?php ABD_L::_e( 'This shortcode utilizes deprecated features! It is kept only for backwards compatibility, and cannot be edited. Shortcodes that use deprecated features will be removed in a future release, so transition away from using them by creating a new shortcode.' ); ?>
					</p>
					<?php
				}
				?>
			</div>
			
			<?php
			return ob_get_clean();			
		}


		protected static function settings_user_defined_selectors_section_header() {
			if( defined( 'ABDBLC_ROOT_URL' ) ) {
				//	Then our plugin is loaded because it defines this constant.
				$prefix = ABDBLC_ROOT_URL;
			}
			else {
				$prefix = ABD_ROOT_URL;
			}

			ob_start();
			?>

			<p>
				<strong><?php ABD_L::_e( 'The Problem:' ); ?></strong> &nbsp; 

				<?php ABD_L::_e( 'Some privacy browser add-ons, such as Ghostery, also block advertisements. However, they accomplish this in a completely different manner than typical ad blockers, which makes them very difficult to detect.' ); ?>
			</p>
			<p>
				<?php ABD_L::_e( 'Traditional ad blockers can be fooled into hiding bait content because they parse your website for content that loosely resembles an ad.  This plugin can then look to see if the bait was taken.  Ghostery, and add-ons like it, don\'t fall for this because they do not search your page for ads.  Instead, they prevent "tracking" code used by ad providers from loading.  Ad blocking is a happy side effect of privacy add-ons.' ); ?>
			</p>

			<p>
				<strong><?php ABD_L::_e( 'The Solution:' ); ?></strong> &nbsp;

				<?php ABD_L::_e( 'The only way to detect these types of add-ons is to look for content that you know would be on the page, if the add-ons were not enabled. For the most common advertisement providers, this can be done by looking for a wrapping element around the advertisement that does not disappear when privacy extensions are enabled.  This has the added benefit of improving detection of other ad blocking add-ons.' ); ?>
			</p>
			<p>
				<strong><?php ABD_L::_e( 'What You Need To Do:' ); ?></strong> &nbsp;
				<?php ABD_L::_e( 'If detection of Ghostery, and other privacy add-ons is important to you, you must inform this plugin what the wrapping element around your advertisement is. The wrapping element is the first container that does not disappear when the add-on is disabled.  Find this wrapper using a web browser inspection tool and experimentation, and feed this plugin that element\'s most specific CSS selector.' ); ?>
			</p>
			<p>
				<?php ABD_L::_e( 'For example, a modern Google AdSense ad wraps the entire advertisement in an HTML &lt;ins&gt; tag with a class attribute of <em>adsbygoogle</em>.  If the ad is not blocked, this wrapper is filled with content.  If the ad is blocked, this wrapper is empty.  Click on the pictures below to expand them and see this in action.' ); ?>

				<br /><br />
				<table>
					<tr><th><?php ABD_L::_e( 'Ghostery Disabled' ); ?>:</th><th><?php ABD_L::_e( 'Ghostery Enabled' ); ?>:</th></tr>
					<tr>
						<td>
							<a href="<?php echo $prefix . 'assets/images/examples/user-defined-selectors-explanation.jpg'; ?>" class='abd-popup'>
								<img style="width: 200px;" src="<?php echo $prefix . 'assets/images/examples/user-defined-selectors-explanation.jpg'; ?>" />
							</a>
						</td>
						<td>
							<a href="<?php echo $prefix . 'assets/images/examples/user-defined-selectors-explanation-2.jpg'; ?>" class='abd-popup'>
								<img style="width: 200px;" src="<?php echo $prefix . 'assets/images/examples/user-defined-selectors-explanation-2.jpg'; ?>" />
							</a>
						</td>
					</tr>
				</table>
			</p>
			<p>
				<?php ABD_L::_e( 'Therefore, that &lt;ins&gt; tag is the first wrapping element.  The CSS selector for that element is <em>ins.adsbygoogle</em>.  This selector should then be fed into the textbox below.' ); ?>
			</p>
			<p>
				<?php ABD_L::_e( 'Use the field below to specify global wrapper selectors that will be checked everywhere, every time.  Use the User-Defined CSS Selectors field on individual shortcodes to specify wrappers used only when that shortcode exists on a page.' ); ?>
			</p>
			<p>
				<strong style="font-size: 1.15em;">
					<a target="_blank" href="<?php echo self::$our_links['userdefinedwrappers']; ?>">
						<?php ABD_L::_e( 'Read This Article For More Information and Examples' ); ?>
					</a>
				</strong>
			</p>

			<?php

			return ob_get_clean();
		}


		protected static function settings_disable_detection_method_section_header() {
			ob_start();
			?>

			<p>
				<strong><?php ABD_L::_e( 'The Problem:' ); ?></strong> &nbsp; 

				<?php ABD_L::_e( 'This plugin works by inserting bait for ad blockers into your site, then checking to see if the bait was blocked.  While great care was taken to minimize it, each insertion and check has a cost in site performance.  In addition, certain bait methods have been known to play havoc with certain WordPress themes, and some can lead to unwanted errors and web browser console output.' ); ?>
			</p>

			<p>
				<strong><?php ABD_L::_e( 'The Solution:' ); ?></strong> &nbsp;

				<?php ABD_L::_e( 'If a particular bait item and check are causing issues or cause unwanted performance problems, you can disable it using the controls below.  Please note that this may adversely affect ad blocker detection.  The JavaScript file and iframe methods, in particular, are the most reliable method of ad blocker detection.  I encourage you to leave these alone unless you have a good reason not to.' ); ?>
			</p>

			<?php

			return ob_get_clean();
		}


		protected static function settings_customize_iframe_section_header() {
			ob_start();
			?>

			<p>
				<?php ABD_L::_e( 'This plugin inserts bait content on your site. This bait content is detected by ad blockers, which then remove or hide it. This removal can the be detected. One of the bait items is an HTML iframe. This iframe is occasionally troublesome. To account for this, some of the iframe\'s behavior can be customized using the tools below.'); ?>
			</p>
			<p>
				<em>
					<?php ABD_L::_e( 'NOTE: If your site utilizes SSL encryption, the domain used in the iframe must match your website\'s domain, and must be called with https, or visitors will receive mixed content warnings. If your site does not use SSL encryption, the domain will not matter.' ); ?>	
				</em>
			</p>

			<?php
			return ob_get_clean();
		}


		protected static function settings_customize_log_section_header() {
			ob_start();
			?>

			<p>
				<?php  ABD_L::_e( 'This plugin keeps a running log of noteworth activity, errors, and debugging information to help the plugin users and the developer resolve problems. This log is viewable on the "Report a Problem / Debug" tab above. Use the settings below to customize log entries as desired.' ); ?>
			</p>

			<?php
			return ob_get_clean();
		}






		public static function cache_handler_validator( $OG, $input ) {
			//	Reset shortcode cache
			ABD_Log::info( 'Forcing shortcode cache update after new shortcode submission, or existing shortcode edit by deleting existing cache.' );
			ABD_Database::nuke_shortcode_cache();

			
			//	Add shortcode to shortcode list
			$scs = get_option( 'abd_list_of_shortcodes', array() );
			$scs[] = $OG->get_db_option_name();
			update_option( 'abd_list_of_shortcodes', $scs );
			ABD_Log::info( 'Adding ' . $OG->get_db_option_name() . ' to list of shortcodes.' );

			//	Run normal automatic validation and return results
			return $OG->default_validation_function( $input );
		}








		protected static function dev_sig() {
			ob_start();
			?>
			
			<p class="abd_dev_sig">
				<span>John Morris</span><br />
				============<br />
				WordPress Plugin Developer<br />
				<a href="<?php echo self::$our_links['devemail']; ?>">john@johnmorris.me</a> &nbsp; | &nbsp;
				<a href="<?php echo self::$our_links['devwebsite']; ?>" target="_blank"><?php echo self::$our_links['devwebsite']; ?></a>
			</p>

			<?php
			return ob_get_clean();		
		}

		public static function get_js_localization_array() {
			$jsarr =  array(
				'copyShortcodeDialogTitle'          => ABD_L::__( 'Copy and paste your shortcode...'  ),
				'copyShortcodeInstructions'         => ABD_L::__( 'Copy the text in the box below to your clipboard, then paste it into your content. Alternatively, use this plugin\'s sidebar widget.' ),
				'close'                             => ABD_L::__( 'Close' ),
				'nevermind'                         => ABD_L::__( 'No! Take me back.' ),
				'affirmative'                       => ABD_L::__( 'Yes! I\'m sure.' ),
				
				'deleteDialogTitle'                 => ABD_L::__( 'Are you sure?' ),
				'deleteDialogWarning'               => ABD_L::__( 'A deleted shortcode can not be recovered!' ),
				
				'updateManualPluginDownloadWarning' => sprintf( ABD_L::__( 'Before installing this updated plugin, delete the old version! Go to your installed plugins list, deactivate %s, then delete it.' ), ABD_L::__( 'Ad Blocking Detector - Block List Countermeasure' ) ),
				'updateManualPluginDownloadTitle'   => ABD_L::__( 'WARNING: Delete the old version before installing!' ),
				
				'downloadManualPluginWarning'       => sprintf( ABD_L::__( 'The manual plugin ZIP file is downloading now. More detailed instructions for installing this plugin are %savailable on the plugin\'s website%s.  If automatic plugin management is an option, I highly encourage you to utilize it. Manual management will require you to reinstall the plugin from time to time, and without careful management, subtle problems can occur.' ), '<a target="_blank" href="' . self::$our_links['bccpluginintro'] . '">', '</a>' ),
				'downloadManualPluginTitle'         => ABD_L::__( 'Beyond these hills there be dragons!' ),

				'idlingForceRefreshWarning'         => ABD_L::__( 'To function properly, this page reserves database names and space on loading. However, if the page idles for an extended period, that reservation is released and a lot of errors and problems will result unless you refresh this page. It appears this page has sat for too long and needs a refresh. Upon closing this dialog, the page should automatically reload. If it does not, reload this page before submitting a shortcode.' ),
				'idlingForceRefreshTitle'           => ABD_L::__( 'This page has been idling too long!' )
			);

			return $jsarr;
		}

		public static function plugin_update_news() {
			ob_start();
			?>
			<p>

			</p>
			<?php
			return ob_get_clean();
		}

		public static function v2_to_v3_migration_notice() {
			?>
			<div class='updated'>
				<h3><?php ABD_L::_e( 'Major Ad Blocking Detector Update' ); ?></h3>
				<p><?php ABD_L::_e( 'You have just finished installing a massive update to Ad Blocking Detector!  All of your existing shortcodes should have migrated automatically, and the plugin should continue to work as before.  However, there are a few new features you should be aware of.' ); ?></p>
				<ul style="list-style-type: disc; margin-left: 25px;">
					<li><?php echo sprintf( ABD_L::__( '%sBlock List Countermeasure%s:  Circumvent the ad blockers targeting this plugin using the Block List Countermeasure Plugin.' ), '<strong><a target="_blank" href="' . self::$our_links['bccpluginintro'] . '">', '</a></strong>' ); ?></li>
					<li><?php echo sprintf( ABD_L::__( '%sOptional Detection Improvements%s:  Improve the detection mechanism of this plugin by providing it the wrapping element around your ads. Facilitates the detection of ad blocking by privacy plugins like Ghostery.' ), '<strong><a target="_blank" href="' . self::$our_links['userdefinedwrappers'] . '">', '</a></strong>' ); ?></li>
					<li><?php echo sprintf( ABD_L::__( 'Performance Enhancements:  Disable unnecesary features to decrease website load time, increase detection speed, and more.' ), '<strong><a target="_blank" href="">', '</a></strong>' ); ?></li>
					<li><?php echo sprintf( ABD_L::__( '%sSupport Request Help%s:  Useful information for resolving plugin problems, or seeking support from the plugin developer.' ), '<strong><a href="' . admin_url( 'admin.php?page=ad-blocking-detector&tab=debug' ) . '">', '</a></strong>' ); ?></li>
					<li><?php echo sprintf( ABD_L::__( '%sRevamped User Interface%s:  A completely redesigned user-interface that better matches WordPress styling, eliminates longstanding issues, and will allow easier feature additions in the future.' ), '<strong><a href="' . admin_url( 'admin.php?page=ad-blocking-detector' ) . '">', '</a></strong>' ); ?></li>
				</ul>
				<p><?php ABD_L::_e( 'Visit the Ad Blocking Detector dashboard to check out these changes and take advantage of the new features!' ) ?></p>
			</div>

			<?php
		}

		public static function v1_to_v3_migration_notice() {
			?>
			<div class='error'>
				<h3><?php ABD_L::_e( 'Major Ad Blocking Detector Update' ); ?></h3>
				<p><?php ABD_L::_e( 'You have just finished installing a massive update to Ad Blocking Detector.  Unfortunately, it seems you haven\'t updated this plugin in some time, and this new version (version 3) is not backwards compatible with your old version (version 1).  You will need to recreate all shortcodes, and update all their occurrences on your site. I apologize for the inconvenience.' ); ?></p>
			</div>
			<?php
		}

		public static function manual_plugin_exists_notification() {
			?>
			<div class='error'>
				<h3><?php ABD_L::_e( 'Ad Blocking Detector Error' ); ?></h3>
				<p><?php ABD_L::_e( 'An attempt was made to automatically install and manage Ad Blocking Detector\'s Block List Countermeasure plugin.  However, a manual version of this Block List Countermeasure plugin already exists.  If you want Ad Blocking Detector to manage the Block List Countermeasure plugin automatically, delete the manual version of the plugin.' ); ?></p>
			</div>
			<?php
		}

		public static function update_manual_blcp_notice() {
			?>
			<div class='error'>
				<h3><?php ABD_L::_e( 'Ad Blocking Detector\'s Block List Countermeasure plugin needs updating!'  ); ?></h3>
				<p><?php ABD_L::_e( 'You have an out-of-date manually installed version of the Block List Countermeasure plugin.  You need to download the new version, deactivate and delete the old version, and install and activate the downloaded new version.' ); ?></p>
				<p><?php echo sprintf( ABD_L::__( 'Use the button below to download the new version. %sClick here%s for more detailed instructions.' ), '<a target="_blank" href="' . self::$our_links['manualpluginintro'] . '">', '</a>' ); ?></p>
				<p><a class="button button-primary abd-update-manual-download-button" href="<?php echo ABD_Anti_Adblock::get_bcc_manual_plugin_url( true ); ?>"><?php ABD_L::_e( 'Download Updated Manual Plugin' ); ?></a></p>
			</div>
			<?php
		}

		public static function deprecated_network_wide_shortcodes_notice() {
			?>
			<div class='update-nag'>
				<h3><?php ABD_L::_e( 'An Ad Blocking Detector feature you are using no longer exists.' ); ?></h3>
				<p><?php ABD_L::_e( 'Earlier versions of this plugins supported "Network Wide Shortcodes" which could be created in a multisite\'s Network Admin, and would be available for every site in the network.  Due to the complexity of this feature and technical limitations it imposed, this capability is no longer available.  All shortcode creation and management is done on a site by site basis now.' ); ?></p>
				<p><?php ABD_L::_e( 'All "Network Wide" shortcodes will still function for a limited time.  However, they are no longer editable, and all support will be removed in a future release.  You should change all occurrences of "Network Wide Shortcodes" on your sites to new shortcodes, and encourage any site administrators in your network to do the same to their sites.' ); ?> </p>
			</div>
			<?php
		}


		public static function rate_plugin_nag() {
			
		}
	}	//	end class
}	//	end if ( !class_exists( ...


//	Register the settings
//ABD_Admin_Views::wpsm_settings();