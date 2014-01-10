<?php
require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );

if( !class_exists( 'ABD_Hooks' ) ) {
	class ABD_Hooks {
		public function __construct() {
			register_activation_hook( ABD_PLUGIN_FILE, array( 'ABD_Hooks', 'activation' ) );
			register_deactivation_hook (ABD_PLUGIN_FILE, array( 'ABD_Hooks', 'deactivation' ) );
		}

		public static function activation() {
			global $wpdb;

			//	Needed for dbDelta
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$tableCreate = array();
			$shortcodeAdditions = array();
			$settingAdditions = array();

			$tableCreate[] = "CREATE TABLE " . ABD_Db_Manip::get_shortcode_table_name() . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name text NOT NULL,
				noadblock text NOT NULL,
				adblock text NOT NULL,
				PRIMARY KEY (id)
			);";

			//	Default - 300px x 250px
			$shortcodeAdditions[] = array( 
				'name' => 'Default - 300px x 250px',
				'noadblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 300px; height: 250px"><p><b>YOUR AD HERE!</b></p><p>Go to the Ad Block Detector plugin\'s settings page to insert your ad code.</p></div>',
				'adblock' => '<a target="_blank" href="http://adblockingdetector.jtmorris.net/"><img src="http://adblockingdetector.jtmorris.net/wp-content/uploads/2014/01/abd_default-300250.png" alt="You are using an ad blocker! Please consider disabling it to help us out." /></a>'
			);
			//	Default - 336px x 280px			
			$shortcodeAdditions[] = array( 
				'name' => 'Default - 336px x 280px',
				'noadblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 336px; height: 280px"><p><b>YOUR AD HERE!</b></p><p>Go to the Ad Block Detector plugin\'s settings page to insert your ad code.</p></div>',
				'adblock' => '<a target="_blank" href="http://adblockingdetector.jtmorris.net/"><img src="http://adblockingdetector.jtmorris.net/wp-content/uploads/2014/01/abd_default-336280.png" alt="You are using an ad blocker! Please consider disabling it to help us out." /></a>'
			);
			//	Default - 728px x 90px			
			$shortcodeAdditions[] = array( 
				'name' => 'Default - 728px x 90px',
				'noadblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 728px; height: 90px"><p><b>YOUR AD HERE!</b></p><p>Go to the Ad Block Detector plugin\'s settings page to insert your ad code.</p></div>',
				'adblock' => '<a target="_blank" href="http://adblockingdetector.jtmorris.net/"><img src="http://adblockingdetector.jtmorris.net/wp-content/uploads/2014/01/abd_default-72890.png" alt="You are using an ad blocker! Please consider disabling it to help us out." /></a>'
			);
			//	Default - 160px x 600px			
			$shortcodeAdditions[] = array( 
				'name' => 'Default - 160px x 600px',
				'noadblock' => '<div style="text-align: center; padding: 10px;border-radius: 15px; background-color: #585858; color: #FFFFFF; width: 160px; height: 600px"><p><b>YOUR AD HERE!</b></p><p>Go to the Ad Block Detector plugin\'s settings page to insert your ad code.</p></div>',
				'adblock' => '<a target="_blank" href="http://adblockingdetector.jtmorris.net/"><img src="http://adblockingdetector.jtmorris.net/wp-content/uploads/2014/01/abd_default-160600.png" alt="You are using an ad blocker! Please consider disabling it to help us out." /></a>'
			);
			
			foreach ($tableCreate as $sql) {
				dbDelta($sql);	
			}
			
			foreach ($shortcodeAdditions as $data) {
				ABD_Db_Manip::insert_shortcode( $data );
			}
		}

		public static function deactivation() {
			global $wpdb;

			$sql = "DROP TABLE IF EXISTS " . ABD_Db_Manip::get_shortcode_table_name() . ';';

			$wpdb->query($sql);
		}
	}	//	end class ABD_Hooks

	//	instantiate class to register the hooks
	new ABD_Hooks();
}	//	end if( !class_exists(