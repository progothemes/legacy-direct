<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query. 
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */

get_header();
if ( progo_previewcheck() == false ) {
?>
        <div id="container" class="container_12">
			<div id="main" class="grid_8">

			<?php
			/* Run the loop to output the posts.
			 * If you want to overload this in a child theme then include a file
			 * called loop-index.php and that will be used instead.
			 */
			 get_template_part( 'loop', 'index' );
			?>
			</div><!-- #main -->
		</div><!-- #container -->

<?php get_sidebar(); 
} else { // PREVIEW mode ?>
    <div id="container" class="container_12 direct">
        <div id="main" class="grid_8">
        	<div id="prod">
                <h1>Write a Headline that Captivates</h1>
                <div class="grid_4" id="pimg"><?php progo_productimage(); ?></div>
                <div class="grid_4" id="info">
                    <div id="getyours"><?php _e('Get Yours Today'); ?><br /><span class="m">$</span><span class="p">99</span><span class="c">95</span></div>
                    <div>
                        <ul>
                            <li>All it takes is one Product Image, and a Price, to Get Started</li>
                            <li>And a few Benefit Points for your Product</li>
                            <li>Product Benefit Points could be one or two lines of text</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="arrow"><span><?php _e('ARROW HEADLINE GOES HERE'); ?></span></div>
            <div id="bodycontent">
                <?php echo apply_filters('the_content', progo_defaultdirectcontent()); ?>
            </div>
        </div><!-- #main -->
        <div class="grid_4 omega" id="side">
            <form class="pform direct" enctype="multipart/form-data" action="" onsubmit="return false;" method="post" name="product_0" id="product_0">
            <table width="96%" cellpadding="0" cellspacing="0" class="wpsc_checkout_table">
            <tr><td valign="top" height="56"><h3 id="rh"><?php _e('Friendly Urgent Statement Leading Into Form'); ?></h3></td></tr>
            <tr><td>
            <h3><?php _e('Your Direct Response Product Purchase Form will appear here'); ?>
            </td></tr>
            <tr><td class="cred">&nbsp;</td></tr>
            <tr><td height="63">&nbsp;</td></tr>
            </table>
            </form>
            <?php get_sidebar(); ?>
            </div>
		</div><?php
}
get_footer(); ?>
