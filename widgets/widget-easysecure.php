<?php
/**
 * ProGo Themes' EasySecure Widget Class
 *
 * This widget is for positioning/removing the "Easy & Secure" block on Direct Response pages.
 * modelled after Hybrid theme's widget definitions
 *
 * @since 1.0.57
 *
 * @package ProGo
 * @subpackage Direct
 */

class ProGo_Widget_EasySecure extends WP_Widget {

	var $prefix;
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0.57
	 */
	function ProGo_Widget_EasySecure() {
		$this->prefix = 'progo';
		$this->textdomain = 'progo';

		$widget_ops = array( 'classname' => 'secure', 'description' => __( 'Show Payment Options &amp; Support info.', $this->textdomain ) );
		$this->WP_Widget( "{$this->prefix}-easysecure", __( 'ProGo : Easy &amp; Secure', $this->textdomain ), $widget_ops );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 1.0.57
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty($instance['title']) ? __('Easy &amp; Secure') : $instance['title'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
		$options = get_option('progo_options');
		
		$oot = '<img src="'. get_bloginfo('template_url') .'/images/weaccept.gif" alt="We Accept..." />';
		$oot .= '<span class="support">Customer Support: ';
		if($options['support_email']) {
			$oot .= '<a href="mailto:'. esc_attr($options['support']) .'">email us</a>';
		} else {
			$oot .= esc_html($options['support']);
		}
		$oot .= '</span>';
		$oot = apply_filters('progo_display_easysecure', $oot, $args, $instance);
		echo $oot;
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