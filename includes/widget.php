<?php
/**
 * This file contains the ABD widget declaration and handler functions.
 */

require_once ( ABD_ROOT_PATH . 'views/public-views.php' );
require_once ( ABD_ROOT_PATH . 'includes/db-manip.php' );

if( !class_exists( ABD_Widget ) ) {
    class ABD_Widget extends WP_Widget {
        function __construct() {
            parent::__construct(
                'abd_shortcode_widget', //  Base ID
                'Ad Blocking Detector', //  Widget Name
                array(                  //  args
                    'description' => 'Display an Ad Blocking Detector shortcode as a widget.'
                )
            );
        }

        public function widget( $args, $instance ) {
            echo $args['before_widget'];

            //  Output title
            $title = apply_filters( 'widget_title', ( !empty( $instance['shortcode_widget_title'] ) ? $instance['shortcode_widget_title'] : '' ), $instance, $this->id_base );

            if( !empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            //  Output shortcode
            if( isset( $instance['shortcode_id'] ) ) {
                $abd_id = $instance['shortcode_id'];
            }
            else {
                $abd_id = -1;
            }

            $output = ABD_Public_Views::get_shortcode_output( $abd_id );
            if( !is_string( $output ) ) {
                $output = '';
            }

            echo $output;

            echo $arts['after_widget'];
        }

        public function form( $instance ) {
            if( isset( $instance['shortcode_widget_title'] ) ) {
                $cur_title = $instance['shortcode_widget_title'];
            }
            else {
                $cur_title = '';
            }

            if( isset( $instance['shortcode_id'] ) ) {
                $cur_id = $instance['shortcode_id'];
            }
            else {
                $cur_id = '';
            }

            $shortcodes = ABD_Database::get_all_shortcodes();
            if( !is_array( $shortcodes ) ) {
                $shortcodes = array();
            }

            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'shortcode_widget_title' ); ?>">Title (optional):</label>
                <input class='widefat' type='text' name="<?php echo $this->get_field_name( 'shortcode_widget_title' ); ?>" value="<?php echo $cur_title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'shortcode_id' ); ?>">Shortcode to Display:</label>
                <select class='widefat' name="<?php echo $this->get_field_name( 'shortcode_id' ); ?>">
                    <?php
                    foreach( $shortcodes as $sc ) {
                        if( $sc['id'] == $cur_id ) {
                            $checked = 'selected="selected"';
                        }
                        else {
                            $checked = '';
                        }

                        if( !array_key_exists( 'name', $sc ) ) {    //  Huh? How did that happen. Well, skip so nothing breaks.
                            continue;
                        }
                        ?>
                        <option value="<?php echo $sc['id'] ?>" <?php echo $checked; ?>>
                            <?php echo $sc['name']; ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <?php
        }

        public function update( $new_i, $old_i ) {
            $instance = array();
            $instance['shortcode_id'] = ( !empty( $new_i['shortcode_id'] ) ) ? $new_i['shortcode_id'] : -1;
            $instance['shortcode_widget_title'] = ( !empty( $new_i['shortcode_widget_title'] ) ) ? $new_i['shortcode_widget_title'] : '';

            return $instance;
        }
    }   //  end class
}   //  end if( !class_exists( ...
