<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */

get_header();
$options = get_option('progo_options');
?>
        <div id="container" class="container_12">
<?php if ( have_posts() ) while ( have_posts() ) : the_post();
$showedit = true;
$maincols = 12;
$entrycols = 11;
if($post->post_parent > 0) {
	// check if we are on a CHECKOUT / TRANSACTION RESULTS / YOUR ACCOUNT page, and change EDIT PAGE link accordingly
	$par = get_post($post->post_parent, 'ARRAY_A');
	if($par['post_name']=='products-page') {
		$showedit = false;
		$maincols = $entrycols = 8;
	}
}
?>
			<div id="main" class="grid_<?php echo absint($maincols); ?>">
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<h1 class="page-title"><?php
if($post->post_name == 'checkout') {
	echo wp_kses($options[checkout], array('em'=>array(), 'strong'=>array()));
} else {
	the_title();
}
?></h1>
<div class="grid_<?php echo absint($entrycols); ?> entry">
<?php
the_content();
if(current_user_can('edit_pages')) {
if($showedit) {
edit_post_link('Edit this entry.', '<p>', '</p>');
} else {
	echo '<p><a href="'. get_bloginfo('url') .'/wp-admin/admin.php?page=wpsc-settings">Store Settings</a></p>';
}
}
?>
</div><!-- .entry -->
</div><!-- #post-## -->
</div><!-- #main -->
<?php endwhile; ?>
</div><!-- #container -->
<!-- #THISISTHEDEFAULTPAGE -->
<?php get_footer(); ?>
