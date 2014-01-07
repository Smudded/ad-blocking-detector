<?php

if ( !class_exists( 'ABD_Db_Manip' ) ) {
	class ABD_Db_Manip {
		protected static $our_shortcode_table;

		/**
		 * Gets all shortcode entries from the database and returns them
		 * @return ARRAY_N A numerical array of associative arrays. Each first dimension element is a row, each second dimension is a column (column_name=>column_value)
		 * @return NULL If nothing was found or query fails in any way, NULL is returned.
		 */
		public static function get_all_shortcodes() {
			global $wpdb;

			$sql = "SELECT * FROM " . self::get_shortcode_table_name();

			return $wpdb->get_results($sql, ARRAY_A);	//	returns associative array on success or NULL on fail
		}
		
		/**
		 * Gets a single row from the shortcode table.
		 * @param  int $id The ID # of the row wanted. Used in WHERE clause of SQL query (WHERE id=$id)
		 * @return ARRAY_A An associative array where each entry represents the column (column_name=>column_value)
		 * @return NULL If nothing was found or query fails in any way, NULL is returned.
		 */
		public static function get_shortcode_by_id($id) {
			global $wpdb;

			$sql = "SELECT * FROM " . self::get_shortcode_table_name() . " WHERE id=" . $id . " LIMIT 1";

			return $wpdb->get_row($sql, ARRAY_A);
		}

		/**
		 * Removes a row from the shortcode table.
		 * @param  int $id The ID# of the row to delete. Used in WHERE clause of SQL query (WHERE id=$id)
		 * @return int     The number of rows altered.
		 * @return bool 	FALSE if operation failed in any way.
		 */
		public static function delete_shortcode_by_id($id) {
			global $wpdb;

			return $wpdb->delete( self::get_shortcode_table_name(), array( 'id' => $id ) );
		}


		/**
		 * Updates values for an entry in the shortcode table
		 * @param  int $id The ID# of the row to alter. Used in WHERE clause of SQL query (WHERE id=$id)
		 * @param  ARRAY_A $data An associative array where each entry represents a column (column_name=>column_value)
		 * @return int The number of rows altered
		 * @return bool FALSE if operation failed in any way.
		 */
		public static function update_shortcode_by_id($id, $data) {
			global $wpdb;

			return $wpdb->update( self::get_shortcode_table_name(), $data, array( 'id' => $id ) );
		}

		/**
		 * Inserts a new row in the shortcode table
		 * @param  ARRAY_A $data An associative array where each entry represents a column (column_name=>column_value)
		 * @return bool       FALSE if insertion failed.
		 * @return ARRAY_A Associative array with two elements
		 *                             'id'=>The ID# of the new row
		 *                             'res'=>The actual value returned by $wpdb->insert
		 */
		public static function insert_shortcode($data) {
			global $wpdb;

			$res = $wpdb->insert( self::get_shortcode_table_name(), $data );

			if ( $res ) {
				return array( 'id'=>$wpdb->insert_id, 'res'=>$res );
			}
			else {
				return false;
			}
		}

		/**
		 * Returns the name of the shortcode table, including all prefixes, as a string.
		 */
		public static function get_shortcode_table_name() {
			return self::$our_shortcode_table;
		}

		
		/**
		 * Sets values for static data members.
		 */
		public function __construct() {
			global $wpdb;

			self::$our_shortcode_table = $wpdb->prefix . 'abd_shortcodes';
		}
	}	//	end class ABD_Db_Manip

	//	instantiate class to run constructor
	new ABD_Db_Manip();
}	//	end if ( !class_exists