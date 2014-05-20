<?php
/**
 * This file contains a static-esque class with all necessary database 
 * manipulation functions.  
 */

//	Need is_plugin_active_for_network()
include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
require_once( ABD_ROOT_PATH . 'includes/multisite.php' );

if ( !class_exists( 'ABD_Database' ) ) {
	class ABD_Database {
		/**
		 * Get the database table name for ABD.
		 * @return string The table name.
		 */
		public static function get_table_name() {
			global $wpdb;

			//	Okay, this table is network wide, not blog/site specific. 
			//	Therefore, we want the network table prefix, not the specific 
			//	blog/site prefix. This is $wpdb->base_prefix. 
			//	$wpdb->prefix is blog/site specific.
			$prefix = $wpdb->base_prefix;

			return $prefix . 'abd_shortcodes';
		}

		/**
		 * Most functions will require some form of WHERE clause in the SQL query.
		 * The WHERE will depend on the context. If we have a multisite, and the
		 * user is operating network wide, we want everything network wide, but
		 * not tied to a specific blog_id. If we have multisite, and the user is
		 * in a specific blog context, we want everything blog specific, and 
		 * everything network wide.  If no multisite, then we want network wide.
		 *
		 * @param boolean $cached_context Whether to use cached user context info,
		 * or fresh user context info. Cached context is useful for AJAX calls 
		 * where the calling page is an AJAX handler, not the user's page.  For
		 * info on cached contexts, see the includes/multisite.php file.
		 * 
		 * @return string          String containing some conditions for a WHERE
		 * clause. (e.g. "blog_id=1 OR network_wide<>0")
		 * @return boolean	FALSE if no multisite conditions are needed.
		 */
		protected static function multisite_where_conditions( $cached_context = false ) {
			global $wpdb;

			//	Let's get the current or cached context as needed.
			//	If $cached_context = false, then we want to get the most recent,
			//	which means passing true to get_current_context.  If $cached_context
			//	is true, then we want the cached context, which means passing false
			//	to get_current context... or, in other words, passing the inverse
			//	of $cached_context.
			$context = ABD_Multisite::get_current_context( !$cached_context );

			//	Is this a multisite?
			if ( $context['is_this_a_multisite'] ) {
				//	Is the plugin active network wide?
				if ( $context['is_plugin_active_network_wide'] ) {
					// Is management being done from within the network admin
					// page?
					if ( $context['is_in_network_admin'] ) {
						//	all shortcodes that do not have a specific blog,
						//	and all shortcodes that are network wide.
						return "(blog_id<0 OR blog_id=NULL OR network_wide<>0)";
					}
					//	Network wide, but in specific blog/site admin
					else {
						//	all shortcodes that match current blog ID, and all
						//	network wide shortcodes
						return "(blog_id=" . $wpdb->blogid . 
							" OR network_wide<>0)";
					}
				}
				//	Is not network wide, only blog/site active
				else {
					//	all shortcodes taht match current blog ID
					return "blog_id=" . $wpdb->blogid;
				}
			}
			//	Not a multisite network
			else {
				// No conditions
				return false;
			}
		}

		/**
		 * Retrieves all shortcodes from the database and returns them.
		 * @param  boolean $sort_by_network_wide If true, output is sorted by
		 * network_wide column first, shortcode creation order second.
		 * @param boolean $cached_context Whether to use cached user context info,
		 * or fresh user context info. Cached context is useful for AJAX calls 
		 * where the calling page is an AJAX handler, not the user's page.  For
		 * info on cached contexts, see the includes/multisite.php file.
		 * @return ARRAY_A  A numerical array of associative arrays.
		 * Each first dimension element is a row from the table. Each second
		 * dimension element is a column from the table (key = column name, 
		 * value = value in column)
		 * @return NULL 	If nothing was found or query failes, NULL is 
		 * returned
		 */
		public static function get_all_shortcodes( $sort_by_network_wide = true, $cached_context = false ) {
			global $wpdb;

			$where = self::multisite_where_conditions( $cached_context );
			
			$sql = "SELECT * FROM " . self::get_table_name();

			//	Add the WHERE clause if necessary
			if ( !empty( $where ) && $where !== false ) {
				$sql .= " WHERE " . $where;
			}

			//	If we need to order it, do so
			if ( $sort_by_network_wide ) {
				$sql .= " ORDER BY network_wide DESC, id ASC";
			}

			//	Get the results as an associative array and return them
			return $wpdb->get_results( $sql, ARRAY_A );
		}

		/**
		 * Retrieves a single row from the shortcode table.
		 * @param  int $id The ID# of the row wanted. Used in a WHERE clause
		 * of the SELECT query.
		 * @return ARRAY_A An associative array where each entry 
		 * represents the column (column_name=>column_value)
		 * @return NULL If nothing was found, user doesn't have permission, or 
		 * query fails in any way, NULL is returned.
		 */
		public static function get_shortcode_by_id( $id, $cached_context = false ) {
			global $wpdb;

			$where = self::multisite_where_conditions( $cached_context );

			if ( !empty( $where ) && $where !== false ) {
				$where .= " AND id=" . $id;
			}
			else {
				$where = " id=" . $id;
			}

			$sql = "SELECT * FROM " . self::get_table_name();
			$sql .= " WHERE " . $where;
			$sql .= " LIMIT 1";

			$retval = $wpdb->get_row($sql, ARRAY_A);


			//	Does the user have permission to see this particular entry?
			//	Permission in this context means a multisite within
			//	the site/blog owning the shortcode, or a singlesite.		
			//	If so, return it.  Otherwise, return NULL.
			if ( ABD_Multisite::is_this_a_multisite() ) {
				if ( $retval['blog_id'] != $wpdb->blogid && 
					$retval['blog_id'] != null ) {

					return NULL;
				}
			}

			return $retval;
		}

		/**
		 * Removes a row from shortcode table
		 * @param  int $id The ID# of the row wanted. Used in a WHERE clause
		 * of the DELETE query.
		 * @return int The number of rows altered
		 * @return bool FALSE if operation failed in any way, such as a lack of 
		 * permission.
		 */
		public static function delete_shortcode_by_id( $id ) {
			global $wpdb;

			//	Hold up! Does the user have permission to delete this entry?
			//	The easiest way to check this is to get_shortcode_by_id. It should
			//	not return it if something goes wrong, like lack of permission.
			$sc = self::get_shortcode_by_id( $id );
			if ( !empty( $sc ) ) {
				//	Okay, we have permission, let's delete the row and return
				//	the appropriate response.
				return $wpdb->delete( 
					self::get_table_name(), 
					array( 'id' => $id ) 
				);
			}
			else {
				return false;
			}
		}

		/**
		 * Updates values for an entry in the shortcode table
		 * @param  int $id The ID# of the row to alter. Used in WHERE clause of 
		 * SQL UPDATE query (WHERE id=$id)
		 * @param  ARRAY_A $data An associative array where each entry 
		 * represents a column (column_name=>column_value)
		 * @return int The number of rows altered
		 * @return bool FALSE if operation failed in any way, such as lack 
		 * permission.
		 */
		public static function update_shortcode_by_id( $id, $data ) {
			global $wpdb;

			//	Hold up! Does the user have permission to update this entry?
			//	The easiest way to check this is to get_shortcode_by_id. It should
			//	not return it if something goes wrong, like lack of permission.
			$sc = self::get_shortcode_by_id( $id );
			if ( !empty( $sc ) ) {
				//	Okay, we have permission

				//	Now, do we need to run the wpautop function on anything?
				$data = self::wpautop( $data );

				// Llet's update the row and return the appropriate response.
				return $wpdb->update( 
					self::get_table_name(), 
					$data, 
					array( 'id' => $id ) 
				);
			}
			else {
				return false;
			}
		}

		/**
		 * Inserts a new row in the shortcode table. blog_id and network_wide is 
		 * automatically input if appropriate and not already defined in $data.
		 * @param  ARRAY_A $data An associative array where each entry 
		 * represents a column (column_name=>column_value)
		 * @param boolean $cached_context Whether to use cached user context info,
		 * or fresh user context info. Cached context is useful for AJAX calls 
		 * where the calling page is an AJAX handler, not the user's page.  For
		 * info on cached contexts, see the includes/multisite.php file.
		 * @return bool       FALSE if insertion failed.
		 * @return ARRAY_A Associative array with two elements
		 *                             'id'=>The ID# of the new row
		 *                             'res'=>The actual value returned by 
		 *                             $wpdb->insert
		 */
		public static function insert_shortcode( $data, $cached_context = false ) {
			global $wpdb;

			//	Let's get the current or cached context as needed.
			//	If $cached_context = false, then we want to get the most recent,
			//	which means passing true to get_current_context.  If $cached_context
			//	is true, then we want the cached context, which means passing false
			//	to get_current context... or, in other words, passing the inverse
			//	of $cached_context.
			$context = ABD_Multisite::get_current_context( !$cached_context );

			//	Is this being done in the network admin?
			//	If so, this is network wide. Set the appropriate values
			//	in $data if they're not already set.
			if ( $context['is_in_network_admin'] && 
				!array_key_exists('network_wide', $data) ) {
				
				$data['network_wide'] = true;
			}
			//	Okay, not network wide admin... If this is multisite, then we
			//	must be on site/blog specific page. Set the appropriate values
			//	in $data if they're not already set.
			else if ( $context['is_this_a_multisite'] && 
				!array_key_exists( 'blog_id', $data ) ) {
				
				$data['blog_id'] = $wpdb->blogid;
			}

			//	Now, do we need to run the wpautop function on anything?
			$data = self::wpautop( $data );

			//	Okay, now insert the values and return appropriate response
			$res = $wpdb->insert( self::get_table_name(), $data );

			if ( $res ) {
				return array( 'id'=>$wpdb->insert_id, 'res'=>$res );
			}
			else {
				return false;
			}
		}

		/**
		 * Checks for set flags in a data array and appropriately decides
		 * whether to run wpautop function on the adblock and noadblock fields
		 * in the data array.
		 * @param  ARRAY_A $data The data array
		 * @return ARRAY_A       The data array after processing.
		 */
		protected static function wpautop ( $data ) {
			if ( array_key_exists( 'wpautop_adblock', $data ) &&
			 $data['wpautop_adblock'] ) {
			 	$data['adblock'] = wpautop( $data['adblock'] );
			}

			if ( array_key_exists( 'wpautop_noadblock', $data ) &&
				$data['wpautop_noadblock'] ) {

				$data['noadblock'] = wpautop( $data['noadblock'] );
			}

			//	We don't need those flags anymore.
			unset($data['wpautop_adblock']);
			unset($data['wpautop_noadblock']);

			return $data;
		}
	}	//	end class
}	// end if (!class_exists(...