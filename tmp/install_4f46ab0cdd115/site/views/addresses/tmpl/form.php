<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $tmpl = @$this->tmpl; ?>
<?php JFilterOutput::objectHTMLSafe( $row ); ?>

<div class='componentheading'>
    <span><?php echo JText::_( "Edit Address" ); ?></span>
</div>

<form action="<?php echo JRoute::_( @$form['action'].$tmpl ) ?>" onsubmit="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', document.adminForm.task.value, document.adminForm )" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <div style="float: right;">
        <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />    
    </div>

    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=addresses".$tmpl)."'>".JText::_( 'Cancel and Return to List' )."</a>";
    ?>
    
    <div id="validationmessage"></div>
	<?php echo $this->form_inner; ?>
    <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />

    <input type="hidden" name="id" value="<?php echo @$row->address_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />

    <?php echo @$form['validate']; ?>

</form>