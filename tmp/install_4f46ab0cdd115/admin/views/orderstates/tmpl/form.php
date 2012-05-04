<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input name="order_state_name" id="order_state_name" value="<?php echo @$row->order_state_name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->order_state_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>