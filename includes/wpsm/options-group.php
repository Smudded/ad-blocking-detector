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
 * Contains the ABDWPSM_Options_Group class declaration.
 */

require_once( 'settings-manager.php' );
require_once( 'tab.php' );
require_once( 'section.php' );

if( !class_exists( 'ABDWPSM_Options_Group' ) ) {
    /**
     *	This class defines the options group object.  An options group is an
     *	entry in the WordPress options table.  Every tab can have several options
     *  groups.  Every options group can have several sections.
     */
    class ABDWPSM_Options_Group {
        protected $my_sections = array();
        protected $my_db_option_name;
        protected $my_validation_callback = null;
        protected $my_tab;


        /**
         * Append a section object to the options group.
         * @param {object} $Section ABDWPSM_Section object to append.
         */
        public function add_section( $Section ) {
            //  Don't add something that isn't a section.
            if( !( $Section instanceof ABDWPSM_Section ) ) {
                //  Okay, it's not an instance of ABDWPSM_Section, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Section );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Section object as parameter.  Got ' . $type . '.' );
                return;
            }


            //  If this section already has a parent, then we don't want to
            //  add it because that will screw up references to it.
            if( $Section->get_options_group() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add a section to multiple options groups!' );
            }


            //  A valid section must contain some pieces of information! Make sure that it does.
            if( !$Section->get_id() ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Cannot add section to options group. You must specify a value for the <em>id</em> property!' );
                return;
            }

            //  Update the section to point to options group
            $Section->set_options_group_reference( $this );

            //  Check IP uniqueness requirements
            $Section->ip_uniqueness_check();

            //  Okay, add the bastard.
            $this->my_sections[] = $Section;

            //  And add it to ABDWPSM_Settings_Manager
            ABDWPSM_Settings_Manager::$sections[] = $Section;
        }   //  end add_section

        /**
         * Adds this options group to the specified tab.
         * @param {object} $Tab A ABDWPSM_Tab object to add the options group to.
         */
        public function add_to_tab( $Tab ) {
            //  Don't add to something that isn't a tab.
            if( !( $Tab instanceof ABDWPSM_Tab ) ) {
                //  Okay, it's not an instance of ABDWPSM_Tab, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Tab );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Tab object as parameter.  Got ' . $type . '.' );
                return;
            }

            //  Okay, add $this to the tab.
            $Tab->add_options_group( $this );
        }   //  end add_to_tab()

        /**
         * Returns the committed ABDWPSM_Options_Group object with the specified DB option name.
         * @param  {string} $db_option_name The DB option name used by the options group
         * @return {object}           The committed instance of the ABDWPSM_Options_Group class.
         * with the given URL slug.
         * @return {null}             If no tab with DB option anme is found, null is returned.
         */
        public static function get_options_group_by_db_option_name( $db_option_name ) {
            foreach ( ABDWPSM_Settings_Manager::$options_groups as $OG ) {
                if( $OG->get_db_option_name() == $db_option_name ) {
                    return $OG;
                }
            }

            return null;
        }

        /**
         * Returns the current values for this options group's field from the database.
         * @param  {bool} $force_cache_update = false Whether to override default
         * caching behavior and refresh the cache before returning or not.
         * @return {ARRAY_A} The current fields stored in an associative array:
         * $field_name=>$field_value. Basically, the results of WordPress' get_option().
         */
        public function get_stored_field_values(  ) {
            return get_option( $this->my_db_option_name );
        }   //  end get_stored_field_values()

        /**
         * Returns an array of all ABDWPSM_Field objects tied with this options group
         * (meaning in a section in this options group).
         * @return {ARRAY_N} Array of ABDWPSM_Field objects.
         */
        public function get_fields() {
            $fields = array();

            foreach( $this->my_sections as $Section ) {
                $fields = array_merge( $fields, $Section->get_fields() );
            }

            return $fields;
        }   //  end get_fields()


        /**
         * Makes sure this options group meets identifying property uniqueness requirements.
         */
        public function ip_uniqueness_check() {
            $my_dbon = $this->get_db_option_name();

            //  Options groups' identifying properties (IP) are the db_option_names, and they must be globally
            //  unique.

            //  So, first, make sure we have a db_option_name set If we don't then presumably
            //  this construct is still being built and we have nothing to check for.
            //  When committing it later, it will call
            //  ip_uniqueness_check and should get past this point.
            if( empty( $my_dbon ) ) {
                //  Options group not complete, can't check uniqueness, return null.
                return null;
            }


            //  Okay, if we're here, we have everything set. We can check uniqueness.
            foreach( ABDWPSM_Settings_Manager::$options_groups as $OG ) {
                if( $OG->get_db_option_name() == $my_dbon &&
                    $OG                       != $this ) {

                    //  Not unique!
                    ABDWPSM_Settings_Manager::die_with_message( 'Your options group needs a unique identifying property (db_option_name) amongst other options groups. <em>' . $my_dbon . '</em> has already been used!' );
                    return;
                }
            }


            //  If we're here, then no matches were found and we can report that all is well.
            return true;
        }


        /**
         * This is the sanitation callback to be called by WordPress'
         * register_setting function. It's purpose is to take the
         * input submitted on the options page, find the options group
         * related to that submission, and call that option group's
         * validation function.
         * @param  {ARRAY_A} $input The submitted fields as an associative array.
         * @return {ARRAY_A}        The input array after all entries validated.
         */
        public static function validation_handler( $input ) {
            //  Have we already sanitized this input?  (see bottom of this function
            //  for info on double-sanitization bug in WordPress)
            if( array_key_exists( 'abdwpsm_sanitization_already_complete', $input ) &&
                $input['abdwpsm_sanitization_already_complete'] === true ) {

                //  Yes, sanitization has already been run... Remove the flag
                //  and simply return the input we've got.
                unset( $input['abdwpsm_sanitization_already_complete'] );

                return $input;
            }

            //  Okay, no sanitization has been run yet, so run it.


            //  First, we need to get the option group object corresponding with
            //  this form submission.  Fortunately, the form should be submitting
            //  option group information in hidden fields along with the data.
            //
            //  So, extract the information and find the option group object.
            if( !array_key_exists( 'oi_db_option_name', $input ) ) {            
                ABDWPSM_Settings_Manager::die_with_message( 'Invalid form submission! Form must send along option group metadata.' );
                return false;
            }

            $db_option_name = $input['oi_db_option_name'];
            unset( $input['oi_db_option_name'] );

            $OG = ABDWPSM_Options_Group::get_options_group_by_db_option_name( $db_option_name );

            if( empty( $OG ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Invalid form submission! Form contained invalid option group metadata.' );
                return false;
            }


            //  Okay, now we should have the option group object in $OG. Call that
            //  option group object's validation function.
            $clbck_value = $OG->get_validation_callback();

            if( !empty( $clbck_value ) && is_callable( $clbck_value ) ) {
                $callback = $clbck_value;

                $retval = call_user_func_array( $callback, array( $OG, $input ) );
            }
            else {
                $callback = 'ABDWPSM_Options_Group::default_validation_function()';
                $retval = $OG->default_validation_function( $input );
            }
            //  Make sure we got back an okay value... it is supposed to return an array.
            if( !is_array( $retval ) && $retval !== false ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Validation callback error. Expected array return value. Got: <pre>' . print_r( $retval, true ) . '</pre> when using <em>' . print_r( $callback, true ) . '</em> callback.'  );
                return array();
            }



            //  WordPress' Settings API has an annoying shortcoming. The sanitization
            //  callback for the register_setting() function, which this is, gets called
            //  twice if the option does not exist in the database. Most of the time,
            //  this isn't a problem, but in our case, it is catastrophic because
            //  we depend on metadata submitted with the form that is stripped out
            //  during sanitization.  Therefore, the first sanitization runs successfully,
            //  but the second dies a fiery death because the metadata is missing.
            //
            //  The simplest way I can think of to get around this is to determine if the
            //  option exists already, and, if it doesn't, add a flag to the returned input that
            //  indicates we don't need to run validation again.  We checked for this
            //  flag at the beginning of this function.
            if( get_option( $db_option_name ) === false ) {
                $retval['abdwpsm_sanitization_already_complete'] = true;
            }


            return $retval;
        }

        /**
         * This function's purpose is to strip out any unexpected form submissions
         * for security, and to run validation from the fields.
         * @param  {ARRAY_A} $input The submitted fields as an associative array.
         * @return {ARRAY_A}        The input array after all entries validated.
         */
        public function default_validation_function( $input ) {
            $sanitized_input = array(); //  Where we're gonna put input after we've sanitized it.
            $for_the_love_of_God_dont_submit = false;   //  A flag to indicate whether we need to
                                                        //  yell at the user and not submit to the
                                                        //  database.

            $fields = $this->get_fields();

            if( !is_array( $fields ) ) {
                return array();
            }


            foreach( $fields as $Field ) {
                //  Okay, for every field, there should be a corresponding value
                //  in $input.  If there isn't one, then we assume that field is
                //  empty.
                //
                //  So, we are looping through each field, and doing four things.
                //  One, checking if the field was passed a value, and if not,
                //  marking it empty.  Two, running any field validation specified.  Three,
                //  conforming with WordPress best practices and stripping out
                //  everything but what we're expecting from $input (don't want
                //  any ne'er-do-wells sneaking in malicious input).
                //  And four, running generic validation based on field validation
                //  options specified by the user.

                //  Thing 1 & 3:  Check if we have passed input for this field, and
                //  put it in a sanitized array.
                $field_name = $Field->get_field_name();

                if( array_key_exists( $field_name, $input ) ) {
                    $sanitized_input[$field_name] = $input[$field_name];
                }
                else {
                    $sanitized_input[$field_name] = null;
                }

                //  Thing 2:  Run any field validation on the input for the field.
                $callback = $Field->get_validation_callback();

                if( !empty( $callback ) && is_callable( $callback ) ) {
                    //  The callback function will either return a sanitized string,
                    //  or a boolean false if validation should die a miserable death
                    //  here.
                    $clbckretval = call_user_func_array( $callback,
                        array( $Field, $sanitized_input[$field_name] ) );

                    //  Did we get a valid return value?
                    if( !is_string( $clbckretval ) && $clbckretval !== false ) {
                        //  No, we didn't.  Throw a useful error so the user can
                        //  update their validation function.
                        ABDWPSM_Settings_Manager::die_with_message( 'Field validation function error on <em>' . $field_name . '</em> field. Validation function must return either sanitized input as a string, or a boolean false if value is unnacceptable and submission should fail.  Review your validation function!' );
                    }

                    if( $clbckretval !== false ) {
                        $sanitized_input[$field_name] = $clbckretval;
                    }
                    else {
                        $for_the_love_of_God_dont_submit = true;
                    }
                }


                //  Thing 4: Run generic validation
                $validator_options = $Field->get_validator_options_array();

                if( !empty( $validator_options ) ) {
                     // Okay, we have validator options...  There are 5 things
                     // generic validation can look for:
                     // 1.) 'required':           Whether the field must be specified.
                     // 2.) 'valid_options':      A list of possible submission values.
                     // 3.) 'max_length':         The maximum # of characters of the submitted value.
                     // 4.) 'min_length':         The minimum # of characters of the submitted value.
                     // 5.) 'regular_expression': A regular expression to compare the submitted value to
                     //         ('pattern' is also a valid name for this to conform with HTML 5 input attributes).

                     // Check #1: Required
                     if( $validator_options['required'] &&
                        empty( $sanitized_input[$field_name] ) ) {

                         add_settings_error(
                            $field_name,                                                //  Field slug
                            $field_name . '-required',                                  //  Error code (ID attribute value)
                            '<em>' . $Field->get_display_name() . '</em> is required!'  //  Message
                         );

                         $for_the_love_of_God_dont_submit = true;
                     }

                     // Check #2: Valid Options
                     if( !$for_the_love_of_God_dont_submit ) {  //  Don't run if we've already failed
                         if( array_key_exists( 'valid_options', $validator_options ) &&
                            is_array( $validator_options['valid_options'] ) ) {

                            foreach( $validator_options['valid_options'] as $poss ) {
                                if( $sanitized_input[$field_name] == $poss ) {
                                    $foundit = true;
                                    break;
                                }
                                else {
                                    $foundit = false;
                                }
                            }

                            if( !$foundit ) {
                                add_settings_error(
                                   $field_name,                                                        //  Field slug
                                   $field_name . '-invalidchoice',                                     //  Error code (ID attribute value)
                                   'Invalid value for the <em>' . $Field->get_display_name() . '</em> field.'  //  Message
                                );

                                $for_the_love_of_God_dont_submit = true;
                            }
                        }
                     }

                     // Check #3: Max Length
                     if( !$for_the_love_of_God_dont_submit ) {  //  Don't run if we've already failed
                         if( array_key_exists( 'max_length', $validator_options ) &&
                            $validator_options['max_length'] < strlen( $sanitized_input[$field_name] ) ) {

                            add_settings_error(
                               $field_name,                                                //  Field slug
                               $field_name . '-toolong',                                   //  Error code (ID attribute value)
                               '<em>' . $Field->get_display_name() . '</em> is too long!'  //  Message
                            );

                            $for_the_love_of_God_dont_submit = true;
                        }
                     }


                     // Check #4: Min Length
                     if( !$for_the_love_of_God_dont_submit ) {  //  Don't run if we've already failed
                         if( array_key_exists( 'min_length', $validator_options ) &&
                            $validator_options['min_length'] > strlen( $sanitized_input[$field_name] ) ) {

                            add_settings_error(
                               $field_name,                                                  //  Field slug
                               $field_name . '-tooshort',                                    //  Error code (ID attribute value)
                               '<em>' . $Field->get_display_name() . '</em> is too short!'   //  Message
                            );

                            $for_the_love_of_God_dont_submit = true;
                        }
                     }

                     // Check #5:  Regular Expression
                     if( !$for_the_love_of_God_dont_submit ) {  //  Don't run if we've already failed
                         // Remember, we accept either 'regular_expression' or 'pattern' to conform with
                         // both PHP and HTML input attribute terminology. For simplicity, change any
                         // pattern value to regular_expression.
                         if( array_key_exists( 'pattern', $validator_options ) ) {
                            $validator_options['regular_expression'] = $validator_options['pattern'];
                         }

                         if( array_key_exists( 'regular_expression', $validator_options ) ) {
                             $regres = preg_match( $validator_options['regular_expression'],
                                $sanitized_input[$field_name] );

                            if( $regres === false ) {   //  Something went wrong... likely an invalid regular expression
                                ABDWPSM_Settings_Manager::die_with_message( 'Regular expression validation resulted in error for <em>' . $Field->get_display_name() . '</em> field. Make sure the regular expression is valid! Regular expression: <em>' . $validator_options['regular_expression'] . '</em>' );
                                $for_the_love_of_God_dont_submit = true;
                            }
                            else if( !$regres ) {    //  $regres will be the integer 0 if no matches, so this checks for no matches.
                                add_settings_error(
                                   $field_name,                                                                //  Field slug
                                   $field_name . '-nomatch',                                                   //  Error code (ID attribute value)
                                   'Invalid value for the <em>' . $Field->get_display_name() . '</em> field.'  //  Message
                                );

                                $for_the_love_of_God_dont_submit = true;
                            }
                         }
                     }
                }
            }

            // How did the checks go?
            if( $for_the_love_of_God_dont_submit ) {
                // Damn! Something went wrong. Let's save the submitted values
                // so we can refill the form, and return false so that this thing
                // stops submission in its tracks.
                //
                // Data persistence method: http://wordpress.stackexchange.com/a/51925
                set_transient( 'ABDWPSM_input_persistence', $input, 60 );

                return false;
            }
            else {
                // YAY! Nothing went wrong... we can continue on with our lives
                // by submitting our sanitized and validated data.
                return $sanitized_input;
            }
        }   //  end default_validation_function()

        /**
         * Outputs the input form for this options group and all its
         * sections and fields.
         */
        public function display_options_group_contents() {
            //  Add a hidden field with information about this option group
            //  so we can find it later on when we need to validate the form
            //  submission.
            $dbon = $this->get_db_option_name();
            $fname = $dbon . '[oi_db_option_name]';
            echo "<input type='hidden' name='$fname' value='$dbon' />";

            //  Output security gobbledygook
            settings_fields( $dbon );

            foreach( $this->my_sections as $Section ) {
                //  Register Section
                add_settings_section(
                    $Section->get_id(),
                    $Section->get_display_name(),
                    array( get_class( $Section ), 'display_section_contents' ),
                    $this->get_id()
                );

                //  Register Section fields
                $Section->register_fields( $this->get_id() );
            }

            //  Display all sections and fields for this options group.
            do_settings_sections( $this->get_id() );
        }

        ///////////////////////////////////////////////
        /// Constructor, Accessor (Getter) Methods, ///
        /// and Modifier (Setter) Methods           ///
        ///////////////////////////////////////////////
        public function __construct( $options_group_options_array = array() ) {
            //  Default options
            $defaults = array(
                'db_option_name'         => '',
                'validation_callback'    => null,
                'section_object_array'   => array()
            );

            //  Make sure any passed arguments are valid
            if( !is_array( $options_group_options_array ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Options group constructor will only take an array as a parameter. Got: <pre>' . print_r( $options_group_options_array, true ) . '</pre>'  );
                return;
            }

            //  Merge together the two arrays with the passed array taking precedence
            $ogoa = $options_group_options_array + $defaults;


            //  Assign the database option name to the ID and the db_option_name.
            $this->my_id = $ogoa['db_option_name'];
            $this->set_db_option_name( $ogoa['db_option_name'] );



            //  If no validation callback was provided, point to the included one.
            if( !empty( $ogoa['validation_callback'] ) ) {
                $this->set_validation_callback( $ogoa['validation_callback'] );
            }

            //  Add each section object
            if( is_array( $ogoa['section_object_array'] ) ) {
                //  Loop through the array and add sections individually
                //  so we get the add_section function's validation
                //  instead of simply assigning the array.
                foreach( $ogoa['section_object_array'] as $Section ) {
                    $this->add_section( $Section );
                }
            }
        }

        public function get_id() {
            return $this->my_id;
        }

        public function get_db_option_name() {
            return $this->my_db_option_name;
        }

        public function get_validation_callback() {
            return $this->my_validation_callback;
        }

        public function get_tab() {
            return $this->my_tab;
        }

        public function get_sections() {
            return $this->my_sections;
        }


        public function set_id( $id ) {
            if( is_string( $id ) ) {
                $this->my_id = $id;
            }
            else {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $id, true ) . '</pre>' );
            }
        }

        public function set_db_option_name( $db_option_name ) {
            if( is_string( $db_option_name ) ) {
                $this->my_db_option_name = $db_option_name;
                $this->my_id = $db_option_name;
            }
            else {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $db_option_name, true ) . '</pre>' );
            }

            //  Identifying property uniqueness check
            $this->ip_uniqueness_check();
        }

        public function set_validation_callback( $validation_callback ) {
            if( is_callable( $validation_callback ) ) {
                $this->my_validation_callback = $validation_callback;
            }
            else {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected valid PHP callable.  Got: <pre>' . print_r( $validation_callback, true ) . '</pre>' );
            }
        }

        public function set_tab_reference( $Tab ) {
            //  Don't add to something that isn't an options group.
            if( !( $Tab instanceof ABDWPSM_Tab ) ) {
                //  Okay, it's not an instance of ABDWPSM_Options_Group, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Tab );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Tab object.  Got ' . $type . '.' );
                return;
            }

            //  Okay, if we're here, then we have a section, so set it.
            $this->my_tab = $Tab;
        }
    }   //  end class
}   //  end if( !class_exists( ...
