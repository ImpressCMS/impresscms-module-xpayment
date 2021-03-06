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
class XpaymentGateways_options extends icms_core_Object
{

    function __construct()
    {
        $this->initVar('goid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gid', XOBJ_DTYPE_INT, null, false);
		$this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('value', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('refereer', XOBJ_DTYPE_TXTBOX, null, false, 255);
		
		foreach($this->vars as $key => $data) {
			$this->vars[$key]['persistent'] = true;
		}
		
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
class XpaymentGateways_optionsHandler extends icms_ipf_Handler
{
    function __construct(&$db) 
    {
		$this->db = $db;
        parent::__construct($db, 'gateways_options', "goid", "name", '', 'xpayment');
    }
	
}

?>