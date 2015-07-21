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
 * Contains the ABDWPSM_Section class declaration.
 */

require_once( 'settings-manager.php' );
require_once( 'options-group.php' );
require_once( 'field.php' );

if( !class_exists( 'ABDWPSM_Section' ) ) {
    /**
     *	This class defines the section object.  A section is a collection of
     *	related fields.  Every options group can have several sections.  Every
     *	section can have several fields.
     */
    class ABDWPSM_Section {
        protected $my_fields = array();
        protected $my_display_name;
        protected $my_display_description;
        protected $my_id;
        protected $my_options_group;


        /**
         * Append a field object to this section's fields.
         * @param {object} $Field A ABDWPSM_Field object to add.
         */
        public function add_field( $Field ) {
            //  Don't add something that isn't a field.
            if( !( $Field instanceof ABDWPSM_Field ) ) {
                //  Okay, it's not an instance of ABDWPSM_Field, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Field );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Field object.  Got ' . $type . '.' );
                return;
            }


            //  If this field already has a parent, then we don't want to
            //  add it because that will screw up references to it.
            if( $Field->get_section() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add a field to multiple sections!' );
            }


            //  A valid field must contain several pieces of information! Make sure that it does.
            if( !$Field->get_type() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add <em>' . $Field->get_display_name() . '</em> field to section. You must specify a valid <em>type</em> property!' );
                return;
            }
            if( !$Field->get_field_name() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add <em>' . $Field->get_display_name() . '</em> field to section. You must specify a valid <em>field_name</em> property!' );
                return;
            }

            if( $Field->get_type() !== 'hidden' ) {
                if( !$Field->get_display_name() ) {
                    ABDWPSM_Settings_Manager::die_with_message( 'Cannot add field to section. You must specify a value for the <em>display_name</em> property!' );
                    return;
                }
            }



            //  Update the field's section
            $Field->set_section_reference( $this );


            //  IP uniqueness check
            $Field->ip_uniqueness_check();


            //  Okay, add the bastard.
            $this->my_fields[] = $Field;

            //  And add it to ABDWPSM_Settings_Manager
            ABDWPSM_Settings_Manager::$fields[] = $Field;
        }   //  end add_field()

        /**
         * Append this section to an options group.
         * @param {object} $Options_group The options group object to append to.
         */
        public function add_to_options_group( $Options_group ) {
            //  Don't add to something that isn't an options group.
            if( !( $Options_group instanceof ABDWPSM_Options_Group ) ) {
                //  Okay, it's not an instance of ABDWPSM_Options_Group, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Options_group );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Options_Group object.  Got ' . $type . '.' );
                return;
            }

            //  Okay, add $this to the options group.
            $Options_group->add_section( $this );
        }   //  end add_to_options_group()


        /**
         * Returns the section object with the given id value.
         * @param  {string} $section_id The id of the section.
         * @return {object|null} The ABDWPSM_Section object with the id if it exists.
         */
        public static function get_section_by_id( $section_id ) {
            foreach( ABDWPSM_Settings_Manager::$sections as $Section ) {
                if( $Section->get_id() == $section_id ) {
                    return $Section;
                }
            }

            return null;
        }   //  end get_section_by_id()


        /**
         * Return an associative array of field_names=>field_values for every
         * field in this section with a stored value.
         * @param  {string | ARRAY_N}  If field name, or array of field names is given, only those fields are returned.
         * @return {ARRAY_A} Associative array where the key is the field name,
         * and the value is the field value.
         */
        public function get_stored_field_values( $specific_field_name = null ) {
            //  The problem is that fields are stored in the database by options
            //  group, not by sections.  So we have to retrieve all the fields
            //  for the options group and then parse out the fields for the section.

            $Og = $this->get_options_group();
            $Fields = $this->get_fields();

            $all_values_array = $Og->get_stored_field_values(); //  Everything in options group
            $ret_values_array = array();    //  Where section field values will be stored

            //  If there's no array in $all_values_array, then the option is
            //  empty and we have nothing to do.
            if ( !is_array( $all_values_array ) ) {
                return array();
            }

            //  Do we have any field filters in the $specific_field_name parameter?
            //  If so, make it consistent so we can easily deal with it.
            if( is_string( $specific_field_name ) ) {
                $specific_field_name = array( $specific_field_name );
            }
            else if( !is_array( $specific_field_name ) ) {
                $specific_field_name = null;
            }

            //  Loop through all fields in this section and get the corresponding
            //  value from the options group field value array.
            foreach( $Fields as $F ) {
                $fn = $F->get_field_name();

                //  Does the field have an entry in the database?
                if( array_key_exists( $fn, $all_values_array ) ) {
                    //  Yes, but is it one of the okay fields?
                    if( !is_null( $specific_field_name ) ) {
                        foreach( $specific_field_name as $sfn ) {
                            if( $fn == $sfn ) {
                                //  Yes, it's okay, add it to the return array.
                                $ret_values_array[$fn] = $all_values_array[$fn];
                            }
                        }
                    }
                    else {
                        //  No, there are no field filters.  Add it to the return array.
                        $ret_values_array[$fn] = $all_values_array[$fn];
                    }
                }
            }

            return $ret_values_array;
        }   //  end get_stored_field_values()


        /**
         * Makes sure this section meets identifying property uniqueness requirements.
         */
        public function ip_uniqueness_check() {
            $my_id = $this->get_id();

            //  Sections' identifying properties (IP) are the ids, and they must be globally
            //  unique.

            //  So, first, make sure we have an id set. If we don't then presumably
            //  this construct is still being built and we have nothing to check for.
            //  When committing it later, it will call
            //  ip_uniqueness_check and should get past this point.
            if( empty( $my_id ) ) {
                //  Section not complete, can't check uniqueness, return null.
                return null;
            }


            //  Okay, if we're here, we have everything set. We can check uniqueness.
            foreach( ABDWPSM_Settings_Manager::$sections as $Section ) {
                if( $Section->get_id() == $my_id &&
                    $Section           != $this ) {

                    //  Not unique!
                    ABDWPSM_Settings_Manager::die_with_message( 'Your section needs a unique identifying property (id) amongst other sections. <em>' . $my_id . '</em> has already been used!' );
                    return;
                }
            }


            //  If we're here, then no matches were found and we can report that all is well.
            return true;
        }


        /**
         * Registers the fields with WordPress using add_settings_field()
         * http://codex.wordpress.org/Function_Reference/add_settings_field
         */
        public function register_fields( $og_identifier ) {
            foreach( $this->my_fields as $Field ) {
                add_settings_field(
                    uniqid(),
                    $Field->get_display_name(),
                    array( get_class( $Field ), 'display_field_contents'  ),
                    $og_identifier,
                    $this->get_id(),
                    array(
                        'field_name'=>$Field->get_field_name(),
                        'section'   =>$this
                    )
                );
            }
        }


        /**
         * The add_settings_section() callback for this section.
         * http://codex.wordpress.org/Function_Reference/add_settings_section
         */
        public static function display_section_contents( $arg ) {
            // $arg is passed a lot of data by WordPress in the form of an
            // associative array. But, we only need the
            // section ID which is $arg['id'].

            $Section = self::get_section_by_id( $arg['id'] );

            if( empty( $Section ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Could not find a ABDWPSM_Section object with the specified id: <em>' . $arg['id'] . '</em>.' );
                return;
            }

            echo '<div id="abdwpsm_tab_display_description" class="abdwpsm_display_description" />' . $Section->get_display_description() . '</div>';
        }



        ///////////////////////////////////////////////
        /// Constructor, Accessor (Getter) Methods, ///
        /// and Modifier (Setter) Methods           ///
        ///////////////////////////////////////////////
        public function __construct( $section_options_array = array() ) {
            //  Default options
            $defaults = array(
                'id'                   => uniqid(),
                'display_name'         => '',
                'display_description'  => '',
                'field_object_array'   => array()
            );

            //  Make sure any passed arguments are valid
            if( !is_array( $section_options_array ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Section constructor will only take an array as a parameter. Got: <pre>' . print_r( $section_options_array, true ) . '</pre>'  );
                return;
            }

            //  Merge together the two arrays with the passed array taking precedence
            $soa = $section_options_array + $defaults;

            //  Add field objects
            if( is_array( $soa['field_object_array'] ) ) {
                //  Loop through the field objects and add individually so we
                //  get the validation benefits of the add_field function instead
                //  of simply assigning the array to $my_fields.
                foreach( $soa['field_object_array'] as $Field ) {
                    $this->add_field( $Field );
                }
            }

            //  Set the ID
            $this->set_id( $soa['id'] );

            //  Set the display name/heading
            $this->set_display_name( $soa['display_name'] );

            //  Set the display description
            $this->set_display_description( $soa['display_description'] );
        }   //  end __construct()

        public function get_fields() {
            return $this->my_fields;
        }
        public function get_display_name() {
            return $this->my_display_name;
        }
        public function get_display_description() {
            return $this->my_display_description;
        }
        public function get_id() {
            return $this->my_id;
        }
        public function get_options_group() {
            return $this->my_options_group;
        }

        public function set_id( $id ) {
            if( !is_string( $id ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $id, true ) . '</pre>' );
                return;
            }

            $this->my_id = $id;

            //  IP uniqueness check (dies if it fails)
            $this->ip_uniqueness_check();


        }

        public function set_display_name( $display_name ) {
            if( !is_string( $display_name ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $display_name, true ) . '</pre>' );
                return;
            }

            $this->my_display_name = $display_name;
        }

        public function set_display_description( $display_description ) {
            if( !is_string( $display_description ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $display_description, true ) . '</pre>' );
                return;
            }

            $this->my_display_description = $display_description;
        }

        public function set_options_group_reference( $Options_group ) {
            //  Don't add to something that isn't an options group.
            if( !( $Options_group instanceof ABDWPSM_Options_Group ) ) {
                //  Okay, it's not an instance of ABDWPSM_Options_Group, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Options_group );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Options_Group object.  Got ' . $type . '.' );
                return;
            }

            //  Okay, if we're here, then we have a section, so set it.
            $this->my_options_group = $Options_group;
        }
    }   //  end class
}   //  end if( !class_exists( ...
