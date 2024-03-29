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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperProductDownload extends TiendaHelperBase
{
    /**
     * Given a productfile or an array of productfiles
     * will filter productfiles if the user cannot download them 
     *  
     * @param mixed $items
     * @param int $user_id
     * @return array
     */
    function filterRestricted( $productfiles, $user_id )
    {
        (array) $productfiles;
        $filtered = array();
        $unfiltered = array();
               
        foreach ($productfiles as $productfile)
        {
            if (TiendaHelperProductDownload::canDownload( $productfile->productfile_id, $user_id))
            {
                $filtered[] = $productfile;
            }
                else 
            {
            	$unfiltered[] = $productfile;
            }
        }
        
        $allItems = array( 0=>$filtered, 1=>$unfiltered );
        return $allItems;
    }
    
    /**
     * Given a productfile_id and user_id
     * determines if user can download file
     *  
     * @param $productfile_id
     * @param $user_id
     * @return boolean
     */
    function canDownload( $productfile_id, $user_id, $datetime=null )
    {
    	
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $productfile = JTable::getInstance( 'ProductFiles', 'TiendaTable' );
        $productfile->load( $productfile_id );
      	 if ($productfile->canDownload( $user_id, $datetime ))
        {
            return true;
        }
        $productToDownloads = $this->getProductDownloadInfo($productfile_id, $user_id);
        
        // check he has atleast one attempts
        if (!empty($productToDownloads))
        {
         return true;
       
        }
        return false;
    }
    
    /**
     *  Given the user id and the file id will return the row id on which entry is greate then 0
     *  
     *  @param user id
     *  @param productfile id
     *  @return productdown load id
     */
    function getProductDownloadInfo( $productfile_id, $user_id )
    {
    	Tienda::load( 'TiendaQuery', 'library.query' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $tableProductDownload = JTable::getInstance( 'ProductDownloads', 'TiendaTable' );
       
        $query = new TiendaQuery();
        
        $select[]="productdl.*";
        
        $query->select( $select );
        $query->from($tableProductDownload->getTableName()." AS productdl");
       
        $whereClause[]="productdl.user_id = ".(int)$user_id;
        $whereClause[]="productdl.productfile_id='".$productfile_id."'";
        $whereClause[]="productdl.productdownload_max > 0";

        // Assumed that 0000-00-00 00:00:00 is the entry for the unlimited Downloads 
        
        // TODO apply the where task for the Date
        
        $query->where($whereClause,"AND" );
        $db = JFactory::getDBO();
        $db->setQuery( (string) $query );
        $item = $db->loadObject();
        return $item;
    }
    
 }