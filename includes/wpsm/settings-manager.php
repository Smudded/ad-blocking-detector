<?php

/*****

 This file is part of the WordPress Settings API Manager created by John Morris.

 The WordPress Settings API Manager (WPSM) is a drop-in framework that drastically
 simplifies interaction with the WordPress Settings API.  It removes the tedium
 and confusing workflow and overlays a simple, well-documented layer over the top.
 Forget the headache, use WPSM!

    Website: http://wpsm.johnmorris.me


    Copyright 2014 John Morris  (email : john@johnmorris.me)

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

*****/

/**
 * Contains the ABDWPSM_Settings_Manager class declaration.
 */
 require_once( 'field.php' );
 require_once( 'options-group.php' );
 require_once( 'section.php' );
 require_once( 'tab.php' );


 // Initialize this b*tch!
 add_action( 'admin_init', array( 'ABDWPSM_Settings_Manager', 'initialize' ), 1002 );


if( !class_exists( 'ABDWPSM_Settings_Manager' ) ) {
    /**
     * This class is simply a static collection of all tabs, options groups,
     * sections, and fields created in the current instance and the necessary
     * function that gets this party started.  It's purpose is
     * to create a single hub where everything is available to prevent users
     * from having to maintain there collection of settings manually and initialize
     * this settings manager piece-by-piece.
     *
     * When you need to access all the settings cold (i.e. when you have no
     * existing code references/variables for the settings), use the static
     * collection below.
     *
     * ABDWPSM_Settings_Manager::$tabs             --->    All created and committed tabs.
     * ABDWPSM_Settings_Manager::$options_groups   --->    All created and committed options groups.
     * ABDWPSM_Settings_Manager::$sections         --->    All created and committed settings.
     * ABDWPSM_Settings_Manager::$fields           --->    All created and committed fields.
     */
    class ABDWPSM_Settings_Manager{
        public static $tabs = array();
        public static $options_groups = array();
        public static $sections = array();
        public static $fields = array();

        protected static $query_args_to_strip = array();

        //  Keep this from being instantiated... It's a static class.
        protected function __construct() {}

        public static function initialize() {
            //wp_die('<pre>' . htmlspecialchars( print_r( self::$options_groups, true ) ) . '</pre>' );
            foreach( self::$options_groups as $OG ) {
                register_setting(
                    $OG->get_db_option_name(),                       //  Option group name
                    $OG->get_db_option_name(),                       //  DB option name
                    array( get_class( $OG ), 'validation_handler' )  //  Sanitize/Validate callback
                );
            }
        }

        public static function display_settings_page_content( $page_identifier ) {
            //  Do we have the basic building block for the settings... an array of tabs in $tabs?
            if( !is_array( self::$tabs ) ) {    //  Oh noes! We must die a horrible death.
                self::die_with_message( "Expected array of ABDWPSM_Tab objects." );
            }

            //  Do we have any tabs in the array?
            if( empty( self::$tabs ) ) {
                self::die_with_message( "No tabs. You must create a tab!" );
            }

            //  Output any errors from previous submissions
            settings_errors();


            //  Output the tab nav
            //  What's the current tab slug?
            if( isset( $_GET['tab'] ) ) {
                $active_tab_slug = $_GET['tab'];
            }
            else {  //  None provided, use the first tab.
                $active_tab_slug = null;
            }
            ?>
            <h2 class="nav-tab-wrapper">
                <?php
                //  Loop through the tabs and output them as nav links...
                $num_tabs = 0;
                foreach( self::$tabs as $Tab ) {
                    //  Is this tab for this page?
                    if( $Tab->get_page() != $page_identifier ) {    //  Nope, skip it.
                        continue;
                    }

                    //  Extract tab data
                    $query_arg = $Tab->get_url_slug();
                    $name = $Tab->get_display_name();
                    $classes = "nav-tab";

                    //  If this tab is active, add the active tab CSS class.
                    if( $query_arg == $active_tab_slug || is_null( $active_tab_slug ) ) {
                        $classes .= " nav-tab-active";
                    }

                    //  Build URL for tab
                    $url = add_query_arg( 'tab', $query_arg );
                    $url = remove_query_arg( self::$query_args_to_strip, $url );

                    ?>
                    <a href="<?php echo $url; ?>" class="<?php echo $classes; ?>"><?php echo $name; ?></a>
                    <?php


                    //  If we don't have an active tab set, then let's just set this one. Theoretically, this will
                    //  be the first tab since it can only be empty when we're running through the first time with
                    //  this method.
                    if( is_null( $active_tab_slug ) ) {
                        $active_tab_slug = $query_arg;
                    }

                    $num_tabs++;
                }
                ?>
            </h2>

            <?php
            //  Do we have any tabs?
            if( $num_tabs > 0 ) {
                //  Yes, we have tabs, so output the tab contents for the active tab
                //  Get the tab
                $Active_Tab = ABDWPSM_Tab::get_tab_by_url_slug( $active_tab_slug, $page_identifier );
                if( is_null( $Active_Tab ) ) {
                    echo '<h3>Invalid tab!</h3>';
                    return;
                }

                $Active_Tab->display_tab_contents( $page_identifier );
            }
            else {
                //  No tabs
                echo '<h3>No tabs are set to display on this page.</h3>';
            }
        }

        public static function add_query_arg_to_strip( $query_arg_name ) {
            if( !is_string( $query_arg_name ) ) {
                self::die_with_message( 'Query arg name must be string!' );
            }

            array_push( self::$query_args_to_strip, $query_arg_name );
        }

        public static function die_with_message( $message ) {
            //  Get calling location as pretty HTML.
            //  http://php.net/manual/en/function.debug-backtrace.php
            $trace = debug_backtrace();
            $trace_str = print_r( $trace, true );
            $caller = array_shift( $trace );
            $second_caller = array_shift( $trace );

            //  Function
            $function = '';
            if( isset( $second_caller['class'] ) ) {
                $function .= $second_caller['class'] . '::';
            }
            $function .= $second_caller['function'] . '()';

            //  Line number
            $line = $caller['line'];

            //  File
            $file = $caller['file'];


            $loc_string = '<h4>Error Location:</h4>';
            $loc_string .= '<strong>Function: </strong>' . $function;
            $loc_string .= '<br /><strong>File: </strong>' . $file;
            $loc_string .= '<br /><strong>Line #: </strong>' . $line;

            $loc_string .= '<br /><br /><h4>Complete Backtrace</h4>';
            $loc_string .= '<pre>' . $trace_str . '</pre>';

            //  Now output the error.
            wp_die( '<h3 style="color: #600;">ERROR</h3><p style="color: #600; font-size: 1.2em;">' . $message . '</p><br />' . $loc_string . 'WPSM Error' );
        }

        /**
         * Returns a string representation of what the passed item is. (e.g.
         * "ABDWPSM_Field object" or "integer").
         * @param  {mixed}  $the_thing The item for which you want to know what it is.
         * @return {string}            Text description of what the passed item is.
         */
        public static function wtf_is_this( $the_thing ) {
            if( is_object( $the_thing ) ) {
                $type = get_class( $the_thing ) . ' object';
            }
            else {
                $type = gettype( $the_thing );
            }

            return $type;
        }
    }   //  end class
}   //  end if( !class_exists( ...
