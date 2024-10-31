<?php

/*
Plugin Name: Random Excerpts Fader
Plugin URI: http://www.jackreichert.com/plugins/random-excerpts-fader/
Description: Creates a widget that takes randomly a number of excerpts from a category of your choice and fades them in and out. Perfect for displaying testimonials.
Version: 2.4.1
Author: Jack Reichert
Author URI: http://www.jackreichert.com/
License: GPLv3
*/

class reFader_widget extends WP_Widget {

	/**
	 * reFader_widget constructor.
	 */
	function __construct() {    // The widget construct. Initiating our plugin data.
		$widgetData = array(
			'classname'   => 'reFader_widget',
			'description' => __( 'Display excerpts from a category of your choice and fades them in and out...' )
		);
		parent::__construct( 'reFader_widget', __( 'Random Excerpts Fader' ), $widgetData );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) { // Displays the widget on the screen.
		echo $args['before_widget'];
		echo $args['before_title'] . $instance['title'] . $args['after_title'];
		$widget_instance = new reFader( $instance );
		echo $widget_instance->buildWidget();
		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) { // Updates the settings.
		return $new_instance;
	}

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {    // The admin form.
		$defaults = array(
			'title'    => 'Random Excerpts',
			'amount'   => 5,
			'cat'      => 0,
			"type"     => "post",
			'length'   => 50,
			'duration' => 5000,
			'linked'   => 'no'
		);

		$post_types = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		), 'objects' );

		$ref_categories = get_categories( array(
			'hide_empty' => '0'
		) );

		$instance = wp_parse_args( $instance, $defaults ); ?>
        <div id="reFader-admin-panel">
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget title:</label>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"
                       id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo $instance['title']; ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'type' ); ?>">Post Type:</label>
                <select name="<?php echo $this->get_field_name( 'type' ); ?>"
                        id="<?php echo $this->get_field_id( 'type' ); ?>">
                    <option value="post" <?php echo selected( 'post', $instance['type'] ); ?>>Posts
                    </option>
					<?php
					foreach ( $post_types as $type ) {
						echo '<option value="' . $type->name . '" ' . selected( $type->name, $instance['type'] ) . '>' . $type->label . "</option>\n";
					} ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'cat' ); ?>">Category:</label>
                <select name="<?php echo $this->get_field_name( 'cat' ); ?>"
                        id="<?php echo $this->get_field_id( 'cat' ); ?>">
                    <option value="0" <?php echo selected( '0', $type->name, $instance['cat'] ); ?>>All Categories
                    </option>
					<?php
					foreach ( $ref_categories as $cat ) {
						echo '<option value="' . $cat->cat_ID . '" ' . selected( $cat->cat_ID, $instance['cat'] ) . '>' . $cat->cat_name . "</option>\n";
					} ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'amount' ); ?>">Number of posts: (-1 for all)</label>
                <input type="text" size="2" name="<?php echo $this->get_field_name( 'amount' ); ?>"
                       id="<?php echo $this->get_field_id( 'amount' ); ?>" value="<?php echo $instance['amount']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'length' ); ?>">Excerpt Word Length:</label>
                <input type="text" size="3" name="<?php echo $this->get_field_name( 'length' ); ?>"
                       id="<?php echo $this->get_field_id( 'length' ); ?>" value="<?php echo $instance['length']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'duration' ); ?>">Fade duration:</label>
                <input type="text" size="5" name="<?php echo $this->get_field_name( 'duration' ); ?>"
                       id="<?php echo $this->get_field_id( 'duration' ); ?>"
                       value="<?php echo $instance['duration']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'use_featured' ); ?>">
                    Use featured image instead of excerpt?</label>
                <select name="<?php echo $this->get_field_name( 'use_featured' ); ?>"
                        id="<?php echo $this->get_field_id( 'use_featured' ); ?>">
                    <option
                            value="yes" <?php echo( ( isset( $instance['use_featured'] ) && $instance['use_featured'] == 'yes' ) ? 'selected="selected"' : '' ); ?>>
                        Yes
                    </option>
                    <option
                            value="no" <?php echo( ( isset( $instance['use_featured'] ) && $instance['use_featured'] != 'yes' ) ? 'selected="selected"' : '' ); ?>>
                        No
                    </option>
                </select><br>
                <label for="<?php echo $this->get_field_id( 'featured_size' ); ?>">Which size?</label>
				<?php $featured_sizes = get_intermediate_image_sizes(); ?>
                <select name="<?php echo $this->get_field_name( 'featured_size' ); ?>"
                        id="<?php echo $this->get_field_id( 'featured_size' ); ?>">
					<?php foreach ( $featured_sizes as $ind => $f_size ) : ?>
                        <option
                                value="<?php echo $ind; ?>" <?php echo( ( isset( $instance['featured_size'] ) && $instance['featured_size'] == $ind ) ? 'selected="selected"' : '' ); ?>><?php echo $f_size; ?></option>
					<?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'linked' ); ?>">Link title to post?</label>
                <select name="<?php echo $this->get_field_name( 'linked' ); ?>"
                        id="<?php echo $this->get_field_id( 'linked' ); ?>">
                    <option value="yes" <?php echo( ( isset( $instance['linked'] ) && $instance['linked'] == 'yes' ) ? 'selected="selected"' : '' ); ?>>
                        Yes
                    </option>
                    <option value="no" <?php echo( ( isset( $instance['linked'] ) && $instance['linked'] != 'yes' ) ? 'selected="selected"' : '' ); ?>>
                        No
                    </option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'url' ); ?>">Link all to one url:</label>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'url' ); ?>"
                       id="<?php echo $this->get_field_id( 'url' ); ?>"
                       value="<?php echo isset( $instance['url'] ) && $instance['url']; ?>"/>
            </p>
        </div>
	<?php }

}

