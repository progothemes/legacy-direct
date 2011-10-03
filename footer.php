<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */
?>
	</div><!-- #page -->
	<div id="ftr" class="container_12">
    <div class="grid_8">
<?php $fmenu = wp_nav_menu( array( 'container' => 'false', 'theme_location' => 'footer', 'echo' => '0' ) );
$fmenu = str_replace('</li>','&nbsp;&nbsp;|&nbsp;&nbsp;</li>',substr($fmenu,0,strrpos($fmenu,'</li>'))) . "</li>\n</ul>";
echo $fmenu;
echo '<br />';
$options = get_option('progo_options');
if ( isset( $options['copyright'] ) ) {
	echo wp_kses($options['copyright'],array());
} else {
	echo '&copy; Copyright '. date('Y') .', All Rights Reserved';
}
?>
</div><?php if ( progo_previewcheck() ) echo '</div>'; ?>
<div class="grid_4 right">Powered by <a href="http://www.wordpress.org" target="_blank">WordPress</a>. Designed by <a href="http://www.progo.com/" title="WordPress Themes" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/logo_admin.png" alt="WordPress Themes by ProGo" /></a></div>
</div><!-- #ftr -->
</div><!-- #wrap -->

<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
</body>
</html>
