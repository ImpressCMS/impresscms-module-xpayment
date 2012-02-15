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
	
	include('../../mainfile.php');
	
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.functions.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.objects.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'include'.DS.'xpayment.forms.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'class'.DS.'cache'.DS.'icmscache.php');
	require_once (ICMS_ROOT_PATH.''.DS.'modules'.DS.'xpayment'.DS.'language'.DS.$GLOBALS['icmsConfig']['language'].DS.'main.php');

	$GLOBALS['myts'] = icms_core_Textsanitizer::getInstance();
	
	$module_handler = icms::handler('icms_module');
	$config_handler = icms::handler('icms_config');
	$GLOBALS['xpaymentModule'] = $module_handler->getByDirname('xpayment');
	$GLOBALS['xpaymentModuleConfig'] = $config_handler->getConfigList($GLOBALS['xpaymentModule']->getVar('mid')); 
	
	include_once (ICMS_ROOT_PATH. "/class/template.php" );
	$GLOBALS['xoopsTpl'] = new icms_view_Tpl();
	$iid = 0;
	
?>