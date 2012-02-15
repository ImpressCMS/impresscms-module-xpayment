<?php
/**
 * Invoice Transaction Gateway with Modular Plugin set
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Chronolabs Co-Op http://www.chronolabs.coop/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         xpayment
 * @since           1.30.0
 * @author          Simon Roberts <simon@chronolabs.coop>
 * @translation     Erol Konik <aphex@aphexthemes.com>
 * @translation     Mariane <mariane_antoun@hotmail.com>
 * @translation     Voltan <voltan@icms.ir>
 * @translation     Ezsky <ezskyyoung@gmail.com>
 * @translation     Richardo Costa <lusopoemas@gmail.com>
 * @translation     Kris_fr <kris@fricms.org>
 */



if (!defined('ICMS_ROOT_PATH')) {
	exit();
}
/**
 * Class for Blue Room Xcenter
 * @author Simon Roberts <simon@icms.org>
 * @copyright copyright (c) 2009-2003 ICMS.org
 * @package kernel
 */
class XpaymentGroups extends icms_core_Object
{
	
    function __construct()
    {
        $this->initVar('rid', XOBJ_DTYPE_INT, null, false);
		$this->initVar('mode', XOBJ_DTYPE_ENUM, null, false, false, false, array('BROKERS','ACCOUNTS','OFFICERS'));
		$this->initVar('plugin', XOBJ_DTYPE_TXTBOX, '*', false, 128);
		$this->initVar('uid', XOBJ_DTYPE_INT, null, false);
		$this->initVar('limit', XOBJ_DTYPE_INT, null, false);
		$this->initVar('minimum', XOBJ_DTYPE_DECIMAL, null, false);
		$this->initVar('maximum', XOBJ_DTYPE_DECIMAL, null, false);
		
		foreach($this->vars as $key => $data) {
			$this->vars[$key]['persistent'] = true;
		}
		
	}
	
	public function getValues($keys = null, $format = 's', $maxDepth = 1) {
		$ret = parent::getValues();
		$user_handler =& icms::handler('icms_member_user');
		$user = $user_handler->get($this->getVar('uid'));
		$ret['user'] = $user->getValues();
		return $ret;
	}
}


/**
* ICMS policies handler class.
* This class is responsible for providing data access mechanisms to the data source
* of ICMS user class objects.
*
* @author  Simon Roberts <simon@chronolabs.coop>
* @package kernel
*/
class XpaymentGroupsHandler extends icms_ipf_Handler
{
    function __construct(&$db) 
    {
		$this->db = $db;
        parent::__construct($db, 'groups', "rid", "plugin", '', 'xpayment');
    }
    	
    function getUids($mode, $plugin, $grand) {
    	
    	$ret = array();
    	
    	$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('`mode`', $mode));
    	$criteria->add(new icms_db_criteria_Item('`plugin`', '*'));
    	$criteria->add(new icms_db_criteria_Item('`limit`', '0'));
    	$objs = $this->getObjects($criteria);
    	foreach($objs as $rid=>$obj)
    		$ret[$obj->getVar('uid')]=$obj->getVar('uid');
    	
    	$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('`mode`', $mode));
    	$criteria->add(new icms_db_criteria_Item('`plugin`', $plugin));
    	$criteria->add(new icms_db_criteria_Item('`limit`', '0'));
    	$objs = $this->getObjects($criteria);
    	foreach($objs as $rid=>$obj)
    		$ret[$obj->getVar('uid')]=$obj->getVar('uid');

        $criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('`mode`', $mode));
    	$criteria->add(new icms_db_criteria_Item('`plugin`', '*'));
    	$criteria->add(new icms_db_criteria_Item('`limit`', '1'));
    	$criteria->add(new icms_db_criteria_Item('`minimum`', $grand, '>='));
    	$criteria->add(new icms_db_criteria_Item('`maximum`', $grand, '<='));
    	$objs = $this->getObjects($criteria);
    	foreach($objs as $rid=>$obj)
    		$ret[$obj->getVar('uid')]=$obj->getVar('uid');
    	
    	$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('`mode`', $mode));
    	$criteria->add(new icms_db_criteria_Item('`plugin`', $plugin));
    	$criteria->add(new icms_db_criteria_Item('`limit`', '1'));
    	$criteria->add(new icms_db_criteria_Item('`minimum`', $grand, '>='));
    	$criteria->add(new icms_db_criteria_Item('`maximum`', $grand, '<='));
    	$objs = $this->getObjects($criteria);
    	foreach($objs as $rid=>$obj)
    		$ret[$obj->getVar('uid')]=$obj->getVar('uid');

    	return $ret;
    }
}

?>