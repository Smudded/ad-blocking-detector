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
 * Contains the ABDWPSM_Tab class declaration.
 */

require_once( 'settings-manager.php' );
require_once( 'options-group.php' );

if( !class_exists( 'ABDWPSM_Tab' ) ) {
    /**
     *	This class defines a tab object.  A tab is a collection of options groups,
     *	sections, and fields organized into separate pages with folder tab like
     *  navigation.  Each tab object represents one tab, and can have many options
     *  groups associated with it.
     */
    class ABDWPSM_Tab {
        protected $my_options_groups;
        protected $my_display_name;
        protected $my_display_description;
        protected $my_page;
        protected $my_url_slug;

        /**
         * Appends an options group object to the tab.
         * @param {object} $options_group_object An instance of ABDWPSM_Options_Group class.
         */
        public function add_options_group( $Options_group_object ) {
            //  Is it a valid options group?
            if( !( $Options_group_object instanceof ABDWPSM_Options_Group ) ) {
                //  Okay, it's not an instance of ABDWPSM_Options_Group, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Options_group_object );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Options_Group object.  Got ' . $type . '.' );
                return;
            }

            //  If this options group already has a parent, then we don't want to
            //  add it because that will screw up references to it.
            if( $Options_group_object->get_tab() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add an options group to multiple tabs!' );
            }


            //  Options groups need certain information to be valid. Make sure that it does.
            if( !$Options_group_object->get_db_option_name() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add options group to tab! You must specify a value for the <em>db_option_name</em> property!' );
                return;
            }
            if( !$Options_group_object->get_id() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add options group to tab! You must specify a value for the <em>id</em> property!' );
                return;
            }


            //  Update options group to point to this tab
            $Options_group_object->set_tab_reference( $this );


            //  IP uniqueness check
            $Options_group_object->ip_uniqueness_check();


            $this->my_options_groups[] = $Options_group_object;
            ABDWPSM_Settings_Manager::$options_groups[] = $Options_group_object;
        }

        /**
         * Returns the committed ABDWPSM_Tab object with the specified url slug.
         * @param  {string} $url_slug The URL slug value in the tab you wish to get.
         * @return {object}           The committed instance of the ABDWPSM_Tab class.
         * with the given URL slug.
         * @return {null}             If no tab with url slug is found, null is returned.
         */
        public static function get_tab_by_url_slug( $url_slug, $page ) {
            foreach ( ABDWPSM_Settings_Manager::$tabs as $tab ) {
                if( $tab->get_url_slug() == $url_slug && $tab->get_page() == $page ) {
                    return $tab;
                }
            }

            return null;
        }


        /**
         * Outputs the content of this tab to the page.
         */
        public function display_tab_contents( $page_identifier ) {
            echo '<div id="abdwpsm_tab-' . $this->get_url_slug() . '" class="abdwpsm_tab_wrapper">';
            echo '<div class="abdwpsm_display_description abdwpsm_tab_display_description" />' . $this->get_display_description() . '</div>';

            echo '<div class="ABDWPSM_options_group_wrapper">';
                foreach( $this->get_options_groups() as $OG ) {
                    ?>
                    <form action="options.php" method="post">
                        <?php
                        //$OG->display_option_group_contents( $page_identifier );
                        $OG->display_options_group_contents();
                        submit_button();
                        ?>
                    </form>
                    <?php
                }
            echo '</div>';
            echo '</div>';
        }


        /**
         * Makes sure this tab meets identifying property uniqueness requirements.
         */
        public function ip_uniqueness_check() {
            $my_page = $this->get_page();
            $my_url_slug = $this->get_url_slug();

            //  Tabs' identifying properties (IP) are the url_slug, and they only
            //  need to remain unique amongst other tabs on the same page.

            //  So, first, make sure we have a page and url_slug set. If we don't then presumably
            //  this construct is still being built and we have nothing to check for.
            //  When committing this tab later with add_to_page(), it will call
            //  ip_uniqueness_check
            if( empty( $my_page ) || empty( $my_url_slug ) ) {
                //  Tab not complete, can't check uniqueness, return null.
                return null;
            }


            //  Okay, if we're here, we have everything set. We can check uniqueness.
            foreach( ABDWPSM_Settings_Manager::$tabs as $Tab ) {
                if( $Tab->get_url_slug() == $my_url_slug &&
                    $Tab->get_page()     == $my_page &&
                    $Tab                 != $this ) {

                    //  Not unique!
                    ABDWPSM_Settings_Manager::die_with_message( 'Your tab needs a unique identifying property (url_slug) amongst other tabs on its page. <em>' . $my_url_slug . '</em> has already been used!' );
                    return;
                }
            }


            //  If we're here, then no matches were found and we can report that all is well.
            return true;
        }


        /**
         * Saves the tab in its current form.
         */
        public function add_to_page( $page_slug ) {
            //  Before we can commit the tab, we need to make sure it has all necessary info
            if( !$this->get_display_name() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Could not commit new tab. You must specify a value for the <em>display_name</em> property!' );
                return;
            }
            if( !$this->get_url_slug() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Could not commit new tab. You must specify a value for the <em>url_slug</em> property!' );
                return;
            }


            //  Set the page slug
            $this->set_page( $page_slug );


            //  Does a tab with this URL slug already exist?
            $this->ip_uniqueness_check();


            //  Commit the tab
            ABDWPSM_Settings_Manager::$tabs[] = $this;
        }   //  end add_to_page()

        ///////////////////////////////////////////////
        /// Constructor, Accessor (Getter) Methods, ///
        /// and Modifier (Setter) Methods           ///
        ///////////////////////////////////////////////
        public function __construct( $tab_options_array = array() ) {
            //  Default parameters
            $defaults = array(
                'page'                       => '',
                'display_name'               => '',
                'display_description'        => '',
                'url_slug'                   => '',
                'options_group_object_array' => array()
            );

            //  Make sure any passed arguments are valid
            if( !is_array( $tab_options_array ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Tab constructor will only take an array as a parameter. Got: <pre>' . print_r( $tab_options_array, true ) . '</pre>'  );
                return;
            }

            //  Merge together the two arrays with the passed array taking precedence
            $toa = $tab_options_array + $defaults;



            $this->set_display_name( $toa['display_name'] );
            $this->set_display_description( $toa['display_description'] );
            $this->set_url_slug( $toa['url_slug'] );


            //  Add the options groups
            $this->my_options_groups = array();
            if( is_array( $toa['options_group_object_array'] ) ) {

                foreach( $toa['options_group_object_array'] as $OG ) {
                    $this->add_options_group( $OG );
                }
            }

            //  Add to the page
            if( !empty( $toa['page'] ) ) {
                $this->add_to_page( $toa['page'] );
            }
        }   //  end __construct

        public function get_options_groups() {
            return $this->my_options_groups;
        }
        public function get_display_name() {
            return $this->my_display_name;
        }
        public function get_display_description() {
            return $this->my_display_description;
        }
        public function get_url_slug() {
            return $this->my_url_slug;
        }
        public function get_page() {
            return $this->my_page;
        }

        public function set_display_name( $tab_name ) {
            //  Display name must be a string.
            if( !is_string( $tab_name ) ) {
                //  Die a terrible death
                ABDWPSM_Settings_Manager::die_with_message('Expected string parameter. Got: <pre>' . print_r( $tab_name, true ) . '</pre>');
            }

            //  We don't want any HTML here, so convert special chars, then set
            //  the variable.
            $this->my_display_name = htmlspecialchars( $tab_name );
        }
        /**
         * @param {string} $tab_description HTML or plain text description to display
         * at the top of the tab.
         */
        public function set_display_description( $tab_description ) {
            //  Description must be a string.
            if( !is_string( $tab_description ) ) {
                //  Die a terrible death
                ABDWPSM_Settings_Manager::die_with_message('Expected string parameter. Got: <pre>' . print_r( $tab_description, true ) . '</pre>');
            }

            $this->my_display_description = $tab_description;
        }
        public function set_url_slug( $url_slug ) {
            //  URL slug must be a string.
            if( !is_string( $url_slug ) ) {
                //  Die a terrible death
                ABDWPSM_Settings_Manager::die_with_message('Expected string parameter. Got: <pre>' . print_r( $url_slug, true ) . '</pre>');
            }


            $this->my_url_slug = $url_slug;

            //  Uniqueness check (dies with a message if it fails)
            $this->ip_uniqueness_check();
        }
        public function set_page( $page_slug ) {
            //  URL slug must be a string.
            if( !is_string( $page_slug ) ) {
                //  Die a terrible death
                ABDWPSM_Settings_Manager::die_with_message('Expected string parameter. Got: <pre>' . print_r( $page_slug, true ) . '</pre>');
            }

            $this->my_page = $page_slug;

            //  Uniqueness check (dies with a message if it fails)
            $this->ip_uniqueness_check();
        }
    }   //  end class
}   //  end if( !class_exists( ...
