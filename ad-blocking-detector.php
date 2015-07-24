<?php
/**
 * Plugin Name: Ad Blocking Detector
 * Plugin URI: http://adblockingdetector.jtmorris.net
 * Description: A plugin for detecting ad blocking browser extensions, plugins, and add-ons. It allows you to display alternative content to site visitors who block your ads.
 * Version: 3.0.2
 * Author: John Morris
 * Author URI: http://cs.johnmorris.me
 * License: GPL2
 */

/*  Copyright 2013 - 2014  John Morris  (email : johntylermorris@jtmorris.net)

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
 * IT IS CRUCIAL THAT YOU UPDATE THIS VERSION NUMBER ALONG WITH THE PLUGIN 
 * HEADER AND README.  NOT DOING SO RUNS THE RISK OF BREAKING BOTH THIS AND THE 
 * BLOCK LIST COUNTERMEASURE PLUGIN IT USES IN VERY SUBTLE WAYS!.
 *
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *    \\//    \\//    \\//    \\//
 *     \/      \/      \/      \/                          */

define( 'ABD_VERSION', '3.0.2' );

/*     /\      /\      /\      /\
 *    //\\    //\\    //\\    //\\
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||
 *     ||      ||      ||      ||                         */
 

$start_time = microtime( true );
$start_mem = memory_get_usage( true );

define ( 'ABD_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define ( 'ABD_ROOT_URL', plugin_dir_url( __FILE__ ) );
define ( 'ABD_PLUGIN_FILE', ABD_ROOT_PATH . 'ad-blocking-detector.php' );
define ( 'ABD_SUBDIR_AND_FILE', plugin_basename(__FILE__) );


require_once ( ABD_ROOT_PATH . 'includes/setup.php' );

ABD_Setup::initialize();



//      Start SESSION to facilitate data transfers
//      Don't Forget Error Prevention: http://goo.gl/Acm9oY
//      Don't Forget PHP 5.4.0+ changes: http://www.php.net/manual/en/function.session-status.php
function abd_my_session_start()
{
    if(abd_is_session_started() || !isset($_SESSION)) {
        if (isset($_COOKIE['PHPSESSID'])) {
                $sessid = $_COOKIE['PHPSESSID'];
        }
        else if (isset($_GET['PHPSESSID'])) {
                $sessid = $_GET['PHPSESSID'];
        }
        else {
                session_start();
                return false;
        }
        if (!preg_match('/^[a-z0-9]{32}$/', $sessid)) {
                return false;
        }
        session_start();
        return true;
    }
}
function abd_is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
abd_my_session_start();

ABD_Log::perf_summary( 'Entire Plugin Init', $start_time, $start_mem );