<?php
/**
 * This file containst the 'ABD_L' class definition which provides useful tools for
 * localization.
 */

if ( !class_exists( 'ABD_L' ) ) {
	class ABD_L {
		protected static $our_text_domain = 'ad-blocking-detector'; // Must match slug of plugin



		/**
		 * Alias for WordPress translation function __() that automatically 
		 * includes text domain parameter.
		 */
		public static function __( $str ) {
			if( empty( $str ) ) {
				wp_die( 'Can not have empty localization string!' );
			}

			return __( $str, self::d() );
		}
		/**
		 * Alias for WordPress translation function _e() that automatically 
		 * includes text domain parameter.
		 */
		public static function _e( $str ) {
			if( empty( $str ) ) {
				wp_die( 'Can not have empty localization string!' );
			}

			_e( $str, self::d() );
		}
		/**
		 * Alias for WordPress translation function _n() that automatically 
		 * includes text domain parameter.
		 */
		public static function _n( $single, $plural, $number ) {
			return _n( $single, $plural, $number, self::d() );
		}
		/**
		 * Alias for WordPress translation function _nx() that automatically 
		 * includes text domain parameter.
		 */
		public static function _nx( $single, $plural, $number, $context ) {
			return _nx( $single, $plural, $number, $context, self::d() );
		}
		/**
		 * Alias for WordPress translation function _x() that automatically 
		 * includes text domain parameter.
		 */
		public static function _x( $text, $context ) {
			return _x( $text, $context, self::d() );
		}
		/**
		 * Alias for WordPress translation function _ex() that automatically 
		 * includes text domain parameter.
		 */
		public static function _ex( $text, $context ) {
			return _ex( $text, $context, self::d() );
		}

		/**
		 * Gets the plugin's text-domain. Used in 2nd parameter of translation functions.
		 *
		 * @return   string   The text domain.
		 */
		public static function get_domain() {
			return self::$our_text_domain;
		}
			/**
			 * A short alias for ABD_L::get_domain(). Easier to write.
			 *
			 * @return   string   The plugin's text-domain.
			 */
			public static function d() {
				return self::get_domain();
			}
	}	//end class ABD_L
}	// end if( !class_exists( ...