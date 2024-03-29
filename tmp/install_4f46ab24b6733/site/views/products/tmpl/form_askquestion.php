<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php
$sender_name 	= JRequest::getVar('sender_name', '');
$sender_mail 	= JRequest::getVar('sender_mail', '');
$sender_message = JRequest::getVar('sender_message', '');
?>
<?php $success = JRequest::getInt('success', 0);?>
<?php if($success && TiendaConfig::getInstance()->get('ask_question_modal', '1')):?>
<script type="text/javascript">
onload=setTimeout("window.parent.document.getElementById( 'sbox-window' ).close()", 2000);
</script>
<?php endif;?>
<div class="tienda_askquestion" style="padding: 20px;">
<form id="adminForm" action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
	<label for="sender_name"><?php echo JText::_('Name');?></label>
	<br><input type="text" value="<?php echo $sender_name;?>" class="inputbox" size="30" id="sender_name" name="sender_name"><br><br>
	<label for="sender_mail"><?php echo JText::_('E-mail Address');?></label>
	<br><input type="text" value="<?php echo $sender_mail;?>" class="inputbox" label="Your email" size="30" name="sender_mail" id="sender_mail"><br><br>
	<label for="sender_message"><?php echo JText::_('Enter your Message');?></label><br>
	<textarea class="inputbox" id="sender_message" name="sender_message" cols="60" rows="10"><?php echo $sender_message;?></textarea><br>
	<!-- CAPTCHA HERE -->	
	<?php if (TiendaConfig::getInstance()->get('ask_question_showcaptcha', '1') == 1 ): ?>          
    <?php Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );?>
    <?php $recaptcha = new TiendaRecaptcha(); ?>
    <?php $publickey = "6LcAcbwSAAAAAIEtIoDhP0cj7AAQMK9hqzJyAbeD"; ?>
    <div><?php echo $recaptcha->recaptcha_get_html($publickey); ?></div>
    <?php endif;?> 
    <br>
    <input type="button" onclick="tiendaSubmitForm('sendAskedQuestion');" value="<?php echo JText::_('Send'); ?>" />
    <input type="hidden" name="product_id" value="<?php echo JRequest::getInt('id');; ?>" />
    <input type="hidden" name="task" id="task" value="" />
    <input type="hidden" name="return" id="return" value="<?php echo JRequest::getVar('return');?>" />	
</form>
</div>