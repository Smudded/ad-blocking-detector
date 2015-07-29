<?php
/**
 * Plugin Name: Ad Blocking Detector - Block List Countermeasure
 * Plugin URI: http://adblockingdetector.johnmorris.me
 * Description: Provides fallback files in the event the main Ad Blocking Detector's assets are blocked.
 * Version: 3.2.0
 * Author: John Morris
 * Author URI: http://cs.johnmorris.me
 * License: GPL2
 */

/*  Copyright 2015 John Morris  (email : johntylermorris@jtmorris.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/*
 * IT IS CRUCIAL THAT YOU UPDATE THIS VERSION NUMBER ALONG WITH AD BLOCKING DETECTOR'S PLUGIN 
 * HEADER AND README.  NOT DOING SO RUNS THE RISK OF BREAKING THIS PLUGIN IN VERY SUBTLE WAYS!.
 *
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *    \\//    \\//    \\//    \\//
 *     \/      \/      \/      \/                          */

define( 'ABDBLC_VERSION', '3.2.0' );

/*     /\      /\      /\      /\
 *    //\\    //\\    //\\    //\\
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||                         */


define ( 'ABDBLC_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define ( 'ABDBLC_ROOT_URL', plugin_dir_url( __FILE__ ) );
define ( 'ABDBLC_PLUGIN_FILE', ABDBLC_ROOT_PATH . 'ad-blocking-detector-block-list-countermeasure.php' );
define ( 'ABDBLC_SUBDIR_AND_FILE', plugin_basename(__FILE__) );

if( !class_exists( 'ABDBLC' ) ) {
    class ABDBLC {
        public function __construct() {
            add_action( 'admin_init', array( &$this, 'admin_init_handler' ) );

            //  Activation
            register_activation_hook( ABDBLC_PLUGIN_FILE,
                array( &$this, 'hooks_helper_activation' ) );

            //  Deactivation
            register_deactivation_hook( ABDBLC_PLUGIN_FILE,
                array( &$this, 'hooks_helper_deactivation' ) );
        }

        public function admin_init_handler() {
            //  We only want to do stuff if Ad Blocking Detector is installed and active
            if( !is_plugin_active( 'ad-blocking-detector/ad-blocking-detector.php' ) ) {
                //  It's not installed or active
                //  Notify the user, deactivate this plugin, then phone home.
                add_action( 'admin_notices', array( &$this, 'admin_notice_missing_abd_handler' ) );
                deactivate_plugins( ABDBLC_SUBDIR_AND_FILE );
                return;
            }

            //  Make sure we have necessary constants defined
            if( !defined( 'ABDBLC_ROOT_URL' ) ) {
                //  Somebody deleted this crucial constant, which is how Ad Blocking Detector
                //  knows whether to refer to these fallback files or not.
                add_action( 'admin_notices', array( &$this, 'admin_notice_missing_consts_handler' ) );
                deactivate_plugins( ABDBLC_SUBDIR_AND_FILE );
                return;
            }


            //  Make sure Ad Blocking Detector and this plugin's versions match... if not,
            //  complain.
            if( ABD_VERSION != ABDBLC_VERSION ) {
                if( class_exists( 'ABD_Admin_Views' ) ) {
                    $fnc = array( 'ABD_Admin_Views', 'update_manual_blcp_notice' );
                }
                else {
                    $fnc = array( &$this, 'admin_notice_mismatched_version_handler' );
                }

                add_action( 'admin_notices', $fnc );
                return;
            }
        }

        public function hooks_helper_activation() {
            if( class_exists( 'ABD_Log' ) ) {
                ABD_Log::info( 'Block List Countermeasure plugin activated.' );
            }

            //  Store this plugin directory in the database so ABD knows where to look for the plugin
            $only_dir = str_replace( '/ad-blocking-detector-block-list-countermeasure.php', '', ABDBLC_SUBDIR_AND_FILE );
            update_site_option( 'abd_blc_dir', $only_dir );
        }
        public function hooks_helper_deactivation() {
            if( class_exists( 'ABD_Log' ) ) {
                ABD_Log::info( 'Block List Countermeasure plugin deactivated.' );
            }
        }



        public function admin_notice_missing_abd_handler() {
           $this->generic_error_handler(
                sprintf( 
                    __( '%1$s depends on the %2$s plugin. Please activate %2$s, then try reactivating %1$s.', 'ad-blocking-detector' ), 
                    '<em>Ad Blocking Detector - Block List Countermeasure</em>', 
                    '<em>Ad Blocking Detector</em>' 
                )
            );
        }

        public function admin_notice_missing_consts_handler() {
           $this->generic_error_handler(
                sprintf( 
                    __( '%1$s plugin is malformed. Missing crucial constant ABDBLC_ROOT_URL.', 'ad-blocking-detector' ), 
                    '<em>Ad Blocking Detector - Block List Countermeasure</em>'
                )
            );
        }

        public function admin_notice_mismatched_version_handler() {
           $this->generic_error_handler(
                sprintf( 
                    __( 'The %1$s plugin is a different version than %2$s. You must update the %2$s plugin to prevent breakage.', 'ad-blocking-detector' ), 
                    '<em>Ad Blocking Detector</em>', '<em>Ad Blocking Detector - Block List Countermeasure</em>'
                )
            );
        }

        protected function generic_error_handler( $msg ) {
            ?>
            <div class="error">
                <p>                
                    <?php echo $msg; ?>
                </p>
            </div>
            <?php
        }
    }
}

//  Initialize class
new ABDBLC();