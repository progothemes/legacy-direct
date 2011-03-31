<?php
global $wpsc_cart, $wpdb, $wpsc_checkout, $wpsc_gateway, $wpsc_coupons;
$wpsc_checkout = new wpsc_checkout();
$wpsc_gateway = new wpsc_gateways();
$alt = 0;
if(isset($_SESSION['coupon_numbers']))
   $wpsc_coupons = new wpsc_coupons($_SESSION['coupon_numbers']);

if(wpsc_cart_item_count() < 1) : ?>
<div style="padding: 20px"><?php
   _e('Oops, there is nothing in your cart.', 'wpsc');
?></div><?php
   return;
endif;

$options = get_option('progo_options');
//echo '<pre style="display:none">'. print_r($_SESSION,true) .'</pre>';
?>
<table class="productcart">
   <tr class="firstrow">
		<td class="rz" width="76">Product</td>
        <td>Title</td>
        <td width="85">Quantity</td>
        <td width="80" class="rz">Price</td>
   </tr>
   <?php if(function_exists('easyupsell_products')) easyupsell_products(); ?>
   <?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
      <?php  //this displays the confirm your order html ?>
      <tr class="product_row">
         <td class="pthm rz"><img src="<?php echo wpsc_cart_item_image(70,70); ?>" alt="<?php echo wpsc_cart_item_name(); ?>" title="<?php echo wpsc_cart_item_name(); ?>" /></td>
         <td><?php echo wpsc_cart_item_name();
		 // should we include the ADDITIONAL DESCRIPTION field of a Product as well?
		  ?></td>
         <td>
            <form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform qty">
               <input type="text" name="quantity" size="2" value="<?php echo wpsc_cart_item_quantity(); ?>" />
               <input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
               <input type="hidden" name="wpsc_update_quantity" value="true" />
               <input type="submit" value="Update" class="upd" name="submit" />
            </form>
         </td>
		<td class="rz"><span class="pricedisplay"><?php echo wpsc_cart_single_item_price(); ?></span></td>
        <?php /*
         <td class="wpsc_product_price wpsc_product_price_<?php echo wpsc_the_cart_item_key(); ?>"><span class="pricedisplay"><?php echo wpsc_cart_item_price(); ?></span></td>

         <td class="wpsc_product_remove wpsc_product_remove_<?php echo wpsc_the_cart_item_key(); ?>">
            <form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform remove">
               <input type="hidden" name="quantity" value="0" />
               <input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
               <input type="hidden" name="wpsc_update_quantity" value="true" />
               <input type="submit" value="<?php _e('Remove', 'wpsc'); ?>" name="submit" />
            </form>
         </td>
		 */ ?>
      </tr>
   <?php endwhile; ?>
   <?php //this HTML displays coupons if there are any active coupons to use ?>

   <?php

   if(wpsc_uses_coupons()): ?>

      <?php if(wpsc_coupons_error()): ?>
         <tr class="wpsc_coupon_row wpsc_coupon_error_row"><td><?php _e('Coupon is not valid.', 'wpsc'); ?></td></tr>
      <?php endif; ?>
      <tr class="wpsc_coupon_row">
         <td colspan="2"><?php _e('Enter coupon code'); ?> :</td>
         <td  colspan="4" class="coupon_code">
            <form  method="post" action="<?php echo get_option('shopping_cart_url'); ?>">
               <input type="text" name="coupon_num" id="coupon_num" value="<?php echo $wpsc_cart->coupons_name; ?>" />
               <input type="submit" value="<?php _e('Update', 'wpsc') ?>" />
            </form>
         </td>
      </tr>
      <tr class="wpsc_total_before_shipping">
	      <td colspan="3"><?php _e('Cost before shipping:','wpsc'); ?></td>
	      <td colspan="3" class="wpsc_total_amount_before_shipping"><?php echo wpsc_cart_total_widget(false,false,false);?></td>
      </tr>
   <?php endif; ?>
   </table>
   <!-- cart contents table close -->
   <?php  //this HTML displays the calculate your order HTML   ?>

   <?php if(wpsc_has_category_and_country_conflict()): ?>
      <p class='validation-error'><?php echo $_SESSION['categoryAndShippingCountryConflict']; ?></p>
      <?php unset($_SESSION['categoryAndShippingCountryConflict']);
   endif;

   if(isset($_SESSION['WpscGatewayErrorMessage']) && $_SESSION['WpscGatewayErrorMessage'] != '') :?>
      <p class="validation-error"><?php echo $_SESSION['WpscGatewayErrorMessage']; ?></p>
   <?php
   endif;
   ?>

   <?php do_action('wpsc_before_shipping_of_shopping_cart'); ?>

   <div id="wpsc_shopping_cart_container">
   <?php if(wpsc_uses_shipping()) : ?>
      <table class="productcart shipping">
      <tr class="firstrow">
      <td><?php _e('Calculate Shipping Price', 'wpsc'); ?></td>
      </tr>
         <tr class="wpsc_shipping_info addon">
            <td>
               <?php _e('Please choose a country below to calculate your shipping costs', 'wpsc'); ?>
            </td>
         </tr>

         <?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
            <?php if (wpsc_have_valid_shipping_zipcode()) : ?>
                  <tr class='wpsc_update_location addon'>
                     <td class='shipping_error' >
                        <?php _e('Please provide a Zipcode and click Calculate in order to continue.', 'wpsc'); ?>
                     </td>
                  </tr>
            <?php else: ?>
               <tr class='wpsc_update_location_error addon'>
                  <td class='shipping_error' >
                     <?php _e('Sorry, online ordering is unavailable to this destination and/or weight. Please double check your destination details.', 'wpsc'); ?>
                  </td>
               </tr>
            <?php endif; ?>
         <?php endif; ?>
         <tr class='wpsc_change_country addon'>
            <td>
               <form name='change_country' id='change_country' action='' method='post'>
                  <?php echo wpsc_shipping_country_list();?>
                  <input type='hidden' name='wpsc_update_location' value='true' />
                  <input type='submit' name='wpsc_submit_zipcode' value='Calculate' />
               </form>
            </td>
         </tr>

         <?php if (wpsc_have_morethanone_shipping_quote()) :?>
            <?php while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); ?>
                  <?php    if (!wpsc_have_shipping_quotes()) { continue; } // Don't display shipping method if it doesn't have at least one quote ?>
                  <tr class='wpsc_shipping_header addon'><td class='shipping_header'><?php echo wpsc_shipping_method_name().__(' - Choose a Shipping Rate', 'wpsc'); ?> </td></tr>
                  <?php while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote();  ?>
                     <tr class='<?php echo wpsc_shipping_quote_html_id(); ?> addon'>
                        <td class='wpsc_shipping_quote_name wpsc_shipping_quote_name_<?php echo wpsc_shipping_quote_html_id(); ?>'><label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_name(); ?></label>: <?php echo wpsc_shipping_quote_value(); ?><?php if(wpsc_have_morethanone_shipping_methods_and_quotes()): ?>
                              <input type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>' <?php echo wpsc_shipping_quote_selected_state(); ?>  onclick='switchmethod("<?php echo wpsc_shipping_quote_name(); ?>", "<?php echo wpsc_shipping_method_internal_name(); ?>")' value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
                           <?php else: ?>
                              <input <?php echo wpsc_shipping_quote_selected_state(); ?> disabled='disabled' type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>'  value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
                                 <?php wpsc_update_shipping_single_method(); ?>
                           <?php endif; ?>
                        </td>
                     </tr>
                  <?php endwhile; ?>
            <?php endwhile; ?>
         <?php endif; ?>

         <?php wpsc_update_shipping_multiple_methods(); ?>


         <?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
               </table>
               </div>
            <?php return; ?>
         <?php endif; ?>
      </table>
   <?php endif;  ?>

   <table class="wpsc_checkout_table totals">
   <?php
      $wpec_taxes_controller = new wpec_taxes_controller();
      if($wpec_taxes_controller->wpec_taxes_isenabled()):
   ?>
         <tr class="addon total_tax">
		 <td align="right"><?php echo wpsc_display_tax_label(true); ?>: <span id="checkout_tax" class="pricedisplay checkout-tax"><?php echo wpsc_cart_tax(); ?></span></td>
         </tr>
         
   <?php endif; ?>
      <?php if(wpsc_uses_shipping()) : /*?>
	      <tr>
	         <td class='wpsc_total_price_and_shipping'colspan='2'>
	            <h4><?php _e('Review and purchase','wpsc'); ?></h4>
	         </td>
	      </tr>
*/ ?>	
	      <tr class="addon total_shipping">
	         <td align="right"><?php _e('Total Shipping', 'wpsc'); ?>: <span id="checkout_shipping" class="pricedisplay checkout-shipping"><?php echo wpsc_cart_shipping(); ?></span></td>
	      </tr>
      <?php endif; ?>

     <?php if(wpsc_uses_coupons() && (wpsc_coupon_amount(false) > 0)): ?>
      <tr class="addon">
         <td align="right"><?php _e('Discount', 'wpsc'); ?>: <span id="coupons_amount" class="pricedisplay"><?php echo wpsc_coupon_amount(); ?></span></td>
         </tr>
     <?php endif ?>
         <tr class="total_price product_row">
		<td align="right"><strong>Total Price: <span id='checkout_total' class="pricedisplay checkout-total"><?php echo wpsc_cart_total(); ?></span></strong></td>
	</tr>
	
	
	</table>
    <table>
    <tr><td><img src="<?php bloginfo('template_url'); ?>/images/easy.gif" alt="Easy &amp; Secure" /></td><td style="vertical-align:middle">Customer Support: <?php if($options['support_email']) {
					 echo '<a href="mailto:'. $options['support'] .'">email us</a>';
				 } else echo $options['support']; ?></td></tr>
    </table>
</div></div></div></div>
            <div class="grid_4 omega" id="side">
   <?php do_action('wpsc_before_form_of_shopping_cart'); ?>         
   <form class="pform" action='' method='post' enctype="multipart/form-data">
            <table width="96%" cellpadding="0" cellspacing="0" height="455" class="wpsc_checkout_table">
            <tr class="hdr"><td><h3>Payment Information<br />Secure Transaction</h3></td></tr>
            <tr><td>
      <?php
      /**
       * Both the registration forms and the checkout details forms must be in the same form element as they are submitted together, you cannot have two form elements submit together without the use of JavaScript.
      */
      ?>
<?php
      if(!empty($_SESSION['wpsc_checkout_misc_error_messages'])): ?>
         <div class='login_error'>
            <?php foreach((array)$_SESSION['wpsc_checkout_misc_error_messages'] as $user_error ){?>
               <p class='validation-error'><?php echo $user_error; ?></p>
               <?php } ?>
         </div>
         </td></tr><tr><td>
      <?php
      endif;
       $_SESSION['wpsc_checkout_misc_error_messages'] = array(); ?>
      <?php 
	  global $wpsc_checkout;
	  progo_direct_form_fields( true, true );
	   ?>

      <?php if (wpsc_show_find_us()) : ?><label for='how_find_us'><?php _e('How did you find us' , 'wpsc'); ?></label><select name='how_find_us'>
               <option value='Word of Mouth'><?php _e('Word of mouth' , 'wpsc'); ?></option>
               <option value='Advertisement'><?php _e('Advertising' , 'wpsc'); ?></option>
               <option value='Internet'><?php _e('Internet' , 'wpsc'); ?></option>
               <option value='Customer'><?php _e('Existing Customer' , 'wpsc'); ?></option>
            </select>
      <?php endif; ?>
      <?php do_action('wpsc_inside_shopping_cart'); ?>
<fieldset id="payment">
<table><tr><td>
      <?php  //this HTML displays activated payment gateways
	  do_action('progo_pre_gateways');
	  $haveformfields = false;
	  if(wpsc_gateway_count() > 1): // if we have more than one gateway enabled, offer the user a choice ?>
         <tr>
         <td colspan='2' class='wpsc_gateway_container'>
            <h3><?php _e('Payment Type', 'wpsc');?></h3>
            <?php
			while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <div class="custom_gateway">
                     <label><input type="radio" value="<?php echo wpsc_gateway_internal_name();?>" <?php echo wpsc_gateway_is_checked(); ?> name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name(); ?> 
                     	<?php if( wpsc_show_gateway_image() ): ?>
                     	<img src="<?php echo wpsc_gateway_image_url(); ?>" alt="<?php echo wpsc_gateway_name(); ?>" style="position:relative; top:5px;" />
                     	<?php endif; ?>
                     </label>

                  <?php if(wpsc_gateway_form_fields()) {
					  $haveformfields = true;
					  ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php } ?>
               </div>
            <?php endwhile; ?>
         <?php else: // otherwise, there is no choice, stick in a hidden form ?>
            <?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
               <input name='custom_gateway' value='<?php echo wpsc_gateway_internal_name();?>' type='hidden' />

                  <?php if(wpsc_gateway_form_fields()):
					  $haveformfields = true;
					  ?>
                     <table class='wpsc_checkout_table <?php echo wpsc_gateway_form_field_style();?>'>
                        <?php echo wpsc_gateway_form_fields();?>
                     </table>
                  <?php endif; ?>
            <?php endwhile; ?>
         </td>
         </tr>
         <?php endif; ?>

      <?php if(wpsc_has_tnc()) : ?>
         <tr>
            <td colspan='2'>
                <input type='checkbox' value='yes' name='agree' /> <?php _e('I agree to The ', 'wpsc');?>
                <a class='thickbox' target='_blank' href='<?php
         echo site_url("?termsandconds=true&amp;width=360&amp;height=400'"); ?>' class='termsandconds'> <?php _e('Terms and Conditions', 'wpsc');?></a>
               </td>
         </tr>
      <?php endif; ?>
      </table>
</fieldset>
<div class="editchecks">
<label><input type="checkbox" name="editbilling" /> Edit my Billing Info</label><br />
<?php if ( absint( get_option( 'do_not_use_shipping' ) ) == 1 ) { 
echo '<br />';
} else { ?>
<label><input type="checkbox" name="editshipping" /> Add/Edit my Shipping Info</label>
<?php } ?>
</div>
</td></tr>
<tr><td align="center" valign="top"><?php echo ( $options['credentials'] != '' ? $options['credentials'] : '<br />' ); ?></td></tr>
            <tr><td height="<?php echo ( $haveformfields ? '63' : '100%" valign="top' ); ?>">
<!-- div for make purchase button -->
      <div class='wpsc_make_purchase'>
         <span>
            <?php if(!wpsc_has_tnc()) : ?>
               <input type='hidden' value='yes' name='agree' />
            <?php endif; ?>
               <input type='hidden' value='submit_checkout' name='wpsc_action' />
               <?php echo apply_filters('progo_checkout_btn', "<input type='submit' value='$options[button]' name='submit' class='make_purchase wpsc_buy_button sbtn buynow' />"); ?>
         </span>
      </div>
</td></tr></table>
</form>
<script type="text/javascript">
jQuery(function($) {
	$('#primary input[name="card_number"]').bind('keyup',function() {
		var iin = $(this).val();
		iin = iin.substr(0,2);
		var cctype = '';
		if(iin>=34 && iin<=37) cctype = 'Amex';
		else {
			if(iin>=51 && iin<=55) cctype = 'MasterCard';
			else {
				if(iin>=60 && iin<=65) cctype = 'Discover';
				else cctype = 'Visa';
			}
		}
		$('#primary select[name="cctype"] option:contains("'+cctype+'")').attr('selected','selected');
	});
	$('#primary select[name^="exp"]').addClass('exp');
	$('#primary fieldset td.wpsc_CC_details').wrapInner('<label />');
});
</script>
<div><div>
<?php
do_action('wpsc_bottom_of_shopping_cart');
?>
