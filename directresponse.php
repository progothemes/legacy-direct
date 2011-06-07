<?php
/**
 * Template Name: Direct Response
 *
 * Direct Response page template.
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */
get_header(); 
$options = get_option('progo_options'); ?>
    <div id="container" class="container_12 direct">
        <div id="main" class="grid_8">
<?php if ( have_posts() ) while ( have_posts() ) : the_post();

$custom = get_post_meta($post->ID,'_progo');
$direct = $custom[0];

if ( $direct[plink] == 0 ) {
	// try to always have SOME product to show...
	$direct[plink] = progo_default_product_id();
}

$direct[productmeta] = get_post_meta($direct[plink],'_wpsc_product_metadata',true);
		
if(has_post_thumbnail($post->ID)) {
	// post Featured Image overwrites product image & bullet points...
	$pagetitle = esc_attr(the_title('','',false));
	$topimg = get_the_post_thumbnail( $post->ID, 'original', array('id'=>'topimg', 'alt'=>$pagetitle, 'title'=>$pagetitle));
	if($_SERVER['HTTPS'] == "on") {
		$topimg = str_replace('http:','https:',$topimg);
	}
	echo $topimg;
} else { ?>
<div id="prod">
<h1><?php the_title(); ?></h1>
<div class="grid_4" id="pimg"><?php progo_productimage($direct[plink]); ?></div>
<div class="grid_4" id="info"><?php
if ( $direct[plink] != 0 ) {
	$prod_info = wp_get_single_post($direct[plink]);
	$direct[getyours] = '<div id="getyours">'. esc_html($direct[getyours]) .'<br />'. progo_price($direct[plink]) .'</div>';
	$direct[prod_copy] = wp_kses($prod_info->post_content, array( 'br' => array(),'em' => array(), 'strong' => array(), 'ul' => array(), 'li' => array() ) );
	if(current_user_can('edit_pages')) {
		$direct[prod_copy] .= '<a href="'. get_bloginfo('url') .'/wp-admin/post.php?post='. absint($direct[plink]) .'&action=edit">Edit Product Info</a>';
	}
} else {
	if(current_user_can('edit_pages')) {
		$direct[getyours] = '<a href="'. get_bloginfo('url') .'/wp-admin/post-new.php?post_type=wpsc-product" id="getyours" title="This link shows up because you are currently logged in">Create your first product to get started</a>';
	} else {
		$direct[getyours] = '<div id="getyours">'. esc_html($direct[getyours]);
		$direct[getyours] .= '<br /><span class="m">$</span><span class="p">99</span><span class="c">95</span>';
		$direct[getyours] .= '</div>';
	}
	$direct[prod_copy] = '<ul><li>All it takes is one Product Image, and a Price, to Get Started</li><li>And a few Benefit Points for your Product</li><li>Product Benefit Points could be one or two lines of text</li></ul>';
} ?>
<?php echo $direct[getyours]; ?>
<div><?php echo $direct[prod_copy]; ?></div>
</div>
</div>
<?php } ?><div id="arrow"><span><?php echo wp_kses($direct[arrowd],array()); ?></span></div>
<?php do_action('progo_direct_after_arrow'); ?>
				<div id="bodycontent">
						<?php the_content(); ?>
	<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
				</div><!-- #post-## -->
<?php endwhile; ?>
			</div><!-- #main -->
            <div class="grid_4 omega" id="side">
            <form class="pform direct" enctype="multipart/form-data" action="<?php echo ($direct[plink]>0 ? get_permalink($post->ID) .'?progo_action=step2' : '" onsubmit="return false;"'); ?>" method="post" name="product_<?php echo absint($direct[plink]); ?>" id="product_<?php echo absint($direct[plink]); ?>">
            <table width="96%" cellpadding="0" cellspacing="0" class="wpsc_checkout_table">
            <tr><td valign="top" height="56"><h3 id="rh"><?php echo nl2br(esc_html($direct[rightheadline])); ?></h3></td></tr>
            <tr><td><input type="hidden" name="wpsc_ajax_action" value="add_to_cart" />
			<input type="hidden" name="product_id" value="<?php echo absint($direct[plink]); ?>" />
            <?php
            $includeshipping = true;
			if( ( absint( get_option( 'do_not_use_shipping' ) ) == 1 ) || ( $direct[productmeta][no_shipping] == 1 ) ) $includeshipping = false;
			progo_direct_form_fields( $includeshipping );
			if ( $includeshipping && function_exists('wpsc_checkout') ) {
				echo '<label class="cb"><input type="checkbox" id="edit" name="edit" /> Add different Shipping Address</label>';
			} ?>
            </td></tr>
            <tr><td class="cred"><?php echo $options['credentials']; ?></td></tr>
            <tr><td height="63"><?php echo progo_direct_submitbtn( $direct[plink], $direct[buynow] ); ?></td></tr>
            </table>
            </form>
            <?php get_sidebar(); ?>
            </div>
		</div><!-- #container -->
<?php get_footer(); ?>