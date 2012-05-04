<?php defined('_JEXEC') or die('Restricted access'); ?>	
<div class="note">
	<?php echo count($this->payment_plugins) ? JText::_("PAYMENT NOTE 1").":" : JText::_( "PAYMENT NOTE 2" );?>
</div>
<?php if(count($this->payment_plugins)):?>
	<?php foreach($this->payment_plugins as $payment_plugin):?>
	<input value="<?php echo $payment_plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $payment_plugin->element; ?>', 'payment_form_div'); $('validation_message').setHTML(''); $('payment_form_div').addClass('note')" name="payment_plugin" type="radio" <?php echo (!empty($payment_plugin->checked)) ? "checked" : ""; ?> />
	<?php echo JText::_( $payment_plugin->name ); ?>
	<br />
	<?php endforeach;?>
	
	 <div id='payment_form_div' <?php if(!empty($this->payment_form_div)) echo 'class="note"';?> style="padding-top: 5px;">
	 <?php if(!empty($this->payment_form_div)):?>
	 	<?php echo $this->payment_form_div;?>
	 <?php endif;?>
	 </div>
<?php endif;?>