class reFader {
	private $options;

	/**
	 * reFader constructor.
	 *
	 * @param array $options
	 */
	function __construct( $options = array() ) {
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	function buildWidget() {
		$excerpts     = get_posts( array(
			'post_type'      => ( isset( $this->options['type'] ) ) ? $this->options['type'] : 'post',
			'posts_per_page' => $this->options['amount'],
			'orderby'        => 'rand',
			'category'       => ( isset( $this->options['cat'] ) && 0 < $this->options['cat'] ) ? $this->options['cat'] : 0
		) );
		$all_excerpts = '';
		foreach ( $excerpts as $excerpt ) {
			$all_excerpts .= '<p>';
			if ( $this->options['use_featured'] == 'yes' && has_post_thumbnail( $excerpt->ID ) ) {
				$featured_sizes = get_intermediate_image_sizes();
				$all_excerpts .= get_the_post_thumbnail( $excerpt->ID, $featured_sizes[ $this->options['featured_size'] ] );
			} else {
				$all_excerpts .= ( ( $this->options['length'] != "-1" ) ? $this->truncWords( $excerpt->post_content, intval( $this->options['length'] ) ) : $excerpt->post_content );
			}
			$all_excerpts .= '<span class="testimonial-title">' . ( ( $this->options['linked'] == 'yes' || $this->options['url'] != '' ) ? '<a href="' . ( ( $this->options['url'] != '' ) ? $this->options['url'] : get_permalink( $excerpt->ID ) ) . '">' . $excerpt->post_title . '</a>' : $excerpt->post_title ) . '</span>';
			$all_excerpts .= '</p>';
		}

		return '<div class="RandomExcerpts">' .
		       $all_excerpts .
		       '<div class="duration" style="display:none;">' . $this->options['duration'] . '</div>' .
		       '</div>';
	}

	/**
	 * @param $string
	 * @param int $words
	 *
	 * @return string
	 */
	function truncWords( $string, $words = 55 ) { //creates custom size excerpt
		$string = explode( ' ', strip_tags( $string ) );
		if ( count( $string ) > $words ) {
			return implode( ' ', array_slice( $string, 0, $words ) );
		}

		return implode( ' ', $string );
	}


}

/**
 * @param $atts
 *
 * @return string
 */
function reFader_shortcode( $atts ) {
	$defaults = array(
		"title"         => "Random Excerpts",
		"type"          => "post",
		"cat"           => "0",
		"amount"        => "5",
		"length"        => "50",
		"duration"      => "5000",
		"use_featured"  => "no",
		"featured_size" => "thumbnail",
		"linked"        => "yes",
		"url"           => ""
	);

	$atts = is_array($atts) ? $atts : array();
	$merged          = array_merge( $defaults, $atts );
	$widget_instance = new reFader( $merged );

	return $widget_instance->buildWidget();
}

function reFaderScripts() {
	wp_enqueue_script( 'reFader_js', plugins_url( 'RandomExcerptsFader.js', __FILE__ ), array( 'jquery' ) );
	wp_register_style( 'reFaderStylesheet', plugins_url( 'RandomExcerptsFader.css', __FILE__ ) );
	wp_enqueue_style( 'reFaderStylesheet' );
}

add_shortcode( 'reFader', 'reFader_shortcode' );
add_action( 'widgets_init', create_function( '', 'return register_widget("reFader_widget");' ) );
add_action( 'wp_enqueue_scripts', 'reFaderScripts' );
