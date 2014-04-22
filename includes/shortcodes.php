<?php
require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );

if( !class_exists( 'ABD_Shortcodes' ) ) {
	class ABD_Shortcodes {
		public function __construct() {
			add_shortcode( 'adblockdetector', array( $this, 'adblockdetector_func' ) );
		}		

		public static function adblockdetector_func ( $atts ) {
			extract( shortcode_atts( array( 
				'id' => null
			 ), $atts ) );

			//	Get database value with that shortcode
			$res = ABD_Db_Manip::get_shortcode_by_id( $id );

			//	Process shortcodes within the shortcode
			$noab = do_shortcode( $res['noadblock'] );
			$ab = do_shortcode( $res['adblock'] );

			if ( $res ) {
				$retval = '<div class="ABD_display ABD_display_noadblock">' . $noab . '</div>';
				$retval .= '<div class="ABD_display ABD_display_adblock" style="display: none;">' . $ab . '</div>';

				return $retval;
			}
		}
	}	//	end class ABD_Shortcodes	

	//	instantiate class to register the hooks
	new ABD_Shortcodes();
}	//	end if( !class_exists(