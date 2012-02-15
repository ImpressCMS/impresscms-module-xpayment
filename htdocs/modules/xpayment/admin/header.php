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
	define('DS', DIRECTORY_SEPARATOR);
	
	include('../../../include/cp_header.php');
	
	if (!defined('_CHARSET'))
		define ("_CHARSET","UTF-8");
	if (!defined('_CHARSET_ISO'))
		define ("_CHARSET_ISO","ISO-8859-1");
		
	$GLOBALS['myts'] = icms_core_Textsanitizer::getInstance();
	
	$module_handler = icms::handler('icms_module');
	$config_handler = icms::handler('icms_config');
	$GLOBALS['xpaymentModule'] = $module_handler->getByDirname('xpayment');
	$GLOBALS['xpaymentModuleConfig'] = $config_handler->getConfigList($GLOBALS['xpaymentModule']->getVar('mid')); 
			
	if ( file_exists(dirname(dirname(__FILE__)).DS.'class'.DS.'moduleclasses'.DS.'moduleadmin'.DS.'moduleadmin.php')){
	        include_once (dirname(dirname(__FILE__)).DS.'class'.DS.'moduleclasses'.DS.'moduleadmin'.DS.'moduleadmin.php');
	        //return true;
	    }else{
	        echo icms_error("Error: You don't use the Frameworks \"admin module\". Please install this Frameworks");
	        //return false;
	    }
	$GLOBALS['xpaymentImageIcon'] = ICMS_URL .'/'. $GLOBALS['xpaymentModule']->getInfo('icons16');
	$GLOBALS['xpaymentImageAdmin'] = ICMS_URL .'/'. $GLOBALS['xpaymentModule']->getInfo('icons32');
	
	if (icms::$user) {
	    $moduleperm_handler =& icms::handler('icms_member_groupperm');
	    if (!$moduleperm_handler->checkRight('module_admin', $GLOBALS['xpaymentModule']->getVar( 'mid' ), icms::$user->getGroups())) {
	        redirect_header(ICMS_URL, 1, _NOPERM);
	        exit();
	    }
	} else {
	    redirect_header(ICMS_URL . "/user.php", 1, _NOPERM);
	    exit();
	}
	
	if (!isset($GLOBALS['xoopsTpl']) || !is_object($GLOBALS['xoopsTpl'])) {
		include_once(ICMS_ROOT_PATH."/class/template.php");
		$GLOBALS['xoopsTpl'] = new icms_view_Tpl();
	}
	
	$GLOBALS['xoopsTpl']->assign('pathImageIcon', $GLOBALS['xpaymentImageIcon']);
	
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.functions.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.objects.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.forms.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'class'.DS.'cache'.DS.'icmscache.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'language'.DS.$GLOBALS['icmsConfig']['language'].DS.'admin.php');
	
	
?>