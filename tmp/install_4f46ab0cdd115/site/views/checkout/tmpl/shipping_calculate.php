<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php  if (!TiendaConfig::getInstance()->get('one_page_checkout')) :?>
	<h3><?php echo JText::_("Select a Shipping Method") ?></h3>
	<input type="button" class="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form, '<?php echo JText::_( 'Updating Shipping Rates' )?>', '<?php echo JText::_( 'Updating Cart' )?>' )" value="<?php echo JText::_("Calculate shipping rates"); ?>" />
<?php endif; ?>
<input type="hidden" id="shippingrequired" name="shippingrequired" value="1"  />
<div class="note">
	<?php echo JText::_( "NO SHIPPING RATES FOUND" ); ?>
</div>
