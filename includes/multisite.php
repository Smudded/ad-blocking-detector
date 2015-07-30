<?php
/**
 * This file contains the class definition for a multisite abstraction
 * class... because multisite is a pain-in-the-ass
 */


//	Need is_plugin_active_for_network()
include_once( ABSPATH . '/wp-admin/includes/plugin.php' );

if ( !class_exists( 'ABD_Multisite' ) ) {
	/**
	 * WordPress multisite is a confusing clusterfuck of functions, hooks,
	 * and design challenges.  The purpose of this class is to provide some
	 * sane functions, properties, and workflows to clear things up a bit.	 *
	 */
	class ABD_Multisite {
		//	Okay, first of all, let's create a library of useful articles and
		//	links.
		//
		//	WordPress MultiSite Category
		//		http://codex.wordpress.org/Category:WPMS
		//			Contains every page related to WPMS including FUNCTION
		//			REFERENCES
		//
		//	Writing a Plugin for WordPress Multisite (considerations article)
		//		http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site
		//			Contains a few good plugin design considerations. Far from
		//			comprehensive, but an okay starting place.
		//
		//	How To Properly Code Your Plugin FOr A WordPress Multisite
		//		http://www.onextrapixel.com/2013/01/08/how-to-properly-code-your-plugin-for-a-wordpress-multisite/
		//			Another article with plugin design considerations and a few
		//			good examples.  Still not comprehensive, but better than nothing.


		////////////////////////////////////////////
		/// Abstraction Functions and Properties ///
		////////////////////////////////////////////

		/**
		 * Used in add_action calls for code to run after a new multisite blog
		 * is created.
		 * http://codex.wordpress.org/Function_Reference/wpmu_create_blog
		 * @var string
		 */
		public static $blog_create_hook = "wpmu_create_blog";

		/**
		 * Used in add_action calls for code to run after a multisite blog
		 * is deleted. http://codex.wordpress.org/Function_Reference/wpmu_delete_blog
		 * @var string
		 */
		public static $blog_delete_hook = "wpmu_delete_blog";


		/**
		 * Determines whether the current WordPress website is configured as as
		 * multisite.
		 */
		public static function is_this_a_multisite() {
			if ( !function_exists( 'is_multisite' ) ) {
				return false;
			}

			return is_multisite();
		}

		/**
		 * Determines whether the plugin is activated network wide (Network Activation)
		 * or if it is site/blog specific.
		 */
		public static function is_plugin_active_network_wide() {
			if ( !function_exists( 'is_plugin_active_for_network' ) ) {
				return false;
			}

			return is_plugin_active_for_network( ABD_SUBDIR_AND_FILE );
		}

		/**
		 * Determines whether the user is in the network admin dashboard or not.
		 */
		public static function is_in_network_admin() {
			if ( !function_exists( 'is_network_admin' ) ) {
				return false;
			}

			return is_network_admin();
		}


		/**
		 * Gets ID of currently active site/blog.
		 */
		public static function get_current_blog_id() {
			if ( self::is_this_a_multisite() ) {
				global $wpdb;

				return $wpdb->blogid;
			}

			return 1;
		}


		/**
		 * Wrappers around WordPress multisite functions add_blog_option(), update_blog_option(),
		 * delete_blog_option(), and get_blog_option that don't die miserably if the user isn't
		 * using Multisite.  These functions call the non _blog_ versions of those functions
		 * if they don't exist.  For example, get_blog_option() will call get_option() if 
		 * get_blog_option() is not defined.
		 */
		public static function update_blog_option( $id, $option, $value ) {
			if( function_exists( 'update_blog_option' ) ) {
				return update_blog_option( $id, $option, $value );
			}
			return update_option( $option, $value );
		}
		public static function get_blog_option( $id, $option, $default=false ) {
			if( function_exists( 'get_blog_option' ) ) {
				return get_blog_option( $id, $option, $default );
			}
			return get_option( $option, $default );
		}
		public static function add_blog_option( $id, $option, $value ) {
			if( function_exists( 'add_blog_option' ) ) {
				return add_blog_option( $id, $option, $default );
			}
			return add_option( $option, $default );
		}
		public static function delete_blog_option( $id, $option ) {
			if( function_exists( 'delete_blog_option' ) ) {
				return delete_blog_option( $id, $option );
			}
			return delete_option( $option );
		}
	}	//	end class
}	//	end if (  !class_exists( ...
