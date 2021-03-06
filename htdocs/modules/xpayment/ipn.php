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
	include('header.php');
	
	$gateways_handler = icms_getModuleHandler('gateways', 'xpayment');
	$gateways = $gateways_handler->getObjects(NULL, true);
	foreach($gateways as $gid => $gateway) {
		include_once((ICMS_ROOT_PATH.'/modules/xpayment/class/gateway/'.$gateway->getVar('class').'/'.$gateway->getVar('class').'.php'));
		$class = ucfirst($gateway->getVar('class')).'GatewaysPlugin';
	
		if (class_exists($class))
			$invoice = $class::goInvoiceObj();
	
		if (is_a($invoice, 'XpaymentInvoice')&&$invoice->getVar('gateway')==$gateway->getVar('class')) {
			$gateways_handler =& icms_getModuleHandler('gateways','xpayment');
			$agateway = $gateways_handler->getGateway($invoice->getVar('gateway'), $invoice);
			
			if (is_a($agateway, 'XpaymentGateways')) {
				$agateway->goIPN($_REQUEST);
				exit(0);
			}
		}
	}
		
		
?>