<?php
/**
 * This file contains the 'ABD_Ajax_Actions' class definition which provides activities
 * for AJAX calls.
 */

if ( !class_exists( 'ABD_Ajax_Actions' ) ) {
	class ABD_Ajax_Actions {
		
		public static function submit_stats() {
			check_ajax_referer( 'ad blocking detector stats ajax nonce', '_wpnonce' );

			//	First, check ignore cases
			$enabled = ABD_Database::get_specific_setting( 'enable_statistics' );
			$ignore_registered = ABD_Database::get_specific_setting( 'stats_ignore_registered' );
			$ignore_ips = ABD_Database::get_specific_setting( 'stats_ignore_ips' );

			if( $enabled == 'no' ) {
				echo 'Statistics recording disabled.';
				wp_die();
			}

			if( $ignore_registered == 'yes' ) {
				if( is_user_logged_in() ) {
					echo 'Skipping stastics update for logged in user.';
					wp_die();	//	terminate immediately for proper AJAX response
				}
			}

			if( !empty( $ignore_ips ) ) {
				//	This is results from textarea... one IP on each line... split it into array
				$ignore_ips = trim( $ignore_ips );
				$ips = explode( '\n', $ignore_ips );
				$ips = array_filter( $ips, 'trim' );	//	remove any \r characters

				$cur_user_ip = $_SERVER['REMOTE_ADDR'];

				foreach( $ips as $ip ) {
					if( ABD_Perf_Tools::ip_in_range( $cur_user_ip, $ip ) ) {
						echo 'Skipping statistics update for user\'s IP address: ' . $cur_user_ip;
						wp_die();	//	terminate immediately for proper AJAX response
					}
				}
			}


			//	If we're here, then we really are supposed to record this
			if( array_key_exists( 'adblocker', $_POST ) ) {
				$adblocker = intval( $_POST['adblocker'] );
				$blog_id = ABD_Multisite::get_current_blog_id();
				$user_ip = $_SERVER['REMOTE_ADDR'];

				//	Check validity of value
				if( $adblocker != 1 && $adblocker != 0 && $adblocker != -1 ) {
					echo 'Invalid adblocker status code submitted. (' . $adblocker . ')';
					wp_die();	//	terminate immediately for proper AJAX response
				}

				//	Add it to database
				$res = ABD_Database::insert_stat( $adblocker, $blog_id, $user_ip );

				if( $res ) {
					echo 'Statistics database updated.';
				}
				else {
					echo 'Failed to update statistics database.';
				}

				wp_die();	//	terminate immediately for proper AJAX response
			}
		}

	}	//	end class
}	//	end if ( !class_exists( ...