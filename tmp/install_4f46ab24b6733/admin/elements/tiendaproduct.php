<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

class JElementTiendaProduct extends JElement
{
	var	$_name = 'TiendaProduct';

	function fetchElement($name, $value, &$node, $control_name)
	{
	    
		$html = "";
		$doc 		=& JFactory::getDocument();
		$fieldName	= $control_name ? $control_name.'['.$name.']' : $name;
		$title = JText::_('Select products');
		if ($value) {
			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables');
			$table = JTable::getInstance('Products', 'TiendaTable');
			$table->load($value);
			$title = $table->product_name;
		}
		else
		{
			$title=JText::_('Select a Product');
		}

 		$js = "
		function jSelectProducts(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_tienda&task=elementproduct&tmpl=component&object='.$name;

		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$name.'_name" value="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Select a Product').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= "\n".'<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.(int)$value.'" />';
		
		return $html;
	}
}
?>