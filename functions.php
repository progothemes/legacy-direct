<?php
/**
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 *
 * Defines all the functions, actions, filters, widgets, etc., for ProGo Themes' Direct Response theme.
 *
 * Some actions for Child Themes to hook in to are:
 * progo_frontend_scripts, progo_frontend_styles, progo_direct_after_arrow (called on directresponse.php page)
 *
 * Some overwriteable functions ( wrapped by "if(!function_exists(..." ) are:
 * progo_sitelogo, progo_direct_submitbtn, progo_posted_on, progo_posted_in, progo_productimage, progo_gateway_cleanup, progo_prepare_transaction_results,
 * progo_admin_menu_cleanup, progo_welcome, progo_custom_login_logo, progo_custom_login_url, progo_metabox_cleanup, progo_colorschemes ...
 *
 * Most Action / Filters hooks are set in the progo_setup function, below. overwriting that could cause quite a few things to go wrong.
 */

$content_width = 650;

global $progo_direct_db_version;
$progo_direct_db_version = "1.0";

/** Tell WordPress to run progo_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'progo_setup' );

if ( ! function_exists( 'progo_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_theme_support( 'post-thumbnails' ) To add support for post thumbnails.
 *
 * @since Direct 1.0
 */
function progo_setup() {
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style( 'css/editor-style.css' );
	
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'footer' => 'Footer Links',
	) );
	
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'large', 650, 413, true );
	
	// add custom actions
	add_action( 'admin_init', 'progo_admin_init' );
	add_action( 'widgets_init', 'progo_direct_widgets' );
	add_action( 'admin_menu', 'progo_admin_menu_cleanup' );
	add_action( 'login_head', 'progo_custom_login_logo' );
	add_action( 'login_headerurl', 'progo_custom_login_url' );
	add_action( 'save_post', 'progo_save_meta' );
	add_action('wp_print_scripts', 'progo_add_scripts');
	add_action('wp_print_styles', 'progo_add_styles');
	add_action( 'admin_notices', 'progo_admin_notices' );
	add_action( 'wp_before_admin_bar_render', 'progo_admin_bar_render' );
	add_action( 'get_header', 'progo_header_check' );
	
	// add custom filters
//	add_filter( 'parse_query', 'progo_directresponse_query' );
	add_filter( 'the_post', 'progo_directresponse_post' );
	add_filter( 'default_content', 'progo_set_default_body' );
	add_filter( 'site_transient_update_themes', 'progo_update_check' );
	add_filter( 'favorite_actions', 'progo_favorite_actions' );
	add_filter( 'admin_post_thumbnail_html', 'progo_admin_post_thumbnail_html' );
	add_filter( 'wpsc_pre_transaction_results', 'progo_prepare_transaction_results' );
	add_filter( 'wp_mail_content_type', 'progo_mail_content_type' );
	
	if ( !is_admin() ) {
		// brick it if not activated
		if ( get_option( 'progo_direct_apiauth' ) != 100 ) {
			add_action( 'template_redirect', 'progo_to_twentyten' );
		}
	}
}
endif;

/********* Front-End Functions *********/

if ( ! function_exists( 'progo_sitelogo' ) ):
/**
 * prints out the HTML for the #logo area in the header of the front-end of the site
 * wrapped so child themes can overwrite if desired
 * @since Direct 1.0.46
 */
function progo_sitelogo() {
	$options = get_option( 'progo_options' );
	$progo_logo = $options['logo'];
	$upload_dir = wp_upload_dir();
	$dir = trailingslashit($upload_dir['baseurl']);
	$imagepath = $dir . $progo_logo;
	if($progo_logo) {
		echo '<table id="logo"><tr><td><img src="'. esc_attr( $imagepath ) .'" alt="'. esc_attr( get_bloginfo( 'name' ) ) .'" /></td></tr></table>';
	} else {
		echo '<div id="logo">'. esc_html( get_bloginfo( 'name' ) ) .'<span class="g"></span></div>';
	}
}
endif;
if ( ! function_exists( 'progo_direct_submitbtn' ) ):
/**
 * helper function to allow children themes to overwrite the SUBMIT button html on Direct Response Pages
 * @param (int) ID of Product
 * @param text for the button
 * @return HTML for the submit button
 * @since Direct 1.0.45
 */
function progo_direct_submitbtn( $pid=0, $btxt = 'BUY NOW' ) {
	return function_exists('wpsc_checkout') ? '<input type="submit" id="product_'. absint($pid) .'_submit_button" name="Buy" value="'. esc_html($btxt) .'" class="buynow sbtn" />' : '';
}
endif;
if ( ! function_exists( 'progo_posted_on' ) ):
/**
 * Prints HTML with meta information for the current post—date/time and author.
 * @since ProGo Direct Response 1.0
 */
function progo_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'progo' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'progo' ), get_the_author() ),
			get_the_author()
		)
	);
}
endif;
if ( ! function_exists( 'progo_posted_in' ) ):
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 * @since ProGo Direct Response 1.0
 */
function progo_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'progo' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}
endif;
if ( ! function_exists( 'progo_productimage' ) ):
/**
 * echoes html for product image, or default product image if there isnt one
 * @since Direct 1.0.31
 */
function progo_productimage($pID) {
	if(($pID==0) || has_post_thumbnail( $pID ) == false) {
		echo '<img src="'. get_bloginfo('template_url') .'/images/productimage.gif" alt="Product Image" />';
	} else {
		echo get_the_post_thumbnail( $pID, 'original');
	}
}
endif;
if ( ! function_exists( 'progo_gateway_cleanup' ) ):
/**
 * checkout page FIELD LABEL formatting function
 * returns the PAYMENT GATEWAY html with revised labels
 * @param gate_code
 * @return revised gate_code html
 * @since Direct 1.0
 */
function progo_gateway_cleanup( $gate_code ) {
	$gate_code = str_replace( array( 'Credit Card Number', 'Credit Card Expiry' ), array( 'Card Number', 'Expiration' ), $gate_code );
	return '<fieldset class="check"><table width="100%" height="155" cellpadding="0" cellspacing="0">'. $gate_code .'</table></fieldset>';
}
endif;
if ( ! function_exists( 'progo_prepare_transaction_results' ) ):
/**
 * filter for wpsc_pre_transaction_results
 * @since Direct 1.0.49
 */
function progo_prepare_transaction_results() {
	global $purchase_log;
	$options = get_option( 'progo_options' );
	$purchase_log['find_us'] = '<table><tr class="firstrow"><td>Our Company Info</td></tr><tr><td>'. esc_html( $options['companyinfo'] ) .'</td></tr></table>';
}
endif;
if ( ! function_exists( 'progo_direct_form_fields' ) ):
/**
 * outputs the BILLING/SHIPPING ADD TO CART form on the top right of DIRECT RESPONSE pages
 * @param whether or not to include the SHIPPING fields
 * @param whether or not to hide (display:none) both fieldsets
 * @since Direct 1.0.59
 */
function progo_direct_form_fields( $includeshipping = true, $hideboth = false ) {
	if ( ! function_exists('wpsc_checkout') ) {
		if( current_user_can( 'activate_plugins' ) ) {
			echo '<h3>Please Install/Activate the WP E-Commerce Plugin</h3><br /><br /><a href="'. get_bloginfo('url') .'/wp-admin/plugins.php" class="sbtn buynow">Manage Plugins</a>';
		}
		return;
	}
	
	global $wpsc_checkout;
	$wpsc_checkout = new wpsc_checkout();
	$formfields = array(
		'billing' => array(),
		'shipping' => array()
	);
	foreach ( $wpsc_checkout->checkout_items as $check ) {
		if( strpos($check->unique_name, 'billing') === 0 ) {
			$formfields[billing][] = $check;
		} elseif( strpos($check->unique_name, 'shipping') === 0 ) {
			$formfields[shipping][] = $check;
		}
	}
	
	foreach($formfields as $k => $fieldset) {
		if ( $k == 'billing' ) {
			echo '<fieldset id="billing"'. ($hideboth ? ' style="display:none"' : '') .'>';
		} else {
			 if ( $includeshipping == false ) return;
			 ?>
				<fieldset id="shipping" style="display:none">
				<div class="inf">Shipping Info</div><?php
		}
	
		$shortenzip = false;
		echo '<pre style="display:none">'. print_r($wpsc_checkout, true) .'</pre>';
	foreach( $fieldset as $i => $item ) {
		if ( $item->type == 'address' ) $item->type = 'text';
		$wpsc_checkout->checkout_item = $item;
		if(wpsc_disregard_shipping_state_fields() || wpsc_disregard_billing_state_fields()) { ?><div style="display:none">
			 <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
			 <?php echo wpsc_checkout_form_name();?>
			 </label>
			 <?php echo wpsc_checkout_form_field();?>
			  <?php if(wpsc_the_checkout_item_error() != ''): ?>
					 <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
			 <?php endif; ?></div>
		<?php
        } else {
		if( (strpos($item->unique_name, 'postcode') > 0) && $shortenzip ) {
				echo '<span class="zip">';
			}
		?>
		<label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
		<?php echo str_replace( 'Postal Code', 'Zip', wpsc_checkout_form_name() );?>
		</label>
		<?php
		
		$fieldcode = wpsc_checkout_form_field();
		
		if ( in_array($item->type, array( 'country', 'delivery_country' ) ) ) {
			// add extra STATE label
			$lookfor = "<div id='region_select_".$item->id."'><select";
			$statestart = strpos($fieldcode, $lookfor);
			if( $statestart > 0 ) {
				
				$selected_country = $_SESSION['wpsc_delivery_country'];
				$selected_region = $_SESSION['wpsc_delivery_region'];
			echo "<div style='display:none' title='sc $selected_country sr $selected_region'></div>";
				if ( empty( $selected_country ) )
					$selected_country = esc_attr( get_option( 'base_country' ) );
				if ( empty( $selected_region ) )
					$selected_region = esc_attr( get_option( 'base_region' ) );
				global $wpdb;
				$region_list = $wpdb->get_results( "SELECT `" . WPSC_TABLE_REGION_TAX . "`.* FROM `" . WPSC_TABLE_REGION_TAX . "`, `" . WPSC_TABLE_CURRENCY_LIST . "`  WHERE `" . WPSC_TABLE_CURRENCY_LIST . "`.`isocode` IN('" . $selected_country . "') AND `" . WPSC_TABLE_CURRENCY_LIST . "`.`id` = `" . WPSC_TABLE_REGION_TAX . "`.`country_id`", ARRAY_A );
				
				$statescode = substr($fieldcode, $statestart + strlen($lookfor));
				$states = array();
				$abbr = array();
				foreach($region_list as $s) {
					$states[] = $s['name'];
					$abbr[] = $s['code'];
				}
				$statescode = str_replace($states,$abbr,$statescode);
				// for whatever reason, wpsc does not set the state by default, so
				if(strpos($statescode,"selected") === false) {
					$statescode = str_replace("value='$selected_region'", "value='$selected_region' selected='selecte'", $statescode);
				}
				
				if(strpos($fieldset[$i+1]->unique_name, 'postcode') > 0) {
					$shortenzip = true;
					echo '<!-- shorterzipcoming -->';
				}
				$fieldcode = substr($fieldcode, 0, $statestart) ."<label class='statelabel label". $item->id ."'>State <span class='asterix'>*</span></label><div id='region_select_".$item->id."'><select". ($shortenzip ? " style='width:54px'" : ""). $statescode;
				$fieldcode = str_replace('set_shipping_country', 'progo_set_shipping_country', $fieldcode);
				$fieldcode = str_replace('set_billing_country', 'progo_set_billing_country', $fieldcode);
			}
		} else {
			// ProGo custom css classes
			$classes = array();
			if ( $item->type == 'text' ) $classes[] = 'txt';
			if ( $item->mandatory == 1 ) $classes[] = 'req';
			
			$classes = implode(' ',$classes) .' ';
			$fieldcode = str_replace("class='", "class='". $classes, $fieldcode);
		}
		echo $fieldcode;
		?>
		<?php if(wpsc_the_checkout_item_error() != ''): ?>
		<p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
		<?php endif;
		
			if( (strpos($item->unique_name, 'postcode') > 0) && $shortenzip ) {
				echo '</span>';
			}
		}
	} ?>
	</fieldset>
	<?php 
	}
}
endif;
/********* Back-End Functions *********/

if ( ! function_exists( 'progo_admin_menu_cleanup' ) ):
/**
 * hooked to 'admin_menu' by add_action in progo_setup()
 * @since Direct 1.0
 */
function progo_admin_menu_cleanup() {
	global $menu;
	$restricted = array( 'Posts', 'Links', 'Comments' );
	end ( $menu );
	while ( prev( $menu ) ) {
		$value = explode(' ', $menu[key( $menu )][0] );
		if ( in_array( $value[0] != NULL ? $value[0] : "" , $restricted ) ) {
			unset( $menu[key( $menu )] );
		}
	}
	global $submenu;
	
	add_menu_page( 'Welcome', 'ProGo Themes', 'edit_theme_options', 'progo_welcome', 'progo_welcome', get_bloginfo( 'template_url' ) .'/images/logo_menu.png', 5 );
	add_submenu_page( 'progo_welcome', 'Welcome', 'Welcome', 'edit_theme_options', 'progo_welcome', 'progo_welcome' );
	add_submenu_page( 'progo_welcome', 'Site Settings', 'Site Settings', 'edit_theme_options', 'progo_site_settings', 'progo_site_settings_page' );
	add_submenu_page( 'progo_welcome', 'Store Settings', 'Store Settings', 'edit_theme_options', 'wpsc-settings', 'options-general.php' );
	add_submenu_page( 'progo_welcome', __( 'Store Sales', 'wpsc' ), __( 'Store Sales', 'wpsc' ), 'administrator', 'wpsc-sales-logs', 'wpsc_display_sales_logs' );
	add_submenu_page( 'progo_welcome', __( 'Store Upgrades', 'wpsc' ), __( 'Store Upgrades', 'wpsc' ), 'administrator', 'wpsc-upgrades', 'wpsc_display_upgrades_page' );
	add_submenu_page( 'progo_welcome', 'Footer Menu', 'Footer Menu', 'edit_theme_options', 'nav-menus.php' );
	
	// and remove STORE SALES and STORE UPGRADES from DASHBOARD menu?
	if ( isset( $submenu['index.php'] ) ) {
		foreach ( $submenu['index.php'] as $ind => $sub ) {
			// sub[0] could change language so check the callback fn instead
			if ( in_array( $sub[2], array( 'wpsc-sales-logs', 'wpsc-ugrades' ) ) ) {
				unset($submenu['index.php'][$ind]);
			}
		}
	}
	
	// add an extra dividing line...
	$menu[6] = $menu[4];
	
	$num = count( get_posts( 'post_type=page&meta_key=_wp_page_template&meta_value=directresponse.php' ) );
	if ( $num > 0 ) {
		add_pages_page( 'Direct Pages', 'Direct Pages', 'edit_pages', 'edit.php?s&post_type=page&progo_template=directresponse.php' );
	
		if ( isset( $submenu['edit.php?post_type=page'] ) ) {
			$submenu['edit.php?post_type=page'][5][0] = 'All Pages';
			
			$submenu['edit.php?post_type=page'][] = $submenu['edit.php?post_type=page'][10];
			unset( $submenu['edit.php?post_type=page'][10] );
		}
	}
	
	// and lets move MEDIA to after PRODUCTS
	$menu[50] = $menu[10];
	unset($menu[10]);
	// and lets move PRODUCTS to before PAGES
	$menu[10] = $menu[26];
	unset($menu[26]);
	
	//wp_die('<pre>'. print_r($menu,true) .'</pre>');
}
endif;
if ( ! function_exists( 'progo_welcome' ) ):
/**
 * ProGo Themes' Direct Response WELCOME page function
 * from add_menu_page( 'Welcome', 'ProGo Themes', 'edit_theme_options', 'progo_welcome'...
 * in progo_admin_menu_cleanup()
 * @since Direct 1.0
 */
function progo_welcome() {
	//must check that the user has the required capability 
	if ( current_user_can('edit_theme_options') == false) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div><h2>Welcome</h2>
    <div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder">
    	<div class="postbox-container" style="width:64%">
        	<div id="progo_welcome" class="postbox">
            	<h3 class="hndle"><span>Welcome</span></h3>
                <div class="inside">
                    <p><img src="<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png" style="float:right; margin: 0 0 21px 21px" alt="ProGo Themes" />ProGo Themes are easy and quick to set up using our step-by-step process below. <a href="http://www.progo.com/ProGo_QuickStartGuide.pdf" target="_blank">Click here to download the ProGo Themes Quick Start Guide (PDF)</a></p>
                    <?php progo_dashboard_widget_function(); ?>
                </div>
            </div>
         </div>
         <div class="postbox-container" style="width:35%">
        	<div id="progo_plugs" class="postbox">
            	<h3 class="hndle"><span>Recommended Plugins</span></h3>
                <div class="inside">
                <?php if ( function_exists( 'alex_recommends_widget' ) ) {
					alex_recommends_widget();
				} else { ?>
                    <p>The following plugins can help improve various aspects of your WordPress / ProGo Themes site:</p>
                    <ul style="list-style:outside; padding: 0 1em">
                    <?php
					$pRec = array();
					$pRec[] = array('name'=>'All in One SEO Pack','stub'=>'all-in-one-seo-pack','desc'=>'Out-of-the-box SEO. Easily control your pages\' keywords / meta description, and more');
					$pRec[] = array('name'=>'ShareThis','stub'=>'share-this','desc'=>'Let your visitors share your Products with others, posting to Facebook/Twitter/social bookmarking sites, and emailing to friends');
					$pRec[] = array('name'=>'Ultimate Google Analytics','stub'=>'ultimate-google-analytics','desc'=>'Add Google Analytics to your site, with options to track external links, mailto\'s, and downloads');
					$pRec[] = array('name'=>'Google XML Sitemaps','stub'=>'google-sitemap-generator','desc'=>'Generate an XML sitemap to help search engines like Google, Yahoo, Bing and Ask.com better index your site');
					$pRec[] = array('name'=>'WB DB Backup','stub'=>'wp-db-backup','desc'=>'On-demand backup of your WordPress database');
					$pRec[] = array('name'=>'Duplicate Post','stub'=>'duplicate-post','desc'=>'Add functionality to Save Page As...');
					$pRec[] = array('name'=>'Gold Cart for WP e-Commerce','stub'=>'','desc'=>'Extend your WP e-Commerce store with additional payment gateways and multiple product image');
					
					foreach( $pRec as $plug ) {
						echo '<li>';
						if ( $plug['name'] == 'Gold Cart for WP e-Commerce' ){
							echo '<a title="Learn more about '. esc_attr( $plug['name'] ) .'" target="_blank" href="http://getshopped.org/extend/premium-upgrades/premium-upgrades/gold-cart-plugin/">';
						} else echo '<a title="Learn more &amp; install '. esc_attr( $plug['name'] ) .'" class="thickbox" href="'. get_bloginfo('url') .'/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin='. $plug['stub'] .'&amp;TB_iframe=true&amp;width=640&amp;height=560">';
						echo esc_html($plug['name']) .'</a> : '. esc_html($plug['desc']) .'</li>';
					}
					?>
                    </ul>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    </div>
	<?php
}
endif;
if ( ! function_exists( 'progo_custom_login_logo' ) ):
/**
 * hooked to 'login_head' by add_action in progo_setup()
 * @since Direct 1.0
 */
function progo_custom_login_logo() {
	if ( get_option('progo_logo') != '' ) {
		#needswork
		echo "<!-- login screen here... overwrite logo with custom logo -->\n"; 
	} else { ?>
<style type="text/css">
#login { margin-top: 6em; }
h1 a { background: url(<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png) no-repeat top center; height: 80px; }
</style>
<?php }
}
endif;
if ( ! function_exists( 'progo_custom_login_url' ) ):
/**
 * hooked to 'login_headerurl' by add_action in progo_setup()
 * @uses get_option() To check if a custom logo has been uploaded to the back end
 * @return the custom URL
 * @since Direct 1.0
 */
function progo_custom_login_url() {
	if ( get_option( 'progo_logo' ) != '' ) {
		return get_bloginfo( 'url' );
	} // else
	return 'http://www.progo.com';
}
endif;
if ( ! function_exists( 'progo_site_settings_page' ) ):
/**
 * outputs HTML for ProGo Themes "Site Settings" page
 * @uses settings_fields() for hidden form items for 'progo_options'
 * @uses do_settings_sections() for 'progo_site_settings'
 * @since Direct 1.0
 */
function progo_site_settings_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>Site Settings</h2>
		<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_options' );
		do_settings_sections( 'progo_site_settings' );
		?><p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
		</form>
	</div>
<?php
}
endif;
if ( ! function_exists( 'progo_admin_page_styles' ) ):
/**
 * hooked to 'admin_print_styles' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen styling
 * @since Direct 1.0
 */
function progo_admin_page_styles() {
	global $pagenow;
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		$thispage = $_GET['page'];
		if ( $thispage == 'progo_welcome' ) {
			wp_enqueue_style( 'dashboard' );
			wp_enqueue_style( 'global' );
			wp_enqueue_style( 'wp-admin' );
			wp_enqueue_style( 'thickbox' );
		}
	}
	wp_enqueue_style( 'progo_admin', get_bloginfo( 'template_url' ) .'/css/admin-style.css' );
}
endif;
if ( ! function_exists( 'progo_admin_page_scripts' ) ):
/**
 * hooked to 'admin_print_scripts' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen Recommended Plugin info
 * @since Direct 1.0
 */
function progo_admin_page_scripts() {
	global $pagenow;
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'progo_welcome' ) ) ) {
		wp_enqueue_script( 'thickbox' );
	}
}
endif;
if ( ! function_exists( 'progo_admin_init' ) ):
/**
 * hooked to 'admin_init' by add_action in progo_setup()
 * adds functionality for progo_admin_action to progo_reset_wpsc or new_direct_page
 * removes meta boxes on EDIT PAGEs, and adds progo_direct_box for Direct Response pages
 * creates CRM table if it does not exist yet
 * sets admin action hooks
 * registers Site Settings
 * @since Direct 1.0
 */
function progo_admin_init() {
	if ( isset( $_REQUEST['progo_admin_action'] ) ) {
		switch( $_REQUEST['progo_admin_action'] ) {
			case 'reset_wpsc':
				progo_reset_wpsc();
				break;
			case 'reset_logo':
				progo_reset_logo();
				break;
			case 'newdirect':
				progo_new_direct_page( false );
				break;
			case 'colorBlackGrey':
				progo_colorscheme_switch( 'BlackGrey' );
				break;
			case 'colorLightBlue':
				progo_colorscheme_switch( 'LightBlue' );
				break;
			case 'colorLightGrey':
				progo_colorscheme_switch( 'LightGrey' );
				break;
			case 'colorOrangeBlack':
				progo_colorscheme_switch( 'OrangeBlack' );
				break;
			case 'colorStrongBlue':
				progo_colorscheme_switch( 'StrongBlue' );
				break;
		}
	}
	
	//Removes meta boxes from pages
	remove_meta_box( 'postcustom', 'page', 'normal' );
	remove_meta_box( 'trackbacksdiv', 'page', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
	remove_meta_box( 'commentsdiv', 'page', 'normal' );
	remove_meta_box(  'authordiv', 'page', 'normal' );
	
	$post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'];
	$template_file = get_post_meta( $post_id, '_wp_page_template', true);
 
	// check for a template type
	if ( $template_file == 'directresponse.php' ) {
		add_meta_box( "progo_direct_box", "Direct Response", "progo_direct_box", "page", "normal", "high" );
	}
	
	// hack to check the db creation for CRM ?
	global $wpdb;
	global $progo_direct_db_version;

	$table_name = $wpdb->prefix ."progo_crm";
	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
			time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			firstname tinytext NOT NULL,
			lastname tinytext NOT NULL,
			address tinytext NOT NULL,
			city tinytext NOT NULL,
			state tinytext NOT NULL,
			zip mediumint(5) UNSIGNED NOT NULL,
			phone tinytext NOT NULL,
			email tinytext NOT NULL,
			purchased bool NOT NULL,
			UNIQUE KEY id (id)
		);";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		add_option( "progo_direct_db_version", $progo_direct_db_version );
	}
	// ACTION hooks
	add_action( 'admin_print_styles', 'progo_admin_page_styles' );
	add_action( 'admin_print_scripts', 'progo_admin_page_scripts' );
	
	// Site Settings page
	register_setting( 'progo_options', 'progo_options', 'progo_options_validate' );
	
	add_settings_section( 'progo_api', 'ProGo Themes API Key', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_api_key', 'API Key', 'progo_field_apikey', 'progo_site_settings', 'progo_api' );
	
	add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_colorscheme', 'Color Scheme', 'progo_field_color', 'progo_site_settings', 'progo_theme' );
	add_settings_field( 'progo_logo', 'Logo', 'progo_field_logo', 'progo_site_settings', 'progo_theme' );

	add_settings_section( 'progo_info', 'Site Info', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_blogname', 'Site Name', 'progo_field_blogname', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_blogdescription', 'Slogan', 'progo_field_blogdesc', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_showdesc', 'Show/Hide Slogan', 'progo_field_showdesc', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_support', 'Customer Support', 'progo_field_support', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_copyright', 'Copyright Notice', 'progo_field_copyright', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_secure', 'Security Logos', 'progo_field_cred', 'progo_site_settings', 'progo_info' );
	add_settings_field( 'progo_companyinfo', 'Company Info', 'progo_field_compinf', 'progo_site_settings', 'progo_info' );

	add_settings_section( 'progo_checkout', 'Checkout Page', 'progo_section_text', 'progo_site_settings' );
	add_settings_field( 'progo_checkout', 'Checkout Headline', 'progo_field_checkout', 'progo_site_settings', 'progo_checkout' );
	add_settings_field( 'progo_button', 'Checkout Button', 'progo_field_button', 'progo_site_settings', 'progo_checkout' );
	
	// since there does not seem to be an actual THEME_ACTIVATION hook, we'll fake it here
	if ( get_option( 'progo_direct_installed' ) != true ) {
		progo_new_direct_page( true );
		// also want to create a few other pages (Terms & Conditions, Privacy Policy), set up the FOOTER menu, and add these pages to it...
		
		$post_date = date( "Y-m-d H:i:s" );
		$post_date_gmt = gmdate( "Y-m-d H:i:s" );
		
		// create the FOOTER menu in the Menu system
		$footer_menu_id = wp_create_nav_menu('Footer');
		//set_theme_mod
		
		if ( $footer_menu_id > 0 ) {
			// register the new "Footer" menu as THE menu in Direct Response theme's FOOTER area
			set_theme_mod( 'nav_menu_locations' , array( 'footer' => $footer_menu_id ) );
		}
			
		// create footer TERMS and PRIVACY pages, and add to our FOOTER menu
		$footer_pages = array(
			'terms' => array(
				'title' => __( 'Terms & Conditions', 'progo' ),
				'content' => "List your Terms and Conditions here",
				'id' => ''
			),
			'privacy' => array(
				'title' => __( 'Privacy Policy', 'progo' ),
				'content' => "Put your Privacy Policy here",
				'id' => ''
			)
		);
		foreach ( $footer_pages as $slug => $page ) {
			$footer_pages[$slug]['id'] = wp_insert_post( array(
				'post_title' 	=>	$page['title'],
				'post_type' 	=>	'page',
				'post_name'		=>	$slug,
				'comment_status'=>	'closed',
				'ping_status' 	=>	'closed',
				'post_content' 	=>	$page['content'],
				'post_status' 	=>	'publish',
				'post_author' 	=>	1,
				'menu_order'	=>	1
			));
			
			$menu_args = array(
				'menu-item-object-id' => $footer_pages[$slug]['id'],
				'menu-item-object' => 'page',
				'menu-item-parent-id' => 0,
				'menu-item-type' => 'post_type',
				'menu-item-title' => $page['title'],
				'menu-item-status' => 'publish',
			);
			if ( $footer_menu_id > 0 ) {
				wp_update_nav_menu_item( $footer_menu_id , 0, $menu_args );
			}
		}
		// set our default SITE options
		progo_options_defaults();
		
		// and send to WELCOME page
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/admin.php?page=progo_site_settings' );
	}
}
endif;

if ( ! function_exists( 'progo_direct_widgets' ) ):
/**
 * registers a sidebar area for the WIDGETS page
 * and registers various Widgets
 * @since Direct 1.0.57
 */
function progo_direct_widgets() {
	register_sidebar(array(
		'name' => 'Direct Response',
		'id' => 'widgets',
		'description' => 'For the area in the bottom right column of Direct Response pages. If no widgets appear below, the "Easy &amp; Secure", "Share", and "Testimonials" will show.',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	
	
	require_once( 'widgets/widget-easysecure.php' );
	register_widget( 'ProGo_Widget_EasySecure' );
	
	require_once( 'widgets/widget-share.php' );
	register_widget( 'ProGo_Widget_Share' );
	
	require_once( 'widgets/widget-testimonials.php' );
	register_widget( 'ProGo_Widget_Testimonials' );
}
endif;
if ( ! function_exists( 'progo_dashboard_widget_function' ) ):
/**
 * prints WELCOME steps
 * @since Direct 1.0
 */
function progo_dashboard_widget_function() {
	$options = get_option( 'progo_options' );
	// Display whatever it is you want to show
	$greyout = ' style="color: #999;"';
	echo '<ol>';
	$lnk = '<a href="admin.php?page=progo_site_settings" title="Site Settings">';
	echo '<li>Update your custom '. $lnk .'Site Settings</a> under <strong>ProGo Themes</strong> in the left menu. Customize...';
	echo "<ul><li>Your Site's Name : ". $lnk . get_option( 'blogname' ) ."</a></li><li>Color Scheme : ". $lnk . esc_html( $options['colorscheme'] ) ."</a></li></ul></li>";
	echo "<li>Your <em>Direct Response</em> ProGo Theme works hand-in-hand with the <strong>WP e-Commerce</strong> Plugin. ";
	// check for wp-e-commerce installed..
	$plugs = get_plugins();
	$goon = isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] );
	if( $goon ) {
		echo "<br />Congratulations! WP e-Commerce appears to be installed.";
	} else {
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
		echo '<br /><strong><a href="'. esc_url( $lnk ) .'" onclick="alert(\'since the STABLE release is v3.7.8, and we are working with v3.8, we need to wait on this one-click...\'); return false;">Click Here to install the WP e-Commerce Plugin.</a></strong>';
		$goon = false;
	}
	echo '</li>';
	//is WP e-Commerce Plugin activated?
	echo '<li'. ($goon ? '' : $greyout) .'>';
	if ( !$goon) {
		echo 'Is WP e-Commerce Plugin activated?';
	} else {
		if ( function_exists('wpsc_admin_pages')) {
			echo "WP e-Commerce Plugin is activated!<ul>";
			
			//check wpsc settings dimensions for thumbnail (product_image) & product image (single_view_image)
			if ( get_option( 'product_image_width' ) == 70 && get_option( 'product_image_height' ) == 70 && get_option( 'single_view_image_width' ) == 300 && get_option( 'single_view_image_height' ) == 300 ) {
				echo "<li>WP e-Commerce settings match ProGo Themes' Recommended Settings!</li>";
			} else {
				echo "<li>A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from ProGo Themes' Recommended Settings : ";
				echo '<a href="'.wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc').'">Click Here to Reset</a></li>';
			}
			
        	echo '<li>Edit your WP e-Commerce store\'s <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings', 'tab-general' ) .'">General Settings</a></li>';
        	echo '<li>Payment Method is set to ';
			$cur_gateway = get_option( 'custom_gateway_options' );
			global $nzshpcrt_gateways;
			foreach( $nzshpcrt_gateways as $gateway ) {
				if ( $gateway['class_name'] == $cur_gateway[0] ) {
					echo esc_html( $gateway['name'] );
				}
			}
			echo ' : <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings&tab=gateway', 'tab-gateway' ) .'">Edit Payment Settings</a></li>';
			echo '<li>Shipping is currently ';
			echo ( absint( get_option( 'do_not_use_shipping' ) ) == 1 ? 'Dis' : 'En');
			echo 'abled : <a href="'. wp_nonce_url( 'options-general.php?page=wpsc-settings&tab=shipping', 'tab-shipping' ) .'">Edit Shipping Settings</a></li>';
			echo '</ul>';
        } else {
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			echo '<a href="'. esc_url($lnk) .'">Click Here to activate the WP e-Commerce Plugin</a>';
			$goon = false;
		}
	}
	echo '</li>';
	// check # of Products (at least 1?)
	if ( $goon ) {
		echo '<li>';
		$num = wp_count_posts( 'wpsc-product' );
		$num = $num->publish;
		if ( $num > 0 ) {
			$lnk = '<a href="edit.php?post_type=wpsc-product">';
			echo 'You have '. $lnk . absint( $num ) .' Product'. ( $num > 1 ? 's' : '' ) .'</a> set up in WP e-Commerce. ';
			echo '<ul><li>'. $lnk .'Edit Product'. ( $num > 1 ? 's' : '' ) .'</a></li>';
			echo '<li><a href="post-new.php?post_type=wpsc-product">Add Another Product</a></li></ul>';
		} else {
			$goon = false;
			echo '<a href="post-new.php?post_type=wpsc-product">Create your first Product.</a>';
		}
	} else {
		echo '<li'. $greyout .'>Have you created at least 1 Product?';
	}
	echo '</li>';
	// check for any Direct Response pages?
	if ( $goon ) {
		// first, quick fix for ProGo Direct Response v1.0.29
		$num = get_posts( 'post_type=page&meta_key=_wp_page_template&meta_value=directmarket.php' );
		foreach ( $num as $p ) {
			// renaming "directmarket.php" template to "directresponse.php" template
			update_post_meta( $p->ID, '_wp_page_template', 'directresponse.php' );
		}
		
		$num = get_posts( 'post_type=page&meta_key=_wp_page_template&meta_value=directresponse.php' );
		$num = count( $num );
		if ( $num > 0 ) {
			echo '<li>You have '. absint( $num ) .' <a href="edit.php?s&post_type=page&progo_template=directresponse.php">Direct Response</a> Page'. ( $num > 1 ? 's' : '' ) .'.';
			echo '<ul>';
			echo '<li><a href="edit.php?s&post_type=page&progo_template=directresponse.php">View your current Direct Response pages</a></li>';
			echo '<li><a href="'.wp_nonce_url("admin.php?progo_admin_action=newdirect", 'progo_newdirect').'">Create another Direct Response page</a></li>';
			echo '</ul>';
		} else {
			echo '<li>You are now ready to create your first Direct Response page. To do so, just <a href="post-new.php?post_type=page">Create a New Page</a>, and in the <strong>Templates</strong> area in the <strong>Page Attributes</strong> box in the right column, choose <strong>Direct Response Page</strong>. More options will appear after the first time you Save the new Page.';
		}
	} else {
		echo '<li'. $greyout .'>Direct Response Page(s)?';
	}
	echo '</li>';
	
	echo '</ol>';
}
endif;
if ( ! function_exists( 'progo_metabox_cleanup' ) ):
/**
 * fires after wpsc_meta_boxes hook, so we can overwrite a lil bit
 * @since Direct 1.0.32
 */
function progo_metabox_cleanup() {
	global $wp_meta_boxes;
	global $post_type;
	global $post;
	
	switch($post_type) {
		case 'wpsc-product':
			if ( isset( $wp_meta_boxes['wpsc-product'] ) ) {
				// unhook wpsc's Product Images metabox and add our own instead
				remove_meta_box( 'wpsc_product_image_forms', 'wpsc-product', 'normal' );
				add_meta_box( 'progo_product_image_forms', 'Product Images', 'progo_product_image_forms', 'wpsc-product', 'normal', 'high' );
				// sort the wpsc-product main column meta boxes so Product Images is #1
				$wp_meta_boxes['wpsc-product']['normal']['high'] = progo_arraytotop( $wp_meta_boxes['wpsc-product']['normal']['high'], 'progo_product_image_forms' );
				
				// also move PRICE to just under SUBMITdiv on right
				// Backup and delete element from parent array
				$toparr = array(
					'submitdiv' => $wp_meta_boxes['wpsc-product']['side']['core']['submitdiv'],
					'wpsc_price_control_forms' => $wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']
				);
				unset($wp_meta_boxes['wpsc-product']['side']['core']['submitdiv']);
				unset($wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']);
				// Merge the two arrays together so our widget is at the beginning
				$wp_meta_boxes['wpsc-product']['side']['core'] = array_merge( $toparr, $wp_meta_boxes['wpsc-product']['side']['core'] );
			}
			break;
		case 'page':
			if ( get_post_meta( $post->ID, '_wp_page_template', true ) == 'directresponse.php' ) {
				#needswork
				$wp_meta_boxes['page']['side']['low']['postimagediv']['title'] = 'Custom Image for Top of Page';
			}
//			wp_die('<pre>'.print_r($wp_meta_boxes,true).'</pre>');
			break;
	}
}
endif;
add_action( 'do_meta_boxes', 'progo_metabox_cleanup' );

if ( ! function_exists( 'progo_direct_box' ) ):
/**
 * outputs html for "Direct Response" meta box on EDIT (Direct Response) PAGE
 * called by add_meta_box( "progo_direct_box", "Direct Response", "progo_direct_box"...
 * in progo_admin_init()
 * @uses progo_direct_meta_defaults()
 * @since Direct 1.0
 */
function progo_direct_box() {
	global $post;
	$custom = get_post_meta($post->ID,'_progo');
	$direct = $custom[0];
	if ( $direct == '' ) {
		// set up default values
		$direct = progo_direct_meta_defaults();
	}
	$colorschemes = progo_colorschemes();
	$options = get_option('progo_options');
	$colorscheme = $options['colorscheme'];
	// include countChars js if All In One SEO Pack is not installed
	if ( !function_exists( 'aiosp_meta' ) ) { ?>
	<script type="text/javascript">
    <!-- Begin
    function countChars( fd, cf ) {
    cf.value = fd.value.length;
    }
    //  End -->
    </script><?php } ?>
	<script type="text/javascript">
    <!-- Begin
    function progo_cc( thefield, counter, pfield ) {
    	counter.value = thefield.value.length;
		<?php if ( count( $colorschemes ) > 0 ) { ?>
		if ( pfield !== false ) {
			jQuery('#'+pfield).html(thefield.value);
		}
		<?php } ?>
    }
	<?php if ( count( $colorschemes ) > 0 ) { ?>
	jQuery(function() {
		jQuery('#title').keyup(function() {
			jQuery('#ptitle').html(jQuery(this).val());
		});
	});
	<?php } ?>
    //  End -->
    </script>
    <table width="100%">
    <tr valign="top"><td>
	<p><strong>1. Choose a Product from the Dropdown</strong></p>
	<select name="_progo[plink]"><?php
$prods = get_posts( 'numberposts=-1&post_type=wpsc-product' );
foreach ( $prods as $p ) {
		echo '<option value="'. absint($p->ID) .'"'. ( $p->ID == absint($direct[plink]) ? ' selected="selected"' : '' ) .'>'. esc_html( $p->post_title ) .'</option>';
	} ?></select>
    <p><strong>2. Price Box Headline</strong></p>
    <input type="text" name="_progo[getyours]" value="<?php esc_html_e( $direct[getyours] ); ?>" size="40" maxlength="<?php esc_attr_e( progo_direct_charcutoff( 'getyours' ) ); ?>" onkeydown="progo_cc( this, document.post.c_getyours, false )" onKeyUp="progo_cc( this, document.post.c_getyours, 'pget' )" />
    <table>
    <tr><td><p><input type="text" name="c_getyours" size="3" maxlength="3" style="text-align:center;" value="<?php echo strlen( $direct[getyours] );?>" readonly="readonly" /> / <?php esc_html_e( progo_direct_charcutoff( 'getyours' ) ); ?> characters max</p></td></tr>
    </table>
    <p><strong>3. Arrow Headline</strong></p>
    <input type="text" name="_progo[arrowd]" value="<?php esc_html_e( $direct[arrowd] ); ?>" size="40" maxlength="<?php esc_html_e( $direct[arrowd] ); ?>" onkeydown="progo_cc( this, document.post.c_arrowd, false )" onKeyUp="progo_cc( this, document.post.c_arrowd, 'parr' )" />
    <table>
    <tr><td><p><input type="text" name="c_arrowd" size="3" maxlength="3" style="text-align:center;" value="<?php echo strlen( $direct[arrowd] );?>" readonly="readonly" /> / <?php esc_html_e( progo_direct_charcutoff( 'arrowd' ) ); ?> characters max</p></td></tr>
    </table>
	<p><strong>4. Top Right Arrow Text</strong></p>
	<input type="text" name="_progo[toparr]" size="30" maxlength="<?php esc_html_e( $direct[toparr] ); ?>" onkeydown="progo_cc( this, document.post.c_ta, false )" onKeyUp="progo_cc( this, document.post.c_ta, 'pta' )" value="<?php esc_html_e( $direct[toparr] ); ?>" />
    <table>
    <tr><td><p><input type="text" name="c_ta" size="2" maxlength="3" style="text-align:center;" value="<?php echo strlen( $direct[toparr] );?>" readonly="readonly" /> / <?php esc_html_e( progo_direct_charcutoff( 'toparr' ) ); ?> characters max</p></td></tr></table>
	<p><strong>5. Statement Leading Into Form</strong></p>
	<textarea name="_progo[rightheadline]" cols="25" rows="2" onkeydown="progo_cc( this, document.post.c_rh, false )" onKeyUp="progo_cc( this, document.post.c_rh, 'prh' )"><?php esc_html_e( $direct[rightheadline] ); ?></textarea>
    <p><input type="text" name="c_rh" size="2" maxlength="3" style="text-align:center;" value="<?php echo strlen( $direct[rightheadline] );?>" readonly="readonly" /> / 60 characters max (Please limit to 2 lines)</p>
	<p><strong>6. "Buy Now" Button Text</strong></p>
	<p><input type="text" name="_progo[buynow]" size="18" maxlength="<?php esc_html_e( $direct[buynow] ); ?>" onkeydown="progo_cc( this, document.post.c_bn, false )" onKeyUp="progo_cc( this, document.post.c_bn, 'pbn' )" value="<?php esc_html_e( $direct[buynow] ); ?>" /> <input type="text" name="c_bn" size="2" maxlength="3" style="text-align:center;" value="<?php echo strlen( $direct[buynow] );?>" readonly="readonly" /> / <?php esc_html_e( progo_direct_charcutoff( 'buynow' ) ); ?> characters max</p>
    </td><td width="375">
<?php if ( count( $colorschemes ) > 0 ) { ?>    
<p>Page Preview:</p><div id="progo_screen" class="<?php echo esc_attr($colorscheme); ?>">
    <!-- thanks andyK for this idea! -->
    <div id="ptitle"><?php the_title(); ?></div>
    <div id="pget"><?php if ( strlen($direct[getyours]) ) { esc_html_e($direct[getyours]); } ?></div>
    <?php if ( $direct[plink] > 0 ) {
		echo get_the_post_thumbnail( absint ( $direct[plink] ), array(119,117), array('id'=>'pimg'));
		echo '<div id="pprice">'. progo_price( absint ( $direct[plink] ) ) .'</div>';
	}
	?>
    <div id="parr"><?php if ( strlen($direct[arrowd]) ) { esc_html_e($direct[arrowd]); } ?></div>
    <div id="pta"><?php if ( strlen($direct[toparr]) ) { esc_html_e($direct[toparr]); } ?></div>
    <div id="prh"><?php if ( strlen($direct[rightheadline]) ) { esc_html_e($direct[rightheadline]); } ?></div>
    <div id="pbn"><?php if ( strlen($direct[buynow]) ) { esc_html_e($direct[buynow]); } ?></div>
</div><p><br /></p>
<?php } ?>
<p><strong>7. Testimonial</strong></p>
<textarea name="_progo[testitxt]" cols="46" rows="5"><?php esc_html_e( $direct[testitxt] ); ?></textarea>
<input type="hidden" name="_progo[hidetest]" value="<?php echo ( $direct[hidetest] == 1 ? 1 : 0 ); ?>" />
    <p>Testimonial Block will not appear if box above is blank.<br /><em>Allowable tags: em, strong</em></p>
	<p><strong>Testimonial Author / Location</strong></p>
	<textarea name="_progo[testiauth]" cols="46" rows="2"><?php esc_html_e( $direct[testiauth] ); ?></textarea>
	
</td></tr>
    </table>
	<?php
}
endif;

/********* core ProGo Themes' Direct Response functions *********/

if ( ! function_exists( 'progo_colorschemes' ) ):
/**
 * @return array of Color Schemes
 * @since Direct 1.0
 */
function progo_colorschemes() {
	//return array( 'Dark', 'Light', 'Blue', 'Green' );
	return array( 'LightGrey', 'LightBlue', 'StrongBlue', 'OrangeBlack', 'BlackGrey' );
}
endif;

if ( ! function_exists( 'progo_add_scripts' ) ):
/**
 * hooked to 'wp_print_scripts' by add_action in progo_setup()
 * adds front-end js
 * @since Direct 1.0
 */
function progo_add_scripts() {
	if ( !is_admin() ) {
		wp_register_script( 'progo', get_bloginfo('template_url') .'/js/progo-frontend.js', array('jquery'), '1.0', true );
		wp_enqueue_script( 'progo' );
		do_action('progo_frontend_scripts');
	}
}
endif;
if ( ! function_exists( 'progo_add_styles' ) ):
/**
 * hooked to 'wp_print_styles' by add_action in progo_setup()
 * checks for Color Scheme setting and adds appropriate front-end stylesheet
 * @since Direct 1.0
 */
function progo_add_styles() {
	if ( !is_admin() ) {
		$options = get_option('progo_options');
		$color = $options['colorscheme'];
		$avail = progo_colorschemes();
		if ( in_array( $color, $avail ) ) {
			$scheme = 'proGoColorscheme'. $color;
			wp_register_style( $scheme, get_bloginfo('template_url') .'/css/style'. $color .'.css' );
			wp_enqueue_style( $scheme );
		}
	}
	do_action('progo_frontend_styles');
}
endif;
if ( ! function_exists( 'progo_reset_wpsc' ) ):
/**
 * sets WPSC image/thumbnail sizes to ProGo recommended settings
 * also updates wpsc_email_receipt
 * @since Direct 1.0
 */
function progo_reset_wpsc(){
	check_admin_referer( 'progo_reset_wpsc' );
	//set thumbnail & main image size to desired dimensions
	update_option( 'product_image_width', 70 );
	update_option( 'product_image_height', 70 );
	update_option( 'single_view_image_width', 300 );
	update_option( 'single_view_image_height', 300 );
	
	update_option( 'wpsc_email_receipt', "Any items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the links on this page. All prices include tax and postage and packaging where applicable.\n\n%product_list%%total_price%%find_us%" );
	
	wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_welcome' );
	exit();
}
endif;
if ( ! function_exists( 'progo_reset_logo' ) ):
/**
 * wipe out any custom logo image setting
 * @since Direct 1.0.53
 */
function progo_reset_logo(){
	check_admin_referer( 'progo_reset_logo' );
	
	// reset logo settings
	$options = get_option('progo_options');
	$options['logo'] = '';
	update_option( 'progo_options', $options );
	update_option( 'progo_settings_just_saved', 1 );
	
	wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_site_settings' );
	exit();
}
endif;
if ( ! function_exists( 'progo_header_check' ) ):
/**
 * function mainly to redirect to checkout from directresponse.php pages
 * @since Direct 1.0.55
 */
function progo_header_check(){
	if($_REQUEST['progo_action'] == 'step2') {
		$formdata = $_REQUEST['collected_data'];
		/*
		// store form in progo_crm db table
		global $wpdb;
		$rows_affected = $wpdb->insert($wpdb->prefix . "progo_crm", array( 'id' => '', 'time' => current_time('mysql'), 'firstname' => $formdata[2], 'lastname' => $_REQUEST['lastname'], 'address' => $_REQUEST['address'], 'city' => $_REQUEST['city'], 'state' => $_REQUEST['state'], 'zip' => $_REQUEST['zip'], 'phone' => $_REQUEST['phone'], 'email' => $_REQUEST['email'], 'purchased' => 0 ));
		*/
		// and set wpsc...
		$_SESSION['wpsc_checkout_saved_values'] = $formdata;
		/*
		$_SESSION['wpsc_selected_country'] = 'US';
		$_SESSION['wpsc_selected_region'] = $_REQUEST['state'];
		$_SESSION['wpsc_delivery_country'] = 'US';
		$_SESSION['wpsc_delivery_region'] = $_REQUEST['state'];
		*/
    	global $wpdb;
   		$checkoutid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'checkout' AND post_status = 'publish' AND post_type = 'page'");
		$checkouturl = get_permalink($checkoutid);
		header('Location: '. $checkouturl);
		die;
	}
}
endif;

if ( ! function_exists( 'progo_arraytotop' ) ):
/**
 * helper function to bring a given element to the start of an array
 * @param parent array
 * @param element to bring to the top
 * @return sorted array
 * @since Direct 1.0.33
 */
function progo_arraytotop($arr, $totop) {
	// Backup and delete element from parent array
	$toparr = array($totop => $arr[$totop]);
	unset($arr[$totop]);
	// Merge the two arrays together so our widget is at the beginning
	return array_merge( $toparr, $arr );
}
endif;

/**
 * creates a new Direct Response PAGE with default helpful copy & meta values
 * @param isfirst if this is the first Direct page, set the Homepage and other settings
 * @uses progo_direct_meta_defaults()
 * @since Direct 1.0
 */
function progo_new_direct_page ( $isfirst ) {
	// should we be checking NONCE here too?
	
	$post_date = date( "Y-m-d H:i:s" );
	$post_date_gmt = gmdate( "Y-m-d H:i:s" );

	$new_page = array(
		'slug' => 'direct',
		'title' => __( 'Write a Headline that Captivates', 'progo' ),
		'content' => "<h2>Write a Sub-Headline that Validates Your Offer</h2>
This is the opening paragraph. It should contain about 3-5 lines and is very important since it needs to catch the attention of the reader. Typically questions of who, what, when, where and why about your offer are answered here. Keep it short and highlight what your product is all about.
<ul>
<li>Write a primary feature about your product</li>
<li>Write a secondary feature about your product</li>
<li>Write a tertiary feature about your product</li>
</ul>
<h3>Write a Secondary Subhead Example right here</h3>
The following paragraphs go into depth about your product or offer. Give more details of the key features that deliver on your product's benefits.  Keep in mind that you are writing to your customer's needs and wants; and not your own. Break out your information- informative and written with facts, statistics and information that is credible. Be authentic and write article with clarity.

Tip: Include photography, videos, and other types of multi-media to reinforce and build credibility of your product.
\nOne more thing: it can be helpful to reiterate your Product Offer and Price again at the bottom of the page."
	); 	
	$new_page_id = wp_insert_post( array(
		'post_title' 	=>	$new_page['title'],
		'post_type' 	=>	'page',
		'post_name'		=>	$new_page['slug'],
		'comment_status'=>	'closed',
		'ping_status' 	=>	'closed',
		'post_content' 	=>	$new_page['content'],
		'post_status' 	=>	'publish',
		'post_author' 	=>	1,
		'menu_order'	=>	0
	));
	update_post_meta( $new_page_id, '_wp_page_template', 'directresponse.php' );
	$default_direct = progo_direct_meta_defaults();
	update_post_meta( $new_page_id, '_progo', $default_direct );
	
	if ( $isfirst ) {
		// now also want to update PERMALINK structure to something nice
		update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
		
		// and set HOMEPAGE = $default_page_id
		update_option ( 'show_on_front', 'page' );
		update_option ( 'page_on_front', $new_page_id );
	} else {
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/post.php?post='. $new_page_id .'&action=edit' );
	}
}
if ( ! function_exists( 'progo_default_product_id' ) ):
/**
 * @return integer product ID of first(?) wpsc-product
 * @since Direct 1.0
 */
function progo_default_product_id() {
	$pID = 0;
	// if we have any Products, set pID = first product ID instead of 0
	$products = get_posts( 'post_type=wpsc-product' );
	if( count( $products ) > 0 ) {
		$pID = $products[0]->ID;
	}
	return $pID;
}
endif;
if ( ! function_exists( 'progo_direct_meta_defaults' ) ):
/**
 * sets up default values for Direct Response meta box fields
 * @return array of default values
 * @since Direct 1.0
 */
function progo_direct_meta_defaults() {
	$pID = progo_default_product_id();
	
	$direct = array(
		'plink' => $pID,
		'getyours' => 'Get Yours Today',
		'arrowd' => 'ARROW HEADLINE GOES HERE',
		'toparr' => 'LIMITED SUPPLY!',
		'rightheadline' => 'Friendly Urgent Statement Leading Into Form',
		'buynow' => 'BUY NOW',
		'testitxt' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation',
		'hidetest' => 0,
		'testiauth' => "John Q Smith\nArcata, CA"
	);
	return $direct;
}
endif;
if ( ! function_exists( 'progo_direct_charcutoff' ) ):
/**
 * helper function to return the max char count for a given field
 * wrapped in function_exists check so children themes can override
 * @param name of field to grab max length
 * @return (int) max char count
 * @since Direct 1.0.43
 */
function progo_direct_charcutoff($field) {
	$cut = 0;
	switch($field) {
		case 'getyours':
			$cut = 21;
			break;
		case 'arrowd':
			$cut = 28;
			break;
		case 'toparr':
			$cut = 30;
			break;
		case 'buynow':
			$cut = 16;
			break;
		case 'rightheadline':
			$cut = 60;
			break;
	}
	return $cut;
}
endif;
if ( ! function_exists( 'progo_save_meta' ) ):
/**
 * hooked to 'save_post' by add_action in progo_setup()
 * checks for _progo (direct) meta data, and performs validation & sanitization
 * @param post_id to check meta on
 * @return post_id
 * @since Direct 1.0
 */
function progo_save_meta( $post_id ){
	// verify if this is an auto save routine. If it is,
	// our form has not been submitted, so we don't want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { 
		return $post_id;
	}
	// check permissions
	if ( $_POST['post_type'] == 'page' ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
	//if ( !current_user_can( 'edit_post', $post_id ) )
	  return $post_id;
	}
	
	// OK, we're authenticated: we need to find and save the data
	if ( isset( $_POST['_progo'] ) ) {
		$direct = $_POST['_progo'];
		
		if ( isset ( $direct[plink] ) ) {
			$direct[plink] = absint( $direct[plink] );
		} else {
			$direct[plink] = progo_default_product_id();
		}
		
		// sanitize the following fields & check max length
		$checklengths = array( 'getyours', 'arrowd', 'toparr', 'rightheadline', 'buynow' );
		foreach ( $checklengths as $key ) {
			if ( isset ( $direct[$key] ) ) {
				$direct[$key] = substr( wp_kses( $direct[$key], array() ), 0, progo_direct_charcutoff($key) );
			} else {
				$direct[$key] = '';
			}
		}
		
		if ( isset ( $direct[testitxt] ) ) {
			if ( strlen( $direct[testitxt] ) > 0 ) {
				$direct[testitxt] = wp_kses( $direct[testitxt], array( 'em' => array(), 'strong' => array() ) );
				$direct[hidetest] = 0;
			} else {
				$direct[hidetest] = 1;
			}
			$direct[testiauth] = wp_kses( $direct[testiauth], array( 'em' => array(), 'strong' => array() ) );
		} else {
			$direct[testitxt] = '';
			$direct[testiauth] = '';
			$direct[hidetest] = 1;
		}
		update_post_meta($post_id, "_progo", $direct);
		return $direct;
	}
	return $post_id;
}
endif;
if ( ! function_exists( 'progo_colorscheme_switch' ) ):
/**
 * helper function to switch the current Color Scheme
 * @since Direct 1.0.52
 */
function progo_colorscheme_switch( $color ) {
	$okgo = true;
	$avail = progo_colorschemes();
	if( current_user_can('manage_options') == false ) {
		$okgo = false;
	} elseif ( in_array($color, $avail) == false ) {
		$okgo = false;
	}
	
	if ( $okgo == true ) {
		$opt = get_option( 'progo_options' );
		$opt[colorscheme] = $color;
		update_option( 'progo_options', $opt );
		
		wp_redirect( get_option('siteurl') );
	} else {
		wp_die('Nice try...');
		return;
	}
}
endif;
/**
 * ProGo Site Settings Options defaults
 * @since Direct 1.0
 */
function progo_options_defaults() {
	// Define default option settings
	$tmp = get_option( 'progo_options' );
    if ( !is_array( $tmp ) ) {
		$def = array(
			"colorscheme" => "LightGrey",
			"logo" => "",
			"favicon" => "",
			"blogname" => get_option( 'blogname' ),
			"blogdescription" => get_option( 'blogdescription' ),
			"showdesc" => 1,
			"support" => "123-555-7890",
			"copyright" => "© Copyright 2011, All Rights Reserved",
			"credentials" => "",
			"companyinfo" => "We sincerely thank you for your patronage.\nThe Our Company Staff\n\nOur Company, Inc.\n1234 Address St\nSuite 43\nSan Diego, CA 92107\n619-555-5555",
			"checkout" => "Your Order is Almost Complete",
			"button" => "BUY NOW"
		);
		update_option( 'progo_options', $def );
	}
	
	update_option( 'progo_direct_installed', true );
	update_option( 'progo_direct_apikey', '' );
	update_option( 'progo_direct_apiauth', 'new' );
	
	update_option( 'wpsc_ignore_theme', true );
	
	// set large image size
	update_option( 'large_size_w', 650 );
	update_option( 'large_size_h', 413 );
}

if ( ! function_exists( 'progo_options_validate' ) ):
/**
 * ProGo Site Settings Options validation function
 * from register_setting( 'progo_options', 'progo_options', 'progo_options_validate' );
 * in progo_admin_init()
 * also handles uploading of custom Site Logo
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since Direct 1.0
 */
function progo_options_validate( $input ) {
	// do validation here...
	$arr = array( 'blogname', 'blogdescription', 'colorscheme', 'support', 'copyright', 'button', 'apikey', 'companyinfo' );
	foreach ( $arr as $opt ) {
		$input[$opt] = wp_kses( $input[$opt], array() );
	}
	// we'll let CHECKOUT headline have some html in it...
	$input['checkout'] = wp_kses( $input['checkout'], array('strong'=>array(), 'em'=>array()) );
	
	// opt[colorscheme] must be one of the allowed colors
	$colors = progo_colorschemes();
	if ( !in_array( $input['colorscheme'], $colors ) ) {
		$input['colorscheme'] = 'LightGrey';
	}
	// opt[showdesc] can only be 1 or 0
	if ( (int) $input['showdesc'] != 1 ) {
		$input['showdesc'] = 0;
	}
	
	// save blogname & blogdescription to other options as well
	$arr = array( 'blogname', 'blogdescription' );
	foreach ( $arr as $opt ) {
		if ( $input[$opt] != get_option( $opt ) ) {
			update_option( $opt, $input[$opt] );
		}
	}
	
	// store API KEY in its own option
	if ( $input['apikey'] != get_option( 'progo_direct_apikey' ) ) {
		update_option( 'progo_direct_apikey', substr( $input['apikey'], 0, 39 ) );
	}
	unset( $input['apikey'] );
	
	// check SUPPORT field & set option['support_email'] flag if we have an email
	$input['support_email'] = is_email( $input['support'] );
	
		// upload error?
		$error = '';
	// upload the file - BASED OFF WP USERPHOTO PLUGIN
	if ( isset($_FILES['progo_options']) && @$_FILES['progo_options']['name']['logotemp'] ) {
		if ( $_FILES['progo_options']['error']['logotemp'] ) {
			switch ( $_FILES['progo_options']['error']['logotemp'] ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error = "The uploaded file exceeds the max upload size.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = "The uploaded file was only partially uploaded.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$error = "No file was uploaded.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error = "Missing a temporary folder.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error = "Failed to write file to disk.";
					break;
				case UPLOAD_ERR_EXTENSION:
					$error = "File upload stopped by extension.";
					break;
				default:
					$error = "File upload failed due to unknown error.";
			}
		} elseif ( !$_FILES['progo_options']['size']['logotemp'] ) {
			$error = "The file &ldquo;". $_FILES['progo_options']['name']['logotemp'] ."&rdquo; was not uploaded. Did you provide the correct filename?";
		} elseif ( !in_array( $_FILES['progo_options']['type']['logotemp'], array( "image/jpeg", "image/pjpeg", "image/gif", "image/png", "image/x-png" ) ) ) {
			$error = "The uploaded file type &ldquo;". $_FILES['progo_options']['type']['logotemp'] ."&rdquo; is not allowed.";
		}
		$tmppath = $_FILES['progo_options']['tmp_name']['logotemp'];
		
		$imageinfo = null;
		if(!$error){			
			$imageinfo = getimagesize($tmppath);
			if ( !$imageinfo || !$imageinfo[0] || !$imageinfo[1] ) {
				$error = __("Unable to get image dimensions.", 'user-photo');
			} else if( $imageinfo[0] > 598 || $imageinfo[1] > 75 ) {
				/*
				if(userphoto_resize_image($tmppath, null, $userphoto_maximum_dimension, $error)) {
					$imageinfo = getimagesize($tmppath);
				}
				*/
				$filename = $tmppath;
				$newFilename = $filename;
				$jpeg_compression = 86;
				#if(empty($userphoto_jpeg_compression))
				#	$userphoto_jpeg_compression = USERPHOTO_DEFAULT_JPEG_COMPRESSION;
				
				$info = @getimagesize($filename);
				if(!$info || !$info[0] || !$info[1]){
					$error = __("Unable to get image dimensions.", 'user-photo');
				}
				//From WordPress image.php line 22
				else if (
					!function_exists( 'imagegif' ) && $info[2] == IMAGETYPE_GIF
					||
					!function_exists( 'imagejpeg' ) && $info[2] == IMAGETYPE_JPEG
					||
					!function_exists( 'imagepng' ) && $info[2] == IMAGETYPE_PNG
				) {
					$error = __( 'Filetype not supported.', 'user-photo' );
				}
				else {
					// create the initial copy from the original file
					if ( $info[2] == IMAGETYPE_GIF ) {
						$image = imagecreatefromgif( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						$image = imagecreatefromjpeg( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						$image = imagecreatefrompng( $filename );
					}
					if(!isset($image)){
						$error = __("Unrecognized image format.", 'user-photo');
						return false;
					}
					if ( function_exists( 'imageantialias' ))
						imageantialias( $image, TRUE );
			
					// make sure logo is within max 598 x 75 dimensions
					
					// figure out the longest side
					if ( ( $info[0] / $info[1] ) > 8 ) { // resize width to fit 
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_width = 598;
			
						$image_ratio = $image_width / $image_new_width;
						$image_new_height = round( $image_height / $image_ratio );
					} else { // resize height to fit
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_height = 75;
			
						$image_ratio = $image_height / $image_new_height;
						$image_new_width = round( $image_width / $image_ratio );
					}
			
					$imageresized = imagecreatetruecolor( $image_new_width, $image_new_height);
					@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );
			
					// move the thumbnail to its final destination
					if ( $info[2] == IMAGETYPE_GIF ) {
						if (!imagegif( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						if (!imagejpeg( $imageresized, $newFilename, $jpeg_compression ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						@ imageantialias($imageresized,true);
						@ imagealphablending($imageresized, false);
						@ imagesavealpha($imageresized,true);
						$transparent = imagecolorallocatealpha($imageresized, 255, 255, 255, 0);
						for($x=0;$x<$image_new_width;$x++) {
							for($y=0;$y<$image_new_height;$y++) {
							@ imagesetpixel( $imageresized, $x, $y, $transparent );
							}
						}
						@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );

						if (!imagepng( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
				}
				if(empty($error)) {
					$imageinfo = getimagesize($tmppath);
				}
			}
		}
		
		if ( !$error ){
			$upload_dir = wp_upload_dir();
			$dir = trailingslashit( $upload_dir['basedir'] );
			$imagepath = $dir . $_FILES['progo_options']['name']['logotemp'];
			
			if ( !move_uploaded_file( $tmppath, $imagepath ) ) {
				$error = "Unable to place the user photo at: ". $imagepath;
			}
			else {
				chmod($imagepath, 0666);
				
				$input['logo'] = $_FILES['progo_options']['name']['logotemp'];
	
				/*
				if($oldFile && $oldFile != $newFile)
					@unlink($dir . '/' . $oldFile);
				*/
			}
		}
	}
	update_option('progo_settings_just_saved',1);
	
	return $input;
}
endif;

/********* more helper functions *********/

if ( ! function_exists( 'progo_field_color' ) ):
/**
 * outputs HTML for "Color Scheme" option on Site Settings page
 * @uses progo_colorschemes() for list of available Color Schemes
 * @since Direct 1.0
 */
function progo_field_color() {
	$options = get_option( 'progo_options' );
	$opts = progo_colorschemes();
	// in case a child theme overwrites the Available Colors progo_colorschemes() function
	if( count($opts) > 0 ) {
	?>
<select id="progo_colorscheme" name="progo_options[colorscheme]" style="float: left; margin-right: 20px; width: 128px;" onchange="updateScreenshot()">
<?php
	foreach ( $opts as $color ) {
		echo '<option value="'. $color .'"'. (($options['colorscheme']==$color) ? ' selected="selected"' : '') .'>'.esc_html($color).'</option>';
	}
?></select><script type="text/javascript">
function updateScreenshot() {
	var color = jQuery('#progo_colorscheme').val();
	jQuery('#progo_color_thm').attr('src','<?php bloginfo('template_url'); ?>/images/'+ color +'/screenshot-thm.jpg');
}

jQuery(function($) {
	$('#progo_colorscheme').after('<img id="progo_color_thm" style="border:1px solid #DFDFDF; width: 150px" />').parent().attr('valign','top');
	updateScreenshot();
});
</script>
<?php } else {
		echo 'COLOR SCHEMES OPTION HAS BEEN OVERWRITTEN';
	}
}
endif;
if ( ! function_exists( 'progo_field_logo' ) ):
/**
 * outputs HTML for custom "Logo" on Site Settings page
 * @since Direct 1.0
 */
function progo_field_logo() {
	$options = get_option('progo_options');
	if ( $options['logo'] != '' ) {
		$upload_dir = wp_upload_dir();
		$dir = trailingslashit( $upload_dir['baseurl'] );
		$imagepath = $dir . $options['logo'];
		echo '<img src="'. esc_attr( $imagepath ) .'" /> [<a href="'. wp_nonce_url("admin.php?progo_admin_action=reset_logo", 'progo_reset_logo') .'">Delete Logo</a>]<br /><span class="description">Replace Logo</span><br />';
	} ?>
<input type="hidden" id="progo_logo" name="progo_options[logo]" value="<?php echo esc_attr( $options['logo'] ); ?>" />
<input type="file" id="progo_logotemp" name="progo_options[logotemp]" />
<span class="description">Upload your logo here.<br />
Maximum dimensions: 598px Width x 75px Height. Larger images will be automatically scaled down to fit size.<br />
Maximum upload file size: <?php echo ini_get( "upload_max_filesize" ); ?>. Allowable formats: gif/jpg/png. Transparent png's / gif's are recommended.</span>
<?php
#needswork
}
endif;

/**
 * outputs HTML for "API Key" field on Site Settings page
 * @since Direct 1.0.47
 */
function progo_field_apikey() {
	$opt = get_option( 'progo_direct_apikey', true );
	echo '<input id="apikey" name="progo_options[apikey]" class="regular-text" type="text" value="'. esc_html( $opt ) .'" maxlength="39" />';
	$apiauth = get_option( 'progo_direct_apiauth', true );
	switch($apiauth) {
		case 100:
			echo ' <img src="'. get_bloginfo('template_url') .'/images/check.jpg" alt="aok" class="kcheck" />';
			break;
		default:
			echo ' <img src="'. get_bloginfo('template_url') .'/images/x.jpg" alt="X" class="kcheck" title="'. $apiauth .'" />';
			break;
	}
	echo '<br /><span class="description">You API Key was sent via email when you purchased the Direct Response theme from ProGo Themes.</span>';
}

if ( ! function_exists( 'progo_field_blogname' ) ):
/**
 * outputs HTML for "Site Name" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_blogname() {
	$opt = get_option( 'blogname' );
	echo '<input id="blogname" name="progo_options[blogname]" class="regular-text" type="text" value="'. esc_html( $opt ) .'" />';
}
endif;
if ( ! function_exists( 'progo_field_blogdesc' ) ):
/**
 * outputs HTML for "Slogan" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_blogdesc() {
	$opt = get_option( 'blogdescription' ); ?>
<input id="blogdescription" name="progo_options[blogdescription]" class="regular-text" type="text" value="<?php esc_html_e( $opt ); ?>" />
<?php }
endif;
if ( ! function_exists( 'progo_field_showdesc' ) ):
/**
 * outputs HTML for checkbox "Show Slogan" field on Site Settings page
 * @since Direct 1.0.53
 */
function progo_field_showdesc() {
	$options = get_option( 'progo_options' ); ?>
<fieldset><legend class="screen-reader-text"><span>Show Slogan</span></legend><label for="progo_showdesc">
<input type="checkbox" value="1" id="progo_showdesc" name="progo_options[showdesc]"<?php
	if ( (int) $options['showdesc'] == 1 ) {
		echo ' checked="checked"';
	} ?> />
Show the Site Slogan next to the Logo at the top of <a target="_blank" href="<?php echo esc_url( trailingslashit( get_bloginfo( 'url' ) ) ); ?>">your site</a></label>
</fieldset>
<?php }
endif;
if ( ! function_exists( 'progo_field_support' ) ):
/**
 * outputs HTML for "Customer Support" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_support() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_support" name="progo_options[support]" value="<?php esc_html_e( $options['support'] ); ?>" class="regular-text" type="text" />
<span class="description">Enter either a Phone # (like <em>222-333-4444</em>) or email address</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_copyright' ) ):
/**
 * outputs HTML for "Copyright Notice" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_copyright() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_copyright" name="progo_options[copyright]" value="<?php esc_html_e( $options['copyright'] ); ?>" class="regular-text" type="text" />
<span class="description">Copyright notice that appears on the right side of your site's footer.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_cred' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_cred() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_secure" name="progo_options[credentials]" style="width: 95%;"><?php esc_html_e( $options['credentials'] ); ?></textarea><br />
<span class="description">Security Logos can help increase your site's conversion by over 20%. Paste any code that is associated with generating your credentials in the text box above. Please separate each credential's code by a space (ie. "&lt;script type="text/javascript" src="https://godaddy.com/..."&gt;&lt;/span&gt; &lt;script type="text/javascript" src="http://www.verisign.com/..."&gt;&lt;/script&gt;").</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_compinf' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since Direct 1.0.49
 */
function progo_field_compinf() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_companyinfo" name="progo_options[companyinfo]" style="width: 95%;" rows="5"><?php esc_html_e( $options['companyinfo'] ); ?></textarea><br />
<span class="description">This text appears at the end of Transaction Results pages and email receipts.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_button' ) ):
/**
 * outputs HTML for "Checkout Button" field on Site Settings page
 * @since Direct 1.0
 */
function progo_field_button() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_button" name="progo_options[button]" value="<?php esc_html_e( $options['button'] ); ?>" class="regular-text" type="text" />
<span class="description">Text for "Buy Now" button on the <a href="../products-page/checkout/">Checkout</a> page</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_checkout' ) ):
/**
 * outputs HTML for "Checkout Headline" field on Site Settings page
 * @since Direct 1.0.35
 */
function progo_field_checkout() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_checkout" name="progo_options[checkout]" value="<?php echo esc_attr( $options['checkout'] ); ?>" class="regular-text" type="text" />
<span class="description">Headline at the top of the <a href="../products-page/checkout/">Checkout</a> page</span>
<?php }
endif;
if ( ! function_exists( 'progo_section_text' ) ):
/**
 * (dummy) function called by 
 * add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_site_settings' );
 * and
 * add_settings_section( 'progo_info', 'Site Info', 'progo_section_text', 'progo_site_settings' );
 * @since Direct 1.0
 */
function progo_section_text() {
	// echo '<p>intro text...</p>';	
}
endif;
if ( ! function_exists( 'progo_directresponse_post' ) ):
/**
 * hooked to 'the_post' by add_filter in progo_setup()
 * used to hide non-DirectResponse PAGES on the admin DIRECT RESPONSE list page
 * @since Direct 1.0.51
 */
function progo_directresponse_post( &$post ) {
    global $pagenow;
    if ( is_admin() && $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && isset( $_GET['post_type'] ) == 'page' && isset( $_GET['progo_template'] ) && isset( $_GET['progo_template'] ) == 'directresponse.php' ) {
		/*
		$query->query_vars['meta_key'] = '_wp_page_template';
		$query->query_vars['meta_value'] = 'directresponse.php';
		*/
		$template = get_post_meta($post->ID,'_wp_page_template',true);
		if ( $template != 'directresponse.php' ) {
			$post->post_status = 'notdirect';
		} else {
			//wp_die('<pre>'.print_r($post,true).'</pre>');
		}
    }
}
endif;
if ( ! function_exists( 'progo_price' ) ):
/**
 * price formatting function
 * returns the given product's price, with appropriate HTML for display
 * @param integer product ID
 * @uses absint() To sanitize the input parameter.
 * @uses get_post_meta() To retrieve the _wpsc_price for given product ID.
 * @uses get_option() To get the decimal separator string, and the selected currency_type
 * @uses wpdb->get_results() To retrieve the proper currency symbol from the WPSC_TABLE_CURRENCY_LIST db
 * @return formatted html to display product's price
 * @since Direct 1.0
 */
function progo_price( $prod ) {
	$prod = absint( $prod );
	if ( $prod <= 0 ) {
		return '';
	}
	$price = get_post_meta( $prod, '_wpsc_price', true );
	
	$oot = progo_price_helper($price);
//meta[_wpsc_special_price]
	$saleprice = get_post_meta( $prod, '_wpsc_special_price', true );
	if($saleprice) {
		$oot = '<span class="s">'. $oot .'</span>&nbsp;&nbsp;<span class="sale">'. progo_price_helper($saleprice) .'</span>';
	}
	return $oot;
}
endif;
if ( ! function_exists( 'progo_price_helper' ) ):
/**
 * price formatting function
 * returns the given product's price, with appropriate HTML for display
 * @param integer product ID
 * @uses absint() To sanitize the input parameter.
 * @uses get_post_meta() To retrieve the _wpsc_price for given product ID.
 * @uses get_option() To get the decimal separator string, and the selected currency_type
 * @uses wpdb->get_results() To retrieve the proper currency symbol from the WPSC_TABLE_CURRENCY_LIST db
 * @return formatted html to display product's price
 * @since Direct 1.0.34
 */
function progo_price_helper( $price ) {
	global $wpdb;
	$sep = get_option( 'wpsc_decimal_separator' );
	// even though WPeC stores the "decimal separator" in the DB, the price is always stored with a "." separator...
	$dot = strrpos( $price, '.' );
	
	if ( $dot !== false ) {
		$price = substr( $price, 0, $dot ) .'</span><span class="d">'. $sep .'</span><span class="c">'. substr( $price, $dot + 1 );
	} else {
		$price .= '</span><span class="d">'. $sep .'</span><span class="c">00';
	}
	$currency_data = $wpdb->get_results( "SELECT `symbol`,`symbol_html`,`code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id`='" . absint( get_option( 'currency_type' ) ) . "' LIMIT 1", ARRAY_A );
	if ( $currency_data[0]['symbol'] != '' ) {
		$currency_sign = $currency_data[0]['symbol_html'];
	} else {
		$currency_sign = $currency_data[0]['code'];
	}
	
	return '<span class="m">'.$currency_sign.'</span><span class="p">'. $price .'</span>';
}
endif;
if ( ! function_exists( 'progo_set_default_body' ) ):
/**
 * hooked to 'default_content' by add_filter in progo_setup()
 * adds default bullet point copy to BODY field for new PRODUCTS
 * @since Direct 1.0
 */
function progo_set_default_body( $content ) {
	global $post_type;
	if ( $post_type == 'wpsc-product' ) {
		$default_line = "Add a 1-2 Line Benefit Point About Your Product";
		$content = "<ul>";
		for ( $i=0; $i<3; $i++ ) {
			$content .="
	<li>". $default_line ."</li>";
		}
		$content .= "
</ul>";
	}
	return $content;
}
endif;

/**
 * hooked to 'admin_notices' by add_action in progo_setup()
 * used to display "Settings updated" message after Site Settings page has been saved
 * @uses get_option() To check if our Site Settings were just saved.
 * @uses update_option() To save the setting to only show the message once.
 * @since Direct 1.0
 */
function progo_admin_notices() {	
	// api auth check
	$apiauth = get_option( 'progo_direct_apiauth', true );
	if( $apiauth != '100' ) {
	?>
	<div id="message" class="error">
		<p><?php
        switch($apiauth) {
			case 'new':	// key has not been entered yet
				echo '<a href="admin.php?page=progo_site_settings" title="Site Settings">Please enter your ProGo Themes API Key to Activate your theme.</a>';
				break;
			case '999': // invalid key?
				echo 'Your ProGo Themes API Key appears to be invalid. <a href="admin.php?page=progo_site_settings" title="Site Settings">Please double check it.</a>';
				break;
			case '300': // wrong site URL?
				echo '<a href="admin.php?page=progo_site_settings" title="Site Settings">The ProGo Themes API Key you entered</a> is already bound to another URL.';
				break;
		}
		?></p>
	</div>
<?php
	}
	
	if( get_option('progo_settings_just_saved')==true ) {
	?>
	<div id="message" class="updated fade">
		<p>Settings updated. <a href="<?php bloginfo('url'); ?>/">View site</a></p>
	</div>
<?php
		update_option('progo_settings_just_saved',false);
	}
}

/**
 * hooked to 'site_transient_update_themes' by add_filter in progo_setup()
 * checks ProGo-specific URL to see if our theme is up to date!
 * @param array of checked Themes
 * @uses get_allowed_themes() To retrieve list of all installed themes.
 * @uses wp_remote_post() To check remote URL for updates.
 * @return checked data array
 * @since Direct 1.0.28
 */
function progo_update_check($data) {
	if ( is_admin() == false ) {
		return $data;
	}
	
	$themes = get_allowed_themes();
	
	if ( isset( $data->checked ) == false ) {
		$checked = array();
		// fill CHECKED array - not sure if this is necessary for all but doesnt take a long time?
		foreach ( $themes as $thm ) {
			// we don't care to check CHILD themes
			if( $thm['Parent Theme'] == '') {
				$checked[$thm[Template]] = $thm[Version];
			}
		}
		$data->checked = $checked;
	}
	if ( isset( $data->response ) == false ) {
		$data->response = array();
	}
	
	$request = array(
		'slug' => "direct",
		'version' => $data->checked[direct],
		'siteurl' => get_bloginfo('url')
	);
	
	// Start checking for an update
	global $wp_version;
	$apikey = get_option('progo_direct_apikey',true);
	if ( $apikey != '' ) {
		$apikey = substr( strtolower( str_replace( '-', '', $apikey ) ), 0, 32);
	}
	$checkplz = array(
		'body' => array(
			'action' => 'theme_update', 
			'request' => serialize($request),
			'api-key' => $apikey
		),
		'user-agent' => 'WordPress/'. $wp_version .'; '. get_bloginfo('url')
	);

	$raw_response = wp_remote_post('http://www.progo.com/updatecheck/', $checkplz);
	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
		
	if ( !empty( $response ) ) {
		// got response back. check authcode
		//wp_die('response:<br /><pre>'. print_r($response,true) .'</pre><br /><br />apikey: '. $apikey );
		// only save AUTHCODE if APIKEY is not blank.
		if ( $apikey != '' ) {
			update_option( 'progo_direct_apiauth', $response[authcode] );
		} else {
			update_option( 'progo_direct_apiauth', 'new' );
		}
		if ( version_compare($data->checked[direct], $response[new_version], '<') ) {
			$data->response[direct] = array(
				'new_version' => $response[new_version],
				'url' => $response[url],
				'package' => $response[package]
			);
		}
	}
	
	return $data;
}

function progo_to_twentyten() {
	$msg = 'This ProGo Themes site is currently not Activated.';
	
	if(current_user_can('edit_pages')) {
		$msg .= '<br /><br /><a href="'. trailingslashit(get_bloginfo('url')) .'wp-admin/admin.php?page=progo_site_settings">Click here to update your API Key</a>';
	}
	wp_die($msg);
}

if ( ! function_exists( 'progo_product_image_forms' ) ):
/**
 * html for WPSC product images meta box
 * @since Direct 1.0.32
 */
function progo_product_image_forms() {

    global $post;
    
    edit_multiple_image_gallery( $post );

	$tab = has_post_thumbnail($post->ID) ? 'gallery' : 'type';
    ?>
    <p><strong <?php if ( isset( $display ) ) echo $display; ?>><a href="media-upload.php?parent_page=wpsc-edit-products&post_id=<?php echo $post->ID; ?>&type=image&tab=<?php echo esc_attr($tab); ?>&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images"><?php _e( 'Manage Product Images', 'wpsc' ); ?></a></strong></p>
<?php
}
endif;
if ( ! function_exists( 'progo_favorite_actions' ) ):
/**
 * hooked by add_filter to 'favorite_actions'
 * @since Direct 1.0.36
 */
function progo_favorite_actions($actions) {
	$arr = array( 'post-new.php', 'edit.php?post_status=draft', 'edit-comments.php' );
	foreach ( $arr as $gone ) {
		unset($actions[$gone]);
	}
	$first = array(
		'admin.php?progo_admin_action=newdirect' => array( 'New Direct Response', 'edit_pages'	)
	);
	$actions = array_merge($first,$actions);
	//wp_die('<pre>'. print_r($actions,true) .'</pre>');
	return $actions;
}
endif;
if ( ! function_exists( 'progo_admin_post_thumbnail_html' ) ):
/**
 * hooked by add_filter to 'admin_post_thumbnail_html'
 * @since Direct 1.0.36
 */
function progo_admin_post_thumbnail_html($html) {
	global $post_type;
	global $post;
	if( ($post_type=='page') && ( get_post_meta($post->ID,'_wp_page_template',true) == 'directresponse.php' ) ) {
		$html = str_replace(__('Set featured image').'</a>',__('Upload/Select an Image to show</a>, in place of the default Direct Response product title / image / pricing area.<br /><strong>Dimensions: 650px w x 413px h</strong>. Images of any different size will be scaled to fit.'), $html );
	}
	return $html;
}
endif;
/**
 * hooked by add_filter to 'wp_before_admin_bar_render'
 * to tweak the new WP 3.1 ADMIN BAR
 * @since Direct 1.0.50
 */
function progo_admin_bar_render() {
	global $wp_admin_bar;
	// since we are hiding COMMENTING and POSTS right now...
	$wp_admin_bar->remove_menu('new-post');
	$wp_admin_bar->remove_menu('comments');
	
	// add links to ProGo Direct Response pages
	$wp_admin_bar->add_menu( array( 'parent' => 'new-content', 'id' => 'new_directresponse', 'title' => __('Direct Response Page'), 'href' => admin_url( 'admin.php?progo_admin_action=newdirect') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'progo_settings', 'title' => __('Site Settings'), 'href' => admin_url('admin.php?page=progo_site_settings') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'store_settings', 'title' => __('Store Settings'), 'href' => admin_url('admin.php?page=wpsc-settings') ) );
	
	$avail = progo_colorschemes();
	if ( count($avail) > 0 ) {
		$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'progo_colorscheme', 'title' => 'Color Scheme', 'href' => admin_url('admin.php?page=progo_site_settings') ) );
	}
	foreach($avail as $color) {
		$wp_admin_bar->add_menu( array( 'parent' => 'progo_colorscheme', 'id' => 'progo_colorscheme'.esc_attr($color), 'title' => esc_attr($color), 'href' => admin_url('admin.php?progo_admin_action=color'. esc_attr($color) ) ) );
	}
	
	if ( is_page_template('directresponse.php') ) {
		global $post;
		
		$custom = get_post_meta($post->ID,'_progo');
		$direct = $custom[0];
		$prod = absint($direct[plink]);
		
		$wp_admin_bar->add_menu( array( 'parent' => 'edit', 'id' => 'edit_product', 'title' => __('Edit Product'), 'href' => admin_url( 'post.php?post='. $prod .'&action=edit') ) );
	}
}

if(!function_exists('progo_change_tax') ) :
/**
 * overrides wpsc_change_tax so we can use state abbreviations instead of full names...
 * @since Direct 1.0.59
 */
function progo_change_tax() {
	global $wpdb, $wpsc_cart;

	$form_id = absint( $_POST['form_id'] );

	$wpsc_selected_country = $wpsc_cart->selected_country;
	$wpsc_selected_region = $wpsc_cart->selected_region;

	$wpsc_delivery_country = $wpsc_cart->delivery_country;
	$wpsc_delivery_region = $wpsc_cart->delivery_region;


	$previous_country = $_SESSION['wpsc_selected_country'];
	if ( isset( $_POST['billing_country'] ) ) {
		$wpsc_selected_country = $wpdb->escape( $_POST['billing_country'] );
		$_SESSION['wpsc_selected_country'] = $wpsc_selected_country;
	}

	if ( isset( $_POST['billing_region'] ) ) {
		$wpsc_selected_region = absint( $_POST['billing_region'] );
		$_SESSION['wpsc_selected_region'] = $wpsc_selected_region;
	}

	$check_country_code = $wpdb->get_var( " SELECT `country`.`isocode` FROM `" . WPSC_TABLE_REGION_TAX . "` AS `region` INNER JOIN `" . WPSC_TABLE_CURRENCY_LIST . "` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '" . $_SESSION['wpsc_selected_region'] . "' LIMIT 1" );

	if ( $_SESSION['wpsc_selected_country'] != $check_country_code ) {
		$wpsc_selected_region = null;
	}

	if ( isset( $_POST['shipping_country'] ) ) {
		$wpsc_delivery_country = $wpdb->escape( $_POST['shipping_country'] );
		$_SESSION['wpsc_delivery_country'] = $wpsc_delivery_country;
	}
	if ( isset( $_POST['shipping_region'] ) ) {
		$wpsc_delivery_region = absint( $_POST['shipping_region'] );
		$_SESSION['wpsc_delivery_region'] = $wpsc_delivery_region;
	}

	$check_country_code = $wpdb->get_var( " SELECT `country`.`isocode` FROM `" . WPSC_TABLE_REGION_TAX . "` AS `region` INNER JOIN `" . WPSC_TABLE_CURRENCY_LIST . "` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '" . $wpsc_delivery_region . "' LIMIT 1" );

	if ( $wpsc_delivery_country != $check_country_code ) {
		$wpsc_delivery_region = null;
	}


	$wpsc_cart->update_location();
	$wpsc_cart->get_shipping_method();
	$wpsc_cart->get_shipping_option();
	if ( $wpsc_cart->selected_shipping_method != '' ) {
		$wpsc_cart->update_shipping( $wpsc_cart->selected_shipping_method, $wpsc_cart->selected_shipping_option );
	}

	$tax = $wpsc_cart->calculate_total_tax();
	$total = wpsc_cart_total();
	$total_input = wpsc_cart_total(false);
	if($wpsc_cart->coupons_amount >= wpsc_cart_total() && !empty($wpsc_cart->coupons_amount)){
		$total = 0;
	}
	if ( $wpsc_cart->total_price < 0 ) { 
		$wpsc_cart->coupons_amount += $wpsc_cart->total_price; 
		$wpsc_cart->total_price = null; 
		$wpsc_cart->calculate_total_price(); 
	} 
	ob_start();

	include_once( wpsc_get_template_file_path( 'wpsc-cart_widget.php' ) );
	$output = ob_get_contents();

	ob_end_clean();

	$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
	if ( get_option( 'lock_tax' ) == 1 ) {
		echo "jQuery('#current_country').val('" . $_SESSION['wpsc_delivery_country'] . "'); \n";
		if ( $_SESSION['wpsc_delivery_country'] == 'US' && get_option( 'lock_tax' ) == 1 ) {
			$output = wpsc_shipping_region_list( $_SESSION['wpsc_delivery_country'], $_SESSION['wpsc_delivery_region'] );
			$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
			echo "jQuery('#region').remove();\n\r";
			echo "jQuery('#change_country').append(\"" . $output . "\");\n\r";
		}
	}


	foreach ( $wpsc_cart->cart_items as $key => $cart_item ) {
		echo "jQuery('#shipping_$key').html(\"" . wpsc_currency_display( $cart_item->shipping ) . "\");\n\r";
	}

	echo "jQuery('#checkout_shipping').html(\"" . wpsc_cart_shipping() . "\");\n\r";

	echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
	if ( get_option( 'lock_tax' ) == 1 ) {
		echo "jQuery('.shipping_country').val('" . $_SESSION['wpsc_delivery_country'] . "') \n";
		$sql = "SELECT `country` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `isocode`='" . $_SESSION['wpsc_selected_country'] . "'";
		$country_name = $wpdb->get_var( $sql );
		echo "jQuery('.shipping_country_name').html('" . $country_name . "') \n";
	}


	$form_selected_country = null;
	$form_selected_region = null;
	$onchange_function = null;

	if ( ($_POST['billing_country'] != 'undefined') && !isset( $_POST['shipping_country'] ) ) {
		$form_selected_country = $wpsc_selected_country;
		$form_selected_region = $wpsc_selected_region;
		$onchange_function = 'progo_set_billing_country';
	} else if ( ($_POST['shipping_country'] != 'undefined') && !isset( $_POST['billing_country'] ) ) {
		$form_selected_country = $wpsc_delivery_country;
		$form_selected_region = $wpsc_delivery_region;
		$onchange_function = 'progo_set_billing_country';
	}

	if ( ($form_selected_country != null) && ($onchange_function != null) ) {
		$region_list = $wpdb->get_results( "SELECT `" . WPSC_TABLE_REGION_TAX . "`.* FROM `" . WPSC_TABLE_REGION_TAX . "`, `" . WPSC_TABLE_CURRENCY_LIST . "`  WHERE `" . WPSC_TABLE_CURRENCY_LIST . "`.`isocode` IN('" . $form_selected_country . "') AND `" . WPSC_TABLE_CURRENCY_LIST . "`.`id` = `" . WPSC_TABLE_REGION_TAX . "`.`country_id`", ARRAY_A );
		if ( $region_list != null ) {
			$title = (empty($_POST['billing_country']))?'shippingstate':'billingstate';
			$output = "<select name='collected_data[" . $form_id . "][1]' class='current_region' onchange='$onchange_function(\"region_country_form_$form_id\", \"$form_id\");' title='" . $title . "'>\n\r";

			foreach ( $region_list as $region ) {
				if ( $form_selected_region == $region['id'] ) {
					$selected = "selected='selected'";
				} else {
					$selected = "";
				}
				$output .= "   <option value='" . $region['id'] . "' $selected>" . htmlspecialchars( $region['code'] ) . "</option>\n\r";
			}
			$output .= "</select>\n\r";

			$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
			echo "jQuery('#region_select_$form_id').html(\"" . $output . "\");\n\r";
			echo "
				progo_selectcheck('$form_id', true);
			";
		} else {
			if ( get_option( 'lock_tax' ) == 1 ) {
				echo "jQuery('#region').hide();";
			}
			echo "jQuery('#region_select_$form_id').html('');\n\r";
			echo "
				progo_selectcheck('$form_id', false);
			";
		}
	}
	if ( $tax > 0 ) {
		echo "jQuery(\"tr.total_tax\").show();\n\r";
	} else {
		echo "jQuery(\"tr.total_tax\").hide();\n\r";
	}
	echo "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>" . wpsc_cart_tax() . "</span>\");\n\r";
	echo "jQuery('#checkout_total').html(\"{$total}<input id='shopping_cart_total_price' type='hidden' value='{$total_input}' />\");\n\r";
	echo "if(jQuery(\"#shippingSameBilling\").is(\":checked\")) wpsc_shipping_same_as_billing();";
	exit();
}
endif;
// execute on POST and GET
if ( isset( $_REQUEST['wpsc_ajax_action'] ) && ($_REQUEST['wpsc_ajax_action'] == 'change_tax') ) {
	remove_action( 'init', 'wpsc_change_tax' );
	add_action( 'init', 'progo_change_tax' );
}
if(!function_exists('progo_mail_content_type')):
function progo_mail_content_type( $content_type ) {
	return 'text/html';
}
endif;