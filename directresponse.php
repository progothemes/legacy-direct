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
    <div id="container" class="container_12">
        <div id="main" role="main" class="grid_8">
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
	echo get_the_post_thumbnail( $post->ID, 'original', array('id'=>'topimg', 'alt'=>$pagetitle, 'title'=>$pagetitle));
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
            <form class="pform" enctype="multipart/form-data" action="<?php echo ($direct[plink]>0 ? get_permalink($post->ID) .'?progo_action=step2' : '" onsubmit="return false;"'); ?>" method="post" name="product_<?php echo absint($direct[plink]); ?>" id="product_<?php echo absint($direct[plink]); ?>">
            <h3 id="rh"><?php echo nl2br(esc_html($direct[rightheadline])); ?></h3>
            <input type="hidden" name="wpsc_ajax_action" value="add_to_cart" />
			<input type="hidden" name="product_id" value="<?php echo absint($direct[plink]); ?>" />
            <fieldset id="billing">
            <label for="firstname">First Name*</label><input type="text" name="firstname" size="27" maxlength="40" value="" class="text req" />
            <label for="lastname">Last Name*</label><input type="text" name="lastname" size="27" maxlength="40" value="" class="text req" />
            <label for="address">Address*</label><input type="text" name="address" size="27" maxlength="40" value="" class="text req" />
            <label for="city">City*</label><input type="text" name="city" size="27" maxlength="40" value="" class="text req" />
            <label for="state">State*</label><select name="state" class="req"><option></option><?php
			$states = array(15 => 'AK', 14 => 'AL', 17 => 'AR', 16 => 'AZ', 18 => 'CA', 19 => 'CO', 20 => 'CT', 61 => 'DC', 21 => 'DE', 22 => 'FL', 23 => 'GA', 24 => 'HI', 28 => 'IA', 25 => 'ID', 26 => 'IL', 27 => 'IN', 29 => 'KS', 30 => 'KY', 31 => 'LA', 34 => 'MA', 33 => 'MD', 32 => 'ME', 35 => 'MI', 36 => 'MN', 38 => 'MO', 37 => 'MS', 39 => 'MT', 46 => 'NC', 47 => 'ND', 40 => 'NE', 42 => 'NH', 43 => 'NJ', 44 => 'NM', 41 => 'NV', 45 => 'NY', 48 => 'OH', 49 => 'OK', 50 => 'OR', 51 => 'PA', 52 => 'RI', 53 => 'SC', 54 => 'SD', 55 => 'TN', 56 => 'TX', 57 => 'UT', 59 => 'VA', 58 => 'VT', 60 => 'WA', 63 => 'WI', 62 => 'WV', 64 => 'WY');
			foreach($states as $key => $val) echo '<option value="'. $key .'">'. $val .'</option>';
			?></select><span class="zip"><label for="zip" class="sh">Zip*</label><input type="text" name="zip" size="6" maxlength="10" value="" class="text req" /></span>
            <label for="phone">Phone*</label><input type="text" name="phone" size="27" maxlength="40" value="" class="text req" />
            <label for="email">Email*</label><input type="text" name="email" size="27" maxlength="40" value="" class="text req" />
            </fieldset>
            <?php	if ( ( absint( get_option( 'do_not_use_shipping' ) ) == 1 ) || ( $direct[productmeta][no_shipping] == 1 ) ) {
				echo '<div id="cred" class="noship">';
			} else { ?>
            <fieldset id="shipping" style="display:none; height: 231px">
            <div class="inf">Shipping Info</div>
            <label for="s_firstname">First Name</label><input type="text" name="s_firstname" size="27" maxlength="40" value="" class="text" />
            <label for="s_lastname">Last Name</label><input type="text" name="s_lastname" size="27" maxlength="40" value="" class="text" />
            <label for="s_address">Address</label><input type="text" name="s_address" size="27" maxlength="40" value="" class="text" />
            <label for="s_city">City</label><input type="text" name="s_city" size="27" maxlength="40" value="" class="text" />
            <label for="s_zip">Zip</label><input type="text" name="s_zip" size="27" maxlength="10" value="" class="text" />
            <label for="s_phone">Phone</label><input type="text" name="s_phone" size="27" maxlength="40" value="" class="text" />
            </fieldset>
            <label class="cb"><input type="checkbox" id="edit" name="edit" /> Add different Shipping Address</label>
            <div id="cred">
            <?php } 
			echo $options['credentials']; ?></div>
            <?php echo progo_direct_submitbtn( $direct[plink], $direct[buynow] ); ?>
            </form>
            <!--/div>
            <div id="secondary"-->
            <?php get_sidebar(); ?>
            </div>
		</div><!-- #container -->
<?php get_footer(); ?>