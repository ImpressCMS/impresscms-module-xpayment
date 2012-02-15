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
	
	if (isset($_POST)&&!empty($_POST)) {
		
		if (isset($_POST['op'])&&$_POST['op']==='createinvoice') {
			
			
			$invoice_handler =& icms_getModuleHandler('invoice', 'xpayment');

			// Stops Duplication
			$userip = getIPData(false);
			if (isset($_POST['key'])) {
				$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('`plugin`', (!empty($_POST['plugin'])?$_POST['plugin']:'xpayment')));
				$criteria->add(new icms_db_criteria_Item('`key`', $_POST['key']));
				$criteria->add(new icms_db_criteria_Item('`mode`', 'UNPAID'));
				if ($userip['uid']>0) {
					$criteria->add(new icms_db_criteria_Item('`user_uid`', $userip['uid']));
				} else {
					$criteria->add(new icms_db_criteria_Item('`user_ip`', $userip['ip4'].$userip['ip6']));
					$criteria->add(new icms_db_criteria_Item('`user_netaddy`', $userip['network-addy']));
				}
				if ($invoice_handler->getCount($criteria)==1) {
					$invoices = $invoice_handler->getObjects($criteria, false);
					header( "HTTP/1.1 301 Moved Permanently" ); 
			       	header('Location: '.$invoices[0]->getURL());
			        exit;
				}
			}
			
			$invoice_items_handler =& icms_getModuleHandler('invoice_items', 'xpayment');
						
			$invoice = $invoice_handler->create();
			
			$invoice->setVar('return', $_POST['return']);
			$invoice->setVar('cancel', $_POST['cancel']);
			$invoice->setVar('ipn', $_POST['ipn']);
			$invoice->setVar('currency', (!empty($_POST['currency'])?strtoupper($_POST['currency']):$GLOBALS['xpaymentModuleConfig']['currency']));
			$invoice->setVar('drawfor', (!empty($_POST['drawfor'])?$_POST['drawfor']:$GLOBALS['icmsConfig']['sitename']));
			$invoice->setVar('invoicenumber', $_POST['invoicenumber']);
			$invoice->setVar('drawto', $_POST['drawto']);
			$invoice->setVar('drawto_email', $_POST['drawto_email']);
			$invoice->setVar('key', $_POST['key']);
			$invoice->setVar('plugin', (!empty($_POST['plugin'])?$_POST['plugin']:'xpayment'));
			$invoice->setVar('weight_unit', (!empty($_POST['weight_unit'])?strtolower($_POST['weight_unit']):$GLOBALS['xpaymentModuleConfig']['weightunit']));
			$invoice->setVar('mode', 'UNPAID');
			$invoice->setVar('reoccurrence', $_POST['reoccurrence']);
			$invoice->setVar('reoccurrence_period_days', (!empty($_POST['reoccurrence_period_days'])?$_POST['reoccurrence_period_days']:($GLOBALS['xpaymentModuleConfig']['period']/(60*60*24))));
			$invoice->setVar('donation', ((isset($_POST['donation'])||isset($_POST['donations']))?true:false));
			$invoice->setVar('comment', $_POST['comment']);
			
			
			$invoice->setVar('user_ip', $userip['ip4'].$userip['ip6']);
			$invoice->setVar('user_netaddy', $userip['network-addy']);
			$invoice->setVar('user_uid', $userip['uid']);
			
			
			
			if ($iid = $invoice_handler->insert($invoice)) {
				$invoice = $invoice_handler->get($iid);
				if (strlen($invoice->getVar('invoicenumber'))==0)
					$invoice->setVar('invoicenumber', $invoice->getVar('iid'));
				$amount=0;
				$shipping=0;
				$handling=0;
				$weight=0;
				$items=0;
				$tax=0;
				foreach($_POST['item'] as $id => $item) {
					if (!empty($item['cat'])&&!empty($item['name'])&&$item['quantity']>0) {
						$itemobj = $invoice_items_handler->create();
						$itemobj->setVar('iid', $invoice->getVar('iid'));		
						$itemobj->setVars($item);
						if ($iiid = $invoice_items_handler->insert($itemobj)) {
							$items=$items+$itemobj->getVar('quantity');
							$totals = $itemobj->getTotalsArray();
							$amount = $amount + $totals['amount'];
							$shipping = $shipping + $totals['shipping'];
							$handling = $handling + $totals['handling'];
							$weight = $weight + $totals['weight'];
							$tax = $tax + $totals['tax'];					
						}
					}
				}
				
				$invoice->setVar('items', $items);
				$invoice->setVar('shipping', $shipping);
				$invoice->setVar('handling', $handling);
				$invoice->setVar('weight', $weight);
				$invoice->setVar('tax', $tax);
				$invoice->setVar('amount', $amount);
				$grand = ($amount+$handling+$shipping+$tax);
				$invoice->setVar('grand', $grand);
				
				$groups_handler  =& icms_getModuleHandler('groups', 'xpayment');
				$invoice->setVar('broker_uids', $groups_handler->getUids('BROKERS', $invoice->getVar('plugin'), $grand));
				$invoice->setVar('accounts_uids', $groups_handler->getUids('ACCOUNTS', $invoice->getVar('plugin'), $grand));
				$invoice->setVar('officer_uids', $groups_handler->getUids('OFFICERS', $invoice->getVar('plugin'), $grand));
				
				$invoice = $invoice_handler->get($invoice_handler->insert($invoice, true));
				if (isset($_POST['code'])) {
					$invoice->applyDiscountCode($_POST['code']);
					$invoice = $invoice_handler->get($invoice_handler->insert($invoice, true));
				}

				$icmsMailer =& new icms_messaging_Handler();
				//$icmsMailer->setHTML(true);
				$icmsMailer->setTemplateDir((ICMS_ROOT_PATH.'/modules/xpayment/language/'.$GLOBALS['icmsConfig']['language'].'/mail_templates/'));
				$icmsMailer->setTemplate('xpayment_invoice_created.tpl');
				$icmsMailer->setSubject(sprintf(_XPY_EMAIL_CREATED_SUBJECT, $grand, $_POST['currency'], $_POST['drawto']));
				
				$icmsMailer->setToEmails($_POST['drawto_email']);
				
				$icmsMailer->assign("SITEURL", ICMS_URL);
				$icmsMailer->assign("SITENAME", $GLOBALS['icmsConfig']['sitename']);
				$icmsMailer->assign("INVOICENUMBER", $invoice->getVar('invoicenumber'));
				$icmsMailer->assign("CURRENCY", $invoice->getVar('currency'));
				$icmsMailer->assign("DRAWTO", $invoice->getVar('drawto'));
				$icmsMailer->assign("DRAWTO_EMAIL", $invoice->getVar('drawto_email'));
				$icmsMailer->assign("DRAWFOR", $invoice->getVar('drawfor'));	
				$icmsMailer->assign("AMOUNT", $invoice->getVar('grand'));
				$icmsMailer->assign("INVURL", $invoice->getURL());
				$icmsMailer->assign("PDFURL", $invoice->getPDFURL());
				
				if(!$icmsMailer->send() ){
					icms_error($icmsMailer->getErrors(true), 'Email Send Error');
				}
								
				header( "HTTP/1.1 301 Moved Permanently" ); 
		       	header('Location: '.$invoice->getURL());
		        exit;
				
			} else {
				include_once (ICMS_ROOT_PATH.'/header.php');
				icms_error($invoice->getHtmlErrors(), 'Invoice Creation Error');
				include_once (ICMS_ROOT_PATH.'/footer.php');
				exit(0);		
			}	
		} elseif (isset($_POST['op'])&&$_POST['op']==='discount') {
			$invoice_handler =& icms_getModuleHandler('invoice', 'xpayment');
			$key = $_POST['iid'];
			$criteria = new icms_db_criteria_Item('offline', time(), '>=');
			$criteria->setSort('iid');
			$criteria->setOrder('DESC');
			$count = $invoice_handler->getCount($criteria);
			$invoices = $invoice_handler->getObjects($criteria, true);
			foreach($invoices as $iid => $inv) {
				if ($key==md5($inv->getVar('iid').ICMS_LICENSE_KEY)) {
					$invoice = $inv;
				}
			}
			
			if (is_object($invoice)) {
				if ($invoice->applyDiscountCode($_POST['code'])) {
					if ($invoice_handler->insert($invoice)) {
						redirect_header($invoice->getURL(), 10, sprintf(_XPY_MF_DISCOUNT_APPLIED_SUCCESSFULLY, $invoice->getVar('discount'), $invoice->getVar('discount_amount'), $invoice->getVar('currency')));
					} else {
						redirect_header($invoice->getURL(), 10, _XPY_MF_DISCOUNT_APPLIED_UNSUCCESSFULLY);
					}
				} else {
					redirect_header($invoice->getURL(), 10, _XPY_MF_DISCOUNT_APPLIED_UNSUCCESSFULLY);
				}
			} else {
				redirect_header(ICMS_URL, 10, _NOPERM);
			}
			exit(0);
		} else {
			
			if ($GLOBALS['xpaymentModuleConfig']['htaccess']==true)
				$url = ICMS_URL.'/'.$GLOBALS['xpaymentModuleConfig']['baseurl'].'/index'.$GLOBALS['xpaymentModuleConfig']['endofurl'];
			else
				$url = ICMS_URL.'/modules/xpayment/index.php';
			
			if (!strpos($url, $_SERVER['REQUEST_URI'])&&$GLOBALS['xpaymentModuleConfig']['htaccess']==true) {
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header('Location: '.$url);
				exit(0);
			}
		
			if ($GLOBALS['xpaymentModuleConfig']['help']==true)
				$xoopsOption['template_main'] = 'xpayment_help.html';
			else 
				$xoopsOption['template_main'] = 'xpayment_invoice.html';
			include_once (ICMS_ROOT_PATH.'/header.php');
			$GLOBALS['xoopsTpl']->assign('icms_siteemail',  $GLOBALS['icmsConfig']['adminmail']);
			$GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
			$GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['xpaymentModuleConfig']);
			if (is_object(icms::$user))
				$GLOBALS['xoopsTpl']->assign('user', icms::$user->getValues());
			include_once (ICMS_ROOT_PATH.'/footer.php');
			exit(0);
		}
		
	} else {
		$invoice_handler =& icms_getModuleHandler('invoice', 'xpayment');
		$invoice_items_handler =& icms_getModuleHandler('invoice_items', 'xpayment');
	
		if (isset($_GET['iid'])&&$GLOBALS['xpaymentModuleConfig']['id_protect']==false) {
			$invoice =& $invoice_handler->get($_GET['iid']);
		} elseif (isset($_GET['invoicenum'])&&$GLOBALS['xpaymentModuleConfig']['id_protect']==false) {
			$invoice =& $invoice_handler->getInvoiceNumber($_GET['invoicenum']);
		} else {
			$key = (isset($_GET['iid'])?$_GET['iid']:0);
			$criteria = new icms_db_criteria_Item('offline', time(), '>=');
			$criteria->setSort('iid');
			$criteria->setOrder('DESC');
			$count = $invoice_handler->getCount($criteria);
			$invoices = $invoice_handler->getObjects($criteria, true);
			foreach($invoices as $iid => $inv) {
				if ($key==md5($inv->getVar('iid').ICMS_LICENSE_KEY)) {
					$invoice = $inv;
				}
			}
		}
		
		if (!isset($invoice)) {

			if ($GLOBALS['xpaymentModuleConfig']['htaccess']==true)
				$url = ICMS_URL.'/'.$GLOBALS['xpaymentModuleConfig']['baseurl'].'/index'.$GLOBALS['xpaymentModuleConfig']['endofurl'];
			else
				$url = ICMS_URL.'/modules/xpayment/index.php';
			
			if (!strpos($url, $_SERVER['REQUEST_URI'])&&$GLOBALS['xpaymentModuleConfig']['htaccess']==true) {
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header('Location: '.$url);
				exit(0);
			}
		
			if ($GLOBALS['xpaymentModuleConfig']['help']==true)
				$xoopsOption['template_main'] = 'xpayment_help.html';
			else 
				$xoopsOption['template_main'] = 'xpayment_invoice.html';
			include_once (ICMS_ROOT_PATH.'/header.php');
			$GLOBALS['xoopsTpl']->assign('icms_siteemail',  $GLOBALS['icmsConfig']['adminmail']);
			$GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
			$GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['xpaymentModuleConfig']);
			if (is_object(icms::$user))
				$GLOBALS['xoopsTpl']->assign('user', icms::$user->getValues());
			include_once (ICMS_ROOT_PATH.'/footer.php');
			exit(0);
		}
		
		if (!strpos($invoice->getURL(), $_SERVER['REQUEST_URI'])&&$GLOBALS['xpaymentModuleConfig']['htaccess']==true) {
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header('Location: '.$invoice->getURL());
				exit(0);
			}
		
		if ($invoice->getVar('offline')<time()) {
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header('Location: '.ICMS_URL.'/modules/xpayment/');
			exit(0);
		}
			
		$xoopsOption['template_main'] = 'xpayment_payment.html';
		include_once (ICMS_ROOT_PATH.'/header.php');
		

		$GLOBALS['xoopsTpl']->assign('invoice', $invoice->getValues());
		$GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['xpaymentModuleConfig']);
		
		if ($invoice->getVar('mode')=='UNPAID') {
			$GLOBALS['xoTheme']->addScript(ICMS_URL.'/browse.php?Frameworks/jquery/jquery.js');
			$GLOBALS['xoTheme']->addScript(ICMS_URL.'/modules/xpayment/js/jquery.json.gateway.js');
			$GLOBALS['xoTheme']->addScript( null, array( 'type' => 'text/javascript' ), 'function ChangeGateway(element) {
	var params = new Array();
	$.getJSON("'.ICMS_URL.'/modules/xpayment/dojsongateway.php?passkey=' . md5(ICMS_LICENSE_KEY.date('Ymdhi')) . '&gid=" + $(\'#\' + element).val() + "&iid='. $_GET['iid'] . '", params, refreshformdesc);
}' );
			include((ICMS_ROOT_PATH.'/modules/xpayment/include/formselectgateway.php'));
			$gatewaysel = new icms_form_elements_SelectGateway('', 'gid');
			$gatewaysel->setExtra('onchange="javascript:ChangeGateway(\'gid\');"');	
			$button = new icms_form_elements_Button('', 'submit', _SUBMIT);
			$button->setExtra('onclick="javascript:ChangeGateway(\'gid\');"');
			$GLOBALS['xoopsTpl']->assign('payment_markup', '<span>'.$gatewaysel->render().$button->render().'</span>');
			$GLOBALS['xoopsTpl']->assign('discount_form', xpayment_userdiscount($invoice));
		}
		
		$criteria = new icms_db_criteria_Item('iid', $invoice->getVar('iid'));
		$items = $invoice_items_handler->getObjects($criteria, true);
		foreach($items as $iiid => $item)
			$GLOBALS['xoopsTpl']->append('items',  $item->toArray(($invoice->getVar('did')!=0)));
			
		include_once (ICMS_ROOT_PATH.'/footer.php');
	}
	
?>