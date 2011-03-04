<?php
/**
 * ProGo Themes' Testimonials Widget Class
 *
 * This widget is for positioning/removing the "Testimonials" block on Direct Response pages.
 * future versions may include listing default / overrideable testimonial here?
 * or rotating/random Testimonials from some Custom Post Type, perhaps?
 * modelled after Hybrid theme's widget definitions
 *
 * @since 1.0.57
 *
 * @package ProGo
 * @subpackage Direct
 */

class ProGo_Widget_Testimonials extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0.57
	 */
	function ProGo_Widget_Testimonials() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'test', 'description' => __( 'Direct Response page\'s Testimonial ', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-testimonials", __( 'ProGo : Testimonial', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0.57
	 */
	function widget( $args, $instance ) {
		extract($args);
		
		global $post;
		$custom = get_post_meta($post->ID,'_progo');
		$direct = $custom[0];
		
 		if(absint($direct[hidetest])==1) return;

		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Testimonial') : $instance['title'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		?><span class="lq">&ldquo;</span><?php echo nl2br(wp_kses($direct[testitxt],array('em'=>array(),'strong'=>array()))); ?>&rdquo;<br /><br />
 <div class="by"><?php echo nl2br(wp_kses($direct[testiauth],array('em'=>array(),'strong'=>array()))); ?></div>
		<?php
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 1.0.57
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 1.0.57
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}

?>