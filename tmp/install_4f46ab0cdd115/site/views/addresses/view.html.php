<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaViewAddresses extends TiendaViewBase 
{
	function _default($tpl = null)
	{
        parent::_default($tpl);
        if (JRequest::getVar('tmpl') == 'component')
        {
        	$this->assign( 'tmpl', '&amp;tmpl=component' );
        }
	}
}
