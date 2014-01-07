<?php
	if ( !class_exists( 'ABD_Enqueue' ) ) {
		class ABD_Enqueue {
			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'css' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'regular_js' ) );
			}

			public static function css() {
				wp_register_style( 'abd-admin-stylesheet', ABD_ROOT_URL . 'css/admin.css' );
				wp_enqueue_style( 'abd-admin-stylesheet' );
			}

			public static function admin_js() {
				wp_enqueue_script( 'abd-admin-page', ABD_ROOT_URL . 'js/admin-page.js' );
				wp_enqueue_script( 'abd-adblock-detector', ABD_ROOT_URL . 'js/adblock-detector.js' );
			}

			public static function regular_js() {	
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'abd-adblock-detector', ABD_ROOT_URL . 'js/adblock-detector.js' );
				wp_enqueue_script( 'abd-run-on-load', ABD_ROOT_URL . 'js/runonload.js' );
			}
		}	//	end class ABD_Enqueue

		//	run constructor which sets everything in motion
		new ABD_Enqueue();
	}	//	end if ( !class_exists
?>