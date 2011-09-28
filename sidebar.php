<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */
?>
<div id="secondary">
<?php
/* When we call the dynamic_sidebar() function, it'll spit out
 * the widgets for that widget area. If it instead returns false,
 * then the sidebar simply doesn't exist, so we'll hard-code in
 * some default sidebar stuff just in case.
 */
if ( ! dynamic_sidebar( 'widgets' ) ) :
global $post;
$options = get_option('progo_options');
$custom = get_post_meta($post->ID,'_progo');
$direct = $custom[0];
?>

 <div class="block secure"><h3 class="title"><span class="spacer">Easy &amp; Secure</span></h3><div class="inside">
 <img src="<?php bloginfo('template_url'); ?>/images/weaccept.gif" alt="We Accept..." />
 <span class="support">Customer Support: <?php if($options['support_email']) {
     echo '<a href="mailto:'. esc_attr($options['support']) .'">email us</a>';
 } else {
	if ( isset( $options['support'] ) ) {
		echo esc_html($options['support']);
	} else {
		echo '(858) 555-1234';
	}
} ?></span>
 </div></div>
 
 <div class="block share"><h3 class="title"><span class="spacer">Share</span></h3><div class="inside">
 <?php if (function_exists('sharethis_button')) {
	 sharethis_button();
	 } else { ?>
 <a name="fb_share" type="icon" href="http://www.facebook.com/sharer.php">Share</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
 <a href="http://twitter.com/share?url=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;text=Check%20Out%20This%20Great%20Product!%20" class="twitter" target="_blank">Tweet</a>
     <?php } ?>
 </div></div>
 
 <?php
 if(absint($direct[hidetest])==0) { ?>
 <div class="block test"><h3 class="title"><span class="spacer">Testimonial</span></h3><div class="inside"><span class="lq">&ldquo;</span>
 <?php if ( progo_previewcheck() == false ) {
 echo nl2br(wp_kses($direct[testitxt],array('em'=>array(),'strong'=>array()))); ?>&rdquo;<br /><br />
 <div class="by"><?php echo nl2br(wp_kses($direct[testiauth],array('em'=>array(),'strong'=>array()))); ?>
 <?php } else {
	echo __('Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation'). '&rdquo;<br /><br /><div class="by">John Q Smith<br />Arcata, CA';
 } ?>
 </div></div></div>
 <?php }
 
 endif; // end primary widget area ?>
</div>