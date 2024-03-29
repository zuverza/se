<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Order ID' ); ?>:
                    </td>
                    <td>
                        <input name="order_id" value="<?php echo @$row->order_id; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'OrderPayment Type' ); ?>:
                    </td>
                    <td>
                        <input name="orderpayment_type" value="<?php echo @$row->orderpayment_type; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'OrderPayment Amount' ); ?>:
                    </td>
                    <td>
                        <input name="orderpayment_amount" value="<?php echo @$row->orderpayment_amount; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'OrderPayment Date' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->created_date, "created_date", "created_date", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction ID' ); ?>:
                    </td>
                    <td>
                        <input name="transaction_id" value="<?php echo @$row->transaction_id; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction Status' ); ?>:
                    </td>
                    <td>
                        <input name="transaction_status" value="<?php echo @$row->transaction_status; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction Details' ); ?>:
                    </td>
                    <td>
                        <textarea cols="50" rows="10" name="transaction_details"><?php echo @$row->transaction_details; ?></textarea>
                    </td>
                </tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->orderpayment_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>