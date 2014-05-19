<?php
/**
 * This file contains the class definition for a multisite abstraction
 * class... because multisite is a pain-in-the-ass
 */

if( !session_id() ) {
	session_start();
}

			


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

		


		/////////////////////////////////
		/// Workflow Easing Functions ///
		/////////////////////////////////

		/**
		 * Occasionally, such as with AJAX action PHP files, the current multisite
		 * status functions don't respond appropriately. If this happens, it would
		 * be useful to cache the value from a previous page/step and then retrieve
		 * it later, when appropriate.  This function caches the context in a
		 * $_SESSION variable for later retrieval using get_current_context()
		 */
		public static function set_current_context() {
			$_SESSION['abd_multisite_data'] = array(
				'is_in_network_admin' => self::is_in_network_admin(),
				'is_this_a_multisite' => self::is_this_a_multisite(),
				'is_plugin_active_network_wide' => self::is_plugin_active_network_wide(),

				'current_blog_id' => self::get_current_blog_id()
			);
		}

		/**
		 * Looks for a previously cached multisite context variable (see 
		 * set_current_context()), and returns an associative array with
		 * each key equal to the corresponding function name from this 
		 * class.  If no context was cached, then it returns the current 
		 * context in the ARRAY_A form.
		 * 
		 * Example:
		 * 	returnedarray['is_in_network_admin'] is the cached value of 
		 * 		ABD_Multisite::is_in_network_admin()
		 *
		 * @param boolean refresh Whether to reset the cache with current user
		 * context data first.
		 * @return ARRAY_A An associative array containing the current context.
		 */
		public static function get_current_context( $refresh = false ) {
			if ( $refresh || !array_key_exists( 'abd_multisite_data', $_SESSION ) ) {
				//	No context available or we want to update it... let's set it 
				//	and try again.
				self::set_current_context();

				return self::get_current_context();
			}

			//	Okay, then we have the context
			return $_SESSION['abd_multisite_data'];
		}



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
	}	//	end class
}	//	end if (  !class_exists( ...