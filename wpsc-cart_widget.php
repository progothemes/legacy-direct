<?php if(isset($cart_messages) && count($cart_messages) > 0) { ?>
	<?php foreach((array)$cart_messages as $cart_message) { ?>
	  <span class="cart_message"><?php echo $cart_message; ?></span>
	<?php } ?>
<?php } ?>
 <div class="shoppingcart"><div class="cart-widget-count"><?php $itemcount = wpsc_cart_item_count(); ?><strong>(<?php echo absint($itemcount); ?>)</strong> Item<?php echo ($itemcount > 1 ? 's' : ''); ?> in your cart</div>
<?php if(wpsc_cart_item_count() > 0): ?>
	<table>
		<tbody>
		<?php while(wpsc_have_cart_items()): wpsc_the_cart_item(); ?>
			<tr>
                <td class='product-name'><a href="<?php echo wpsc_cart_item_url(); ?>"><?php echo wpsc_cart_item_name(); ?></a></td>
                <td><?php echo wpsc_cart_item_quantity(); ?></td>
                <td><?php echo wpsc_cart_item_price(); ?></td>
                <?php /*td class="cart-widget-remove"><form action="" method="post" class="adjustform">
					<input type="hidden" name="quantity" value="0" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input class="remove_button" type="submit" />
				</form></td */ ?>
			</tr>	
		<?php endwhile; ?>
		</tbody>
		<tfoot>
					<?php if(wpsc_cart_has_shipping() && !wpsc_cart_show_plus_postage()) : ?>
			<tr class="cart-widget-total cart-widget-shipping pricedisplay checkout-shipping">
            	<td><?php _e('Shipping', 'wpsc'); ?></td><td colspan="2" align="right"><?php echo wpsc_cart_shipping(); ?></td>
            </tr>
					<?php endif; ?>
					<?php if( (wpsc_cart_tax(false) >0) && !wpsc_cart_show_plus_postage()) : ?>
			<tr class="pricedisplay checkout-tax">
				<td><?php echo wpsc_display_tax_label(true); ?></td><td colspan="2" align="right"><?php echo wpsc_cart_tax(); ?></td>
            </tr>
					<?php endif; ?>
			<tr class="pricedisplay checkout-total cart-widget-total">
				<td><?php _e('Total', 'wpsc'); ?></td><td colspan="2" align="right"><?php echo wpsc_cart_total_widget(); ?></td>
			</tr>
			<?php if(wpsc_cart_show_plus_postage()) : ?>
			<tr class="pluspostagetax"><td colspan="3">+ <?php _e('Postage &amp; Tax ', 'wpsc'); ?></td>
			</tr>
			<?php endif; ?>
			<tr id='cart-widget-links'>
				<td><a target="_parent" href="<?php echo get_option('shopping_cart_url'); ?>" title="Checkout" class="gocheckout"><?php _e('Checkout', 'wpsc'); ?></a>
                    </td><td colspan="2" align="right">
					<form action="" method="post" class="wpsc_empty_the_cart">
						<input type="hidden" name="wpsc_ajax_action" value="empty_cart" />
							<a target="_parent" href="<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES); ?>" class="emptycart" title="Empty Your Cart"><?php _e('Empty cart', 'wpsc'); ?></a>                                                                                    
					</form>
				</td>
			</tr>
		</tfoot>
	</table>	
<?php endif; ?>
	</div><!--close shoppingcart-->	
<?php
wpsc_google_checkout();
?>