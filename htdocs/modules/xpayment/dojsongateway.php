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
function profile_checkpasskey($key) {

	$minseed = strtotime(date('Y-m-d h:i'));
	$diff = intval((120/2)*60);
	for($step=($minseed-$diff);$step<($minseed+$diff);$step++)
		if ($key==md5(ICMS_LICENSE_KEY.date('Ymdhi', $step)))
			return true;
	return false;

}

include(dirname(__FILE__).'/header.php');
$GLOBALS['icmsLogger']->activated = false;

icms_load('XoopsUserUtility');
$myts =& icms_core_Textsanitizer::getInstance();

foreach($_GET as $id => $val)
	${$id} = $val;

if (!function_exists('json_encode')) {
	include (ICMS_ROOT_PATH.'/modules/xpayment/include/JSON.php');
	$json = new services_JSON();
}
set_time_limit(120);


if (!profile_checkpasskey($passkey)) { 
	$values['innerhtml']['gateway_html'] = _XPY_VALIDATE_PASSKEYFAILED;
	if (!function_exists('json_encode'))
		print $json->encode($values);
	else
		print json_encode($values);
	exit(0);
}

$invoice_handler = icms_getModuleHandler('invoice', 'xpayment');
$gateways_handler = icms_getModuleHandler('gateways', 'xpayment');
if (isset($iid)&&$GLOBALS['xpaymentModuleConfig']['id_protect']==false) {
	$invoice =& $invoice_handler->get($iid);
} else {
	$criteria = new icms_db_criteria_Item('offline', time(), '>=');
	$criteria->setSort('iid');
	$criteria->setOrder('DESC');
	$count = $invoice_handler->getCount($criteria);
	$invoices = $invoice_handler->getObjects($criteria, true);
	foreach($invoices as $iiid => $inv) {
		if ($iid==md5($inv->getVar('iid').ICMS_LICENSE_KEY)) {
			$invoice = $inv;
		}
	}
}

$gateway = $gateways_handler->get($gid);
$gateway->loadGateway($invoice);
$invoice->setGateway($gateway);
$values['innerhtml']['gateway_html'] = $invoice->getPaymentHtml(); 
if (!function_exists('json_encode'))
	print $json->encode($values);
else
	print json_encode($values);
?>