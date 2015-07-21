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
 * Contains the ABDWPSM_Field class declaration.
 */

require_once( 'settings-manager.php' );
require_once( 'options-group.php' );
require_once( 'field.php' );

if( !class_exists( 'ABDWPSM_Field' ) ) {
    /**
     *	This class defines the field object.  A field is a single entry in the
     *	database option, a single input on the options page, and all the supporting
     *	data.  Fields go into sections.  A section can have many fields.
     */
    class ABDWPSM_Field {
        protected $my_display_name;
        protected $my_display_description;
        protected $my_display_example;
        protected $my_type;
        protected $my_field_options;
        protected $my_validator_options;
        protected $my_validation_callback;
        protected $my_field_name;
        protected $my_section;
        protected $my_data_persistence_value = null;


        /**
         * Adds this field to a section object.
         * @param {object} $Section The ABDWPSM_Section to add this field to.
         */
        public function add_to_section( $Section ) {
            //  If it's a ABDWPSM_Section, it's what we want
            if ( is_object( $Section ) && ( $Section instanceof ABDWPSM_Section ) ) {
                $So = $Section;
            }
            else {
                //  WTF is this?  This isn't what we want!
                //  Okay, it's not what we want, but what is it?
                $type = get_class( $Options_group );
                if( !$type ) {  //  It's not a class, it's something else... what?
                    $type = gettype( $Options_group );
                }
                else {  //  It is a class, let's tack on ' object' for readability.
                    $type .= ' object';
                }

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Section object or string containing the ABDWPSM_Section object\'s ID.  Got ' . $type . '.' );
                return;
            }

            //  Okay, add this to the ABDWPSM_Section object
            $So->add_field( $this );
        }   //  end add_to_section()

        /**
         * Returns the value for this field currently stored in the database.
         * @return {mixed} The value stored in the database
         */
        public function get_stored_field_value() {
            $S = $this->get_section();

            if( empty( $S ) ) {
                return null;
            }

            $farr = $S->get_stored_field_values( $this->get_field_name() );

            if( is_array( $farr ) && array_key_exists( $this->get_field_name(), $farr ) ) {
                return $farr[$this->get_field_name()];
            }
            else {
                return null;
            }
        }   //  end get_stored_field_value()

        /**
         * Returns an array of all ABDWPSM_Field objects saved and committed to sections.
         * @return {ARRAY_N} Array of ABDWPSM_Field objects that are created and saved.
         */
        public static function get_all_fields() {
            return ABDWPSM_Settings_Manager::$fields;
        }   //  end get_all_fields()

        /**
         * Gets the ABDWPSM_Field object with a field name matching the one specified in the
         * section specified.
         * @param  {string} $field_name The name of the field to get.
         * @param  {string} $Section    A ABDWPSM_Section object to search for the field in.
         * @return {object}             The ABDWPSM_Field object with the specified name.
         * @return {null}               If no field object matches, null is returned.
         */
        public static function get_field_by_field_name( $field_name, $Section ) {
            //  Don't add something that isn't a section.
            if( !( $Section instanceof ABDWPSM_Section ) ) {
                //  Okay, it's not an instance of ABDWPSM_Section, to make
                //  errors more helpful, what is it?
                $type = ABDWPSM_Settings_Manager::wtf_is_this( $Section );

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Section object.  Got ' . $type . '.' );
                return;
            }

            foreach( $Section->get_fields() as $Field ) {
                if( $Field->get_field_name() == $field_name ) {
                    return $Field;
                }
            }

            return null;
        }   //  end get_field_by_field_name()

        /**
         * Appends an option to the field options array.
         * @param {string} $option_name  The name of the option to append.
         * @param {mixed} $option_value The value of the option to append.
         */
        public function add_field_option( $option_name, $option_value ) {
            if( !is_array( $this->my_field_options ) ) {
                $this->my_field_options = array();
            }

            $this->my_field_options[$option_name] = $option_value;
        }

        /**
         * Makes sure this field meets identifying property uniqueness requirements.
         */
        public function ip_uniqueness_check() {
            $My_Section = $this->get_section();
            $my_fn = $this->get_field_name();

            //  Fields' identifying properties (IP) are the field_name, and they only
            //  need to remain unique amongst other fields in the same section.

            //  So, first, make sure we have a section and field_name set. If we don't then presumably
            //  this construct is still being built and we have nothing to check for.
            //  When committing this field later, it will call
            //  ip_uniqueness_check and should get past this point.
            if( empty( $My_Section ) || empty( $my_fn ) ) {
                //  Field not complete, can't check uniqueness, return null.
                return null;
            }


            //  Okay, if we're here, we have everything set. We can check uniqueness.
            foreach( $My_Section->get_fields() as $Field ) {
                if( $Field->get_field_name() == $my_fn &&
                    $My_Section              == $Field->get_section() &&
                    $Field                   != $this ) {

                    //  Not unique!
                    ABDWPSM_Settings_Manager::die_with_message( 'Your field needs a unique identifying property (field_name) amongst other fields in its section. <em>' . $my_fn . '</em> has already been used!' );
                    return;
                }
            }


            //  If we're here, then no matches were found and we can report that all is well.
            return true;
        }

        /**
         * Appends an option to the validator options array.
         * @param {string} $option_name  The name of the option to append.
         * @param {mixed} $option_value The value of the option to append.
         */
        public function add_validator_option( $option_name, $option_value ) {
            if( !is_array( $this->my_validator_options ) ) {
                $this->my_validator_options = array();
            }

            $this->my_validator_options[$option_name] = $option_value;
        }

        /**
         * A wrapper around WordPress' add_settings_error() function that automatically
         * fills in the tedious parameters.
         * @param {string}  $message  The error message to display to the user.
         * @param {string}  $idcode   Optional slug name to help identify the error for CSS and JavaScript targeting.
         */
        public function add_validation_error_to_stack( $message, $idcode = null ) {
            if( empty( $idcode ) ) {
                $idcode = uniqid();
            }

            add_settings_error(
                $this->get_field_name(),
                $this->get_field_name() . '-' . $idcode,
                $message
            );
        }


        /**
         * Outputs the HTML for this field input.
         * @param  {[type]} $args [description]
         * @return {[type]}       [description]
         */
        public static function display_field_contents( $args ) {
            $Field = self::get_field_by_field_name( $args['field_name'], $args['section'] );

            $Field->display_field_contents_helper();
        }
            public function display_field_contents_helper() {
                //  Okay, the main goal is to display the field contents. BUT,
                //  if we have a settings error from a just finished submission,
                //  then there is likely a data persistence transient saved (saved
                //  in validation function in ./options-group.php --
                //  ABDWPSM_Options_Group::default_validation_function()).
                //
                //  If the persistence exists, we need to override the values the
                //  fields will display with the data from the persistence.
                $input_arr = get_transient( 'ABDWPSM_input_persistence' );


                if( is_array( $input_arr ) ) {
                    //  We have data persistence! Get the value for the current
                    //  field if there is one and override the value.
                    $field_name = $this->get_field_name();

                    if( array_key_exists( $field_name, $input_arr ) ) {
                        //  Override!
                        $this->data_persistence_value = $input_arr[$field_name];

                        //  Remove this value from the transient so it doesn't show up
                        //  in future page loads and update the transient.
                        unset( $input_arr[$field_name] );
                        set_transient( 'ABDWPSM_input_persistence', $input_arr );
                    }
                    else {
                        //  Just to be absolutely sure no data persistence persists
                        //  longer than we want, unset the data_persistence_value
                        $this->data_persistence_value = null;
                    }
                }




                $t = strtolower( $this->get_type() );

                if( $t == 'text' || $t == 'password' || $t == 'email' ||
                    $t == 'url' || $t == 'textarea' || $t == 'wysiwyg' || $t == 'color' ||
                    $t == 'date' || $t == 'hidden' || $t == 'number' || $t == 'month' ||
                    $t == 'tel' || $t == 'time' || $t == 'week' ) {

                    $this->display_field_contents_helper_single();
                }
                else if ( $t == 'checkbox' || $t == 'radio' || $t == 'select' || $t == 'dropdown' ) {

                    $this->display_field_contents_helper_grouped();
                }
                else {
                    ABDWPSM_Settings_Manager::die_with_message( 'Invalid field type "<em>' . $this->get_type() . '</em>" supplied for <em>' . $Field->get_display_name() . '</em> field.' );
                }
            }
            protected function display_field_contents_helper_single( ) {
                $dname = $this->get_display_name();
                $ddesc = $this->get_display_description();
                $dex = $this->get_display_example();
                $ftype = $this->get_type();
                $fopt = $this->get_field_options_array();


                $fname = $this->get_section()->get_options_group()->get_db_option_name();

                if( is_array( $this->get_field_name() ) ) {
                    foreach( $this->get_field_name() as $lvl ) {
                        $fname .=  '[' . $lvl . ']';
                    }
                }
                else {
                    $fname .= '[' . $this->get_field_name() . ']';   
                }

                $fid = str_replace('[', '_', $fname);
                $fid = str_replace(']', '', $fid);


                //  Let's parse the field options a little to make life simpler
                //  Start with read only.
                ( array_key_exists( 'readonly', $fopt ) && $fopt['readonly'] ) ?
					$readonly = 'readonly="readonly"' : '';
                //  Now disabled
                ( array_key_exists( 'disabled', $fopt ) && $fopt['disabled'] ) ?
                    $disabled = 'disabled="disabled"' : '';
                //  Required
                ( array_key_exists( 'required', $fopt ) && $fopt['required'] ) ?
                    $required = 'required="required"' : '';
                //  Now default value
                array_key_exists('default', $fopt) ?
					$default = $fopt['default'] : $default = '';
                //  And style
                array_key_exists('style', $fopt) ?
					$style = $fopt['style'] : $style = '';
                //  Max length
                array_key_exists('max_length', $fopt) ?
                    $maxlength = $fopt['max_length'] : $maxlength = '';
                //  Min length
                array_key_exists('min_length', $fopt) ?
                    $minlength = $fopt['min_length'] : $minlength = '';
                //  Placeholder
                array_key_exists('placeholder', $fopt) ?
                    $placeholder = $fopt['placeholder'] : $placeholder = '';
                //  Value override
                array_key_exists('value_override', $fopt) ?
                    $value_override = $fopt['value_override'] : $value_override = null;
                //  WP Editor Settings Array
                ( array_key_exists('wysiwyg_settings_array', $fopt) && is_array( $fopt['wysiwyg_settings_array'] ) ) ?
                    $wp_ed_settings = $fopt['wysiwyg_settings_array'] : $wp_ed_settings = array();


                //  Now, let's get the value we're gonna put in the input.
                //  Unfortunately, there are several possibilities.
                //  Possibility #1: Data persistence from a previous failed submission attempt.
                if( !is_null( $this->data_persistence_value ) ) {
                    $value = $this->data_persistence_value;

                    $this->data_persistence_value = null;   //  Clean up our mess
                }
                else {
                    //  Possibility #2: Value override from the user
                    if( is_null( $value_override ) ) {
                        //  Possibility #3: Existing value in the database
                        $cur_val = $this->get_stored_field_value();
                        if( !isset( $cur_val ) ) {  //  No value stored
                            //  Possibility #4: Default value
                            $value = $default;
                        }
                        else {
                            $value = $cur_val;  //  Something was in the database, use it.
                        }
                    }
                    else {
                        $value = $value_override;   //  We have a user override.
                    }
                }

                //  Covert value to HTML entities
                $value = htmlentities( $value, ENT_QUOTES );

                //  Now, output the field
                if( $ftype == 'textarea' ) {
                    echo '<textarea id="' . $fid . '" name="' . $fname . '" style="' . $style . '" placeholder="' . $placeholder . '" ' . $readonly . ' ' . $disabled . ' ' . $required . '>';
                    echo $value;
                    echo '</textarea>';
                }
                else if ( $ftype == 'wysiwyg' ) {
                    //  Add name
                    $wp_ed_settings['textarea_name'] = $fname;
                    wp_editor( $value, $fid, $wp_ed_settings );
                }
                else {
                    echo "<input id='$fid' name='$fname' type='$ftype' value='$value' style='$style' maxlength='$maxlength' minlength='$minlength' placeholder='$placeholder' $readonly $disabled $required />";
                }

                //  And any description and example
                if( !empty( $ddesc ) ) {
                    echo "<p class='description'>$ddesc</p>";
                }
                if( !empty( $dex ) ) {
                    echo "<p class='description' style='color: #1E8CBE'>Example: $dex</p>";
                }
            }
            protected function display_field_contents_helper_grouped( ) {
                $dname = $this->get_display_name();
                $ddesc = $this->get_display_description();
                $dex = $this->get_display_example();
                $ftype = $this->get_type();
                $fopt = $this->get_field_options_array();


                $fname = $this->get_section()->get_options_group()->get_db_option_name();

                if( is_array( $this->get_field_name() ) ) {
                    foreach( $this->get_field_name() as $lvl ) {
                        $fname .=  '[' . $lvl . ']';
                    }
                }
                else {
                    $fname .= '[' . $this->get_field_name() . ']';   
                }

                $fid = str_replace('[', '--', $fid);
                $fid = str_replace(']', '--', $fid);

                //  Let's parse the field options a little to make life simpler
                //  Start with read only.
                ( array_key_exists( 'readonly', $fopt ) && $fopt['readonly'] ) ?
                    $readonly = 'readonly="readonly"' : '';
                //  Now disabled
                ( array_key_exists( 'disabled', $fopt ) && $fopt['disabled'] ) ?
                    $disabled = 'disabled="disabled"' : '';
                //  Required
                //  Now disabled
                ( array_key_exists( 'required', $fopt ) && $fopt['required'] ) ?
                    $required = 'required="required"' : '';
                //  Now default value
                array_key_exists('default', $fopt) ?
                    $default = $fopt['default'] : $default = null;
                //  And style
                array_key_exists('style', $fopt) ?
                    $style = $fopt['style'] : $style = '';
                //  Buttons
                array_key_exists( 'choices', $fopt ) ?
                    $choices = $fopt['choices'] : ABDWPSM_Settings_Manager::die_with_message( "The $ftype field type must have a \"<em>choices</em>\" field_option!  The <em>$fname</em> field does not." );
                //  Value override
                array_key_exists('value_override', $fopt) ?
                    $value_override = $fopt['value_override'] : $value_override = null;




                //  Now, let's get the value we're gonna put in the input.
                //  Unfortunately, there are several possibilities.
                //  Possibility #1: Data persistence from a previous failed submission attempt.
                if( !is_null( $this->data_persistence_value ) ) {
                    $cur_val = $this->data_persistence_value;

                    $this->data_persistence_value = null;   //  Clean up our mess
                }
                else {
                    //  Possibility #2: Value override from the user
                    if( is_null( $value_override ) ) {
                        //  Possibility #3: Existing value in the database
                        $cur_val = $this->get_stored_field_value();
                        if( !isset( $cur_val ) ) {  //  No value stored
                            //  Possibility #4: Default value
                            $cur_val = $default;
                        }
                        else {
                            $cur_val = $cur_val;  //  Something was in the database, use it.
                        }
                    }
                    else {
                        $cur_val = $value_override;   //  We have a user override.
                    }
                }


                //  If this is a checkbox, then the name must be in array notation
                if( $ftype == 'checkbox' ) {
                    $fname .= '[]';
                }


                //  There's a possibility $value contains an array of values or a
                //  single value of another type.  To make life simple, let's
                //  make everything an array.
                if( !is_array( $cur_val ) ) {
                    $cur_val = array( $cur_val );
                }

                //  Now, we're gonna loop through the "choices" and output them in the
                //  appropriate construct.
                //	Now, loop through $buttons and output them
				if ( $ftype == 'select' || $ftype == 'dropdown' ) {
					$end_tag = "</select>";
					echo "<select id='$fid' style='$style' name='$fname' $readonly $disabled>";
				}
				else {
					$end_tag = "</div>";
					echo "<div class='grouped_inputs_wrapper' id='$fid'>";
				}
					foreach( $choices as $label=>$value ) {
                        //  Boolean values cause problems... let's turn them into something
                        //  simpler... string versions of 1 and 0.  NOTE: Making them strings
                        //  is important because later we might be comparing to strings from
                        //  the database and ( 0 == "ABCDEFG" ) evaluates to true.
                        if( $value === true ) {
                            $value = '1';
                        }
                        else if ( $value === false ) {
                            $value = '0';
                        }

                        //	Is this choice supposed to be "checked"?
						foreach( $cur_val as $cv ) {
                            if ( $cv == $value && $cv != '' && !is_null( $cv ) ) {
								if( $ftype == 'select' || $ftype == 'dropdown' ) {
									$checked = 'selected="selected" ';
								}
								else {
									$checked = 'checked="checked" ';
								}
								break;
							}
							else {
								$checked = '';
							}
						}

                        //  Covert value to HTML entities
                        $value = htmlentities( $value, ENT_QUOTES );

                        //	Output the field
						if( $ftype == 'select' || $ftype == 'dropdown' ) {
							echo "<option value='$value' $checked>$label</option>";
						}
						else {
                            echo "<label><input $checked value='$value' name='$fname' type='$ftype' style='$style' $readonly $disabled /> $label</label><br />";
						}
					}
				echo $end_tag;

                //  And any description and example
                if( !empty( $ddesc ) ) {
                    echo "<p class='description'>$ddesc</p>";
                }
                if( !empty( $dex ) ) {
                    echo "<p class='description' style='color: #1E8CBE'>Example: $dex</p>";
                }
            }



        ///////////////////////////////////////////////
        /// Constructor, Accessor (Getter) Methods, ///
        /// and Modifier (Setter) Methods           ///
        ///////////////////////////////////////////////
        public function __construct( $field_options_array = array() ) {

            //  Default options
            $defaults = array(
                'field_name'                  => '',
                'type'                     => '',
                'display_name'             => '',
                'display_description'      => '',
                'example_entry'            => '',
                'field_options_array'      => array(),
                'validator_options_array'  => array(),
                'validation_callback'       => null
            );

            //  Make sure any passed arguments are valid
            if( !is_array( $field_options_array ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Section constructor will only take an array as a parameter. Got: <pre>' . print_r( $field_options_array, true ) . '</pre>'  );
                return;
            }

            //  Merge together the two arrays with the passed array taking precedence
            $foa = $field_options_array + $defaults;


            //  Save the other junk
            $this->set_display_name( $foa['display_name'] );
            $this->set_display_description( $foa['display_description'] );
            $this->set_display_example( $foa['example_entry'] );
            $this->set_type( $foa['type'] );
            $this->set_field_name( $foa['field_name'] );
            $this->set_field_options_array( $foa['field_options_array'] );
            $this->set_validator_options_array( $foa['validator_options_array'] );
            $this->set_validation_callback( $foa['validation_callback'] );
        }   //  end __construct()

        public function get_display_name() {
            return $this->my_display_name;
        }
        public function get_display_description() {
            return $this->my_display_description;
        }
        public function get_display_example() {
            return $this->my_display_example;
        }
        public function get_field_options_array() {
            return $this->my_field_options;
        }
        public function get_validator_options_array() {
            return $this->my_validator_options;
        }
        public function get_validation_callback() {
            return $this->my_validation_callback;
        }
        public function get_field_name() {
            return $this->my_field_name;
        }
        public function get_section() {
            return $this->my_section;
        }
        public function get_type() {
            return $this->my_type;
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
        public function set_display_example( $display_example ) {
            if( !is_string( $display_example ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $display_example, true ) . '</pre>' );
                return;
            }

            $this->my_display_example = $display_example;
        }
        public function set_field_options_array( $field_options ) {
            //  Field options can be an array or null.
            if( !is_array( $field_options ) && !is_null( $field_options ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected array or null!  Got: <pre>' . print_r( $field_options, true ) . '</pre>' );
                return;
            }

            $this->my_field_options = $field_options;
        }
        public function set_validator_options_array( $validator_options ) {
            //  Validator options can be an array or null.
            if( !is_array( $validator_options ) && !is_null( $validator_options ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected array or null!  Got: <pre>' . print_r( $validator_options, true ) . '</pre>' );
                return;
            }

            $this->my_validator_options = $validator_options;
        }
        public function set_validation_callback( $validation_callback ) {
            if( !is_callable( $validation_callback ) && !is_null( $validation_callback )  ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected array or null!  Got: <pre>' . print_r( $validation_callback, true ) . '</pre>' );
                return;
            }

            $this->my_validation_callback = $validation_callback;
        }
        public function set_field_name( $field_name ) {
            if( !is_string( $field_name ) && !is_array( $field_name ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string or array of strings.  Got: <pre>' . print_r( $field_name, true ) . '</pre>' );
                return;
            }

            //  We have one or more reserved field names used by this Settings API that
            //  we don't want to mess with.  Make sure $field_name isn't one of those.
            if( is_string( $field_name ) ) {
                $dont_push_these_red_buttons = array(
                    'oi_db_option_name',
                    'abd_sm_sanitization_already_complete'
                );

                foreach( $dont_push_these_red_buttons as $red_button ) {
                    if( strtolower( trim( $field_name ) ) == $red_button ) {
                        ABDWPSM_Settings_Manager::die_with_message( 'Invalid field DB name: <em>' . $field_name . '</em>. This name is reserved for Settings API operations.  Please choose another DB name.' );
                    }
                }
            }


            $this->my_field_name = $field_name;


            //  Check identifying property uniqueness (function dies if failed)
            $this->ip_uniqueness_check();
        }
        public function set_type( $type ) {
            if( !is_string( $type ) ) {
                ABDWPSM_Settings_Manager::die_with_message( 'Expected string.  Got: <pre>' . print_r( $type, true ) . '</pre>' );
                return;
            }

            $this->my_type = $type;
        }
        public function set_section_reference( $Section ) {
            //  Don't add to something that isn't an options group.
            if( !( $Section instanceof ABDWPSM_Section ) ) {
                //  Okay, it's not an instance of ABDWPSM_Options_Group, to make
                //  errors more helpful, what is it?
                $type = get_class( $Section );
                if( !$type ) {  //  It's not a class, it's something else... what?
                    $type = gettype( $Section );
                }
                else {  //  It is a class, let's tack on ' object' for readability.
                    $type .= ' object';
                }

                ABDWPSM_Settings_Manager::die_with_message( 'Expected ABDWPSM_Section object.  Got ' . $type . '.' );
                return;
            }

            //  Okay, if we're here, then we have a section, so set it.
            $this->my_section = $Section;
        }



    }   //  end class
}   //  end if( !class_exists( ...
