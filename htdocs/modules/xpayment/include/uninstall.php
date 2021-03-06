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
function icms_module_uninstall_xpayment(&$module) {

	$language = $GLOBALS['icmsConfig']['language'];
	$file = 'modinfo';
	if ( !file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/{$language}/{$file}.php" )){
		if ( file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/english/{$file}.php" )){
			include_once $fileinc;
		}
	} else {
		include_once $fileinc;
	}

	$groups_handler =& icms::handler('icms_member_group');

	$sql = "DELETE FROM ".$GLOBALS['xoopsDB']->prefix('groups'). " WHERE `group_type` IN ('"._XPY_MI_GROUP_TYPE_BROKER."','"._XPY_MI_GROUP_TYPE_ACCOUNTS."','"._XPY_MI_GROUP_TYPE_OFFICER."')";
	$GLOBALS['xoopsDB']->queryF($sql);
	
	return true;
	
}
	
?>