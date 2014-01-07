<?php

require_once ( ABD_ROOT_PATH . 'includes/admin-page.php' );

if ( !class_exists( 'ABD_Admin_Menu' ) ) {
	class ABD_Admin_Menu {
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'declare_menus' ) );
		}


		public function declare_menus() {
			add_menu_page( 'Adblock', 'Adblock', 'administrator', 'adblock-detector', array( $this, 'navigate' ) );			
		}

		public function navigate() {
			ABD_Admin_Pages::navigate();
		}
	}	//	end class AM_Admin_Menu


	//	Instantiate the object to setup the menu
	if ( class_exists( 'ABD_Admin_Menu' ) ) {
		new ABD_Admin_Menu();
	}
}	//	end if ( !class_exists( ...