<?php
/**
 * This file contains a static-esque class with all necessary database 
 * manipulation functions.  
 */

if ( !class_exists( 'ABD_Database' ) ) {
	class ABD_Database {
		/**
		 * Get the database table name for ABD.
		 * @return string The table name.
		 */
		protected static function get_table_name() {
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
		 * @return string          String containing some conditions for a WHERE
		 * clause. (e.g. "blog_id=1 OR network_wide<>0")
		 * @return boolean	FALSE if no multisite conditions are needed.
		 */
		protected static function multisite_where_conditions( ) {
			global $wpdb;

			//	Is this a multisite?
			if ( function_exists('is_multisite') && is_multisite() ) {
				//	Is the plugin active network wide?
				if ( function_exists( 'is_plugin_active_for_network' ) &&
						is_plugin_active_for_network() ) {
					// Is management being done from within the network admin
					// page?
					if ( function_exists( 'is_network_admin' ) && 
							is_network_admin() ) {
						//	all shortcodes that do not have a specific blog,
						//	and all shortcodes that are network wide.
						return "blog_id<0 OR network_wide<>0";
					}
					//	Network wide, but in specific blog/site admin
					else {
						//	all shortcodes that match current blog ID, and all
						//	network wide shortcodes
						return "blog_id=" . $wpdb->$blogid . 
							" OR network_wide<>0";
					}
				}
				//	Is not network wide, only blog/site active
				else {
					//	all shortcodes taht match current blog ID
					return "blog_id=" . $wpdb->$blogid;
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
		 * @return ARRAY_A  A numerical array of associative arrays.
		 * Each first dimension element is a row from the table. Each second
		 * dimension element is a column from the table (key = column name, 
		 * value = value in column)
		 * @return NULL 	If nothing was found or query failes, NULL is 
		 * returned
		 */
		public static function get_all_shortcodes( $sort_by_network_wide = true ) {
			global $wpdb;

			$where = self::multisite_where_conditions();
			
			$sql = "SELECT * FROM " . self::get_table_name();

			//	Add the WHERE clause if necessary
			if ( !empty( $where ) ) {
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
		public static function get_shortcode_by_id( $id ) {
			global $wpdb;

			$where = "id=" . $id;

			$sql = "SELECT * FROM " . self::get_shortcode_table_name() . 
				" WHERE " . $where . " LIMIT 1";

			$retval = $wpdb->get_row($sql, ARRAY_A);


			//	Does the user have permission to see this particular entry?
			//	Permission in this context means either superadmin or within
			//	the site/blog owning the shortcode.			
			//	If so, return it.  Otherwise, return NULL.
			if ( $retval['blog_id'] != $wpdb->$blogid && !is_super_admin() ) {
				return NULL;
			}
			else {
				return $retval;
			}
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
			if ( !empty( self::get_shortcode_by_id( $id ) ) ) {
				//	Okay, we have permission, let's delete the row and return
				//	the appropriate response.
				return $wpdb->delete( 
					self::get_shortcode_table_name(), 
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
			if ( !empty( self::get_shortcode_by_id( $id ) ) ) {
				//	Okay, we have permission, let's update the row and return
				//	the appropriate response.
				return $wpdb->update( 
					self::get_shortcode_table_name(), 
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
		 * epresents a column (column_name=>column_value)
		 * @return bool       FALSE if insertion failed.
		 * @return ARRAY_A Associative array with two elements
		 *                             'id'=>The ID# of the new row
		 *                             'res'=>The actual value returned by 
		 *                             $wpdb->insert
		 */
		public static function insert_shortcode( $data ) {
			global $wpdb;

			//	Is this being done in the network admin?
			//	If so, this is network wide. Set the appropriate values
			//	in $data if they're not already set.
			if ( is_network_admin() && 
				!array_key_exists('network_wide', $data) ) {
				
				$data['network_wide'] = true;
			}
			//	Okay, not network wide admin... If this is multisite, then we
			//	must be on site/blog specific page. Set the appropriate values
			//	in $data if they're not already set.
			else if ( is_multisite() && 
				!array_key_exists( 'blog_id', $data ) ) {
				
				$data['blog_id'] == $wpdb->$blogid;
			}


			//	Okay, now insert the values and return appropriate response
			$res = $wpdb->insert( self::get_shortcode_table_name(), $data );

			if ( $res ) {
				return array( 'id'=>$wpdb->insert_id, 'res'=>$res );
			}
			else {
				return false;
			}
		}
	}	//	end class
}	// end if (!class_exists(...