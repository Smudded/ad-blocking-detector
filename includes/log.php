<?php
/**
 * This file containst the 'ABD_Log' class definition which provides tools for logging
 * actions during the current SESSION.
 */

if ( !class_exists( 'ABD_Log' ) ) {
	class ABD_Log {
		protected static $our_option_name = 'abd_event_log';

		public static function error( $msg ) {
			self::generic_log_entry( 'ERROR', $msg );
		}

		public static function info( $msg ) {
			self::generic_log_entry( 'INFO', $msg );
		}

		public static function debug( $msg ) {
			self::generic_log_entry( 'DEBUG', $msg );
		}



		public static function get_all_log_entries() {
			$entries = get_site_option( self::get_option_name(), array() );

			if( !is_array( $entries ) ) {
				return array();
			}

			return $entries;
		}

		public static function get_last_log_entry() {
			$es = self::get_all_log_entries();

			return end( $es );
		}

		public static function get_readable_log( $num_entries = 0, $line_endings = '&#13;&#10;&#13;&#10;' ) {
			$readable = '';
			$es = self::get_all_log_entries();

			//	Get indices interval
			if( $num_entries > 0 ) {
				$start = count( $es ) - $num_entries;

				if( $start < 0 ) {	//	No negative indices!
					$start = 0;
				}
			}
			else {
				$start = 0;
			}

			$end = count( $es ) - 1;	//	Last index


			//	Loop through those indices of the log
			for( $i = $end; $i >= $start; $i-- ) {
				if( !array_key_exists( $i, $es ) ) {
					//	Hmm... This doesn't exist for some reason
					continue;
				}

				//	Okay, append readable log to string
				$type = $es[$i]['type'];
				$msg = $es[$i]['message'];
				$time = $es[$i]['time'];
				//$loc = print_r( $es[$i]['backtrace'], true );

				//	Pad type with spaces for uniformity
				for( $j=strlen( $type ); $j <= 8; $j++ ) {
					$type .= ' ';
				}
				
				$readable .= $type . ' :: ' . $time . ' ::   ' . $msg . /*' ::  ' . $loc .*/ $line_endings;
			}

			return $readable;
		}

		public static function clear_log( $type='all' ) {
			return delete_site_option( self::get_option_name() );
		}


		protected static function generic_log_entry( $type, $msg ) {
			$bt = debug_backtrace();

			$e = self::get_all_log_entries();

			$e[] = array(
				'type' => $type,
				'message' => $msg,
				'time' => date( 'm/d/y @ H:i:s (P' ) . ' GMT)',
				'backtrace' => $bt
			);

			update_site_option( self::get_option_name(), $e );

			self::prune_log();
		}

		protected static function prune_log( $max_entries = 500 ) {
			$es = self::get_all_log_entries();

			if( count( $es ) > $max_entries ) {
				$es = array_slice( $es, -1*$max_entries );
			}
		}



		protected static function get_option_name() {
			return self::$our_option_name;
		}
	}	//	end class
}	//	end if ( !class_exists( ...