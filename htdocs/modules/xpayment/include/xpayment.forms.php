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
	function xpayment_adminpayment($invoice) {

		$sform = new icms_form_Theme(_XPY_AM_PAYMENT, 'payment', $_SERVER['PHP_SELF'], 'post');
		$formobj = array();	
		$eletray = array();
				
		$formobj['transactionid'] = new icms_form_elements_Text(_XPY_AM_TH_TRANSACTIONID, 'transactionid', 45, 128, '');
		$invoice_transactions_handler =& icms_getModuleHandler('invoice_transactions', 'xpayment');
		$gross = $invoice_transactions_handler->sumOfGross($invoice->getVar('iid'));
		$left = $invoice->getVar('grand')-$gross;
		if ($left<>0)
			$formobj['amount'] = new icms_form_elements_Text(_XPY_AM_TH_AMOUNT, 'amount', 15, 15, $left);
		else 
			return false;

		$eletray['buttons'] = new icms_form_elements_Tray('', '&nbsp;');
		$sformobj['buttons']['save'] = new icms_form_elements_Button('', 'submit', _SUBMIT, 'submit');
		$eletray['buttons']->addElement($sformobj['buttons']['save']);
		$formobj['buttons'] = $eletray['buttons'];
				
		$required = array('transactionid', 'amount');
		
		foreach($formobj as $id => $obj)			
			if (in_array($id, $required))
				$sform->addElement($formobj[$id], true);			
			else
				$sform->addElement($formobj[$id], false);

		$sform->addElement(new icms_form_elements_Hidden('iid', $invoice->getVar('iid')));		
		$sform->addElement(new icms_form_elements_Hidden('op', 'invoices'));	
		$sform->addElement(new icms_form_elements_Hidden('fct', 'transaction'));	
		
		return $sform->render();
		
	}

	function xpayment_adminsettle($invoice) {

		$sform = new icms_form_Theme(_XPY_AM_SETTLE, 'settle', $_SERVER['PHP_SELF'], 'post');
		$formobj = array();	
		$eletray = array();
				
		$invoice_transactions_handler =& icms_getModuleHandler('invoice_transactions', 'xpayment');
		$gross = $invoice_transactions_handler->sumOfGross($invoice->getVar('iid'));
		$left = $invoice->getVar('grand')-$gross;
		if ($left<>0)
			$formobj['settlement'] = new icms_form_elements_Text(_XPY_AM_TH_AMOUNT, 'settlement', 15, 15, $left);
		else 
			return false;

		$eletray['buttons'] = new icms_form_elements_Tray('', '&nbsp;');
		$sformobj['buttons']['save'] = new icms_form_elements_Button('', 'submit', _SUBMIT, 'submit');
		$eletray['buttons']->addElement($sformobj['buttons']['save']);
		$formobj['buttons'] = $eletray['buttons'];
				
		$required = array('settlement');
		
		foreach($formobj as $id => $obj)			
			if (in_array($id, $required))
				$sform->addElement($formobj[$id], true);			
			else
				$sform->addElement($formobj[$id], false);

		$sform->addElement(new icms_form_elements_Hidden('iid', $invoice->getVar('iid')));		
		$sform->addElement(new icms_form_elements_Hidden('op', 'invoices'));	
		$sform->addElement(new icms_form_elements_Hidden('fct', 'settle'));	
		
		return $sform->render();
		
	}
	
	function xpayment_adminrule($rid, $group_id) {

		$sform = new icms_form_Theme(_XPY_AM_ADDRULE, 'rule', $_SERVER['PHP_SELF'], 'post');
		$formobj = array();	
		$eletray = array();
		
		$groups_handler =& icms_getModuleHandler('groups', 'xpayment');
		if ($rid==0)
			$group = $groups_handler->create();
		else 
		 	$group = $groups_handler->get($rid);
				
		$formobj['plugin'] = new icms_form_elements_SelectPlugin(_XPY_AM_TH_PLUGIN, 'plugin', $group->getVar('plugin'));
		$formobj['uid'] = new icms_form_elements_SelectGroupedUser(_XPY_AM_TH_UID, 'uid', $group->getVar('uid'), 1, false, $group_id);
		$formobj['limit'] = new icms_form_elements_RadioYN(_XPY_AM_TH_LIMIT, 'limit', $group->getVar('limit'));
		$formobj['minimum'] = new icms_form_elements_Text(_XPY_AM_TH_MINIMUM, 'minimum', 15, 16, $group->getVar('minimum'));
		$formobj['maximum'] = new icms_form_elements_Text(_XPY_AM_TH_MAXIMUM, 'maximum', 15, 16, $group->getVar('maximum'));
		
		$eletray['buttons'] = new icms_form_elements_Tray('', '&nbsp;');
		$sformobj['buttons']['save'] = new icms_form_elements_Button('', 'submit', _SUBMIT, 'submit');
		$eletray['buttons']->addElement($sformobj['buttons']['save']);
		$formobj['buttons'] = $eletray['buttons'];
				
		$required = array('plugin', 'uid');
		
		foreach($formobj as $id => $obj)			
			if (in_array($id, $required))
				$sform->addElement($formobj[$id], true);			
			else
				$sform->addElement($formobj[$id], false);

		$sform->addElement(new icms_form_elements_Hidden('rid', $group->getVar('rid')));		
		$sform->addElement(new icms_form_elements_Hidden('op', 'groups'));	
		$sform->addElement(new icms_form_elements_Hidden('fct', 'save'));	
		$sform->addElement(new icms_form_elements_Hidden('action', $_REQUEST['fct']));
		
		return $sform->render();
		
		return '';
	}
	
	function xpayment_userdiscount($invoice) {

		$sform = new icms_form_Theme(_XPY_MF_DISCOUNT, 'discount', $_SERVER['PHP_SELF'], 'post');
		$formobj = array();	
		$eletray = array();
		
		if ($invoice->getVar('did')>0) {
			$formobj['discount'] = new icms_form_elements_Label(_XPY_MF_DISCOUNT_CODE, str_replace('%amount', $invoice->getVar('discount_amount') . ' ' . $invoice->getVar('currency'), str_replace('%discount', $invoice->getVar('discount'), _XPY_MF_DISCOUNT_CODE_APPLIED)));
		} else {
			$formobj['code'] = new icms_form_elements_Text(_XPY_MF_DISCOUNT_CODE, 'code', 35, 48, '');
			$eletray['buttons'] = new icms_form_elements_Tray('', '&nbsp;');
			$sformobj['buttons']['save'] = new icms_form_elements_Button('', 'submit', _SUBMIT, 'submit');
			$eletray['buttons']->addElement($sformobj['buttons']['save']);
			$formobj['buttons'] = $eletray['buttons'];
		}
		
		$required = array('code');
		
		foreach($formobj as $id => $obj)			
			if (in_array($id, $required))
				$sform->addElement($formobj[$id], true);			
			else
				$sform->addElement($formobj[$id], false);

		$sform->addElement(new icms_form_elements_Hidden('iid', md5($invoice->getVar('iid').ICMS_LICENSE_KEY)));		
		$sform->addElement(new icms_form_elements_Hidden('op', 'discount'));	
		$sform->addElement(new icms_form_elements_Hidden('fct', 'apply'));	
		
		return $sform->render();
		
	}
	
	function xpayment_admincreatediscounts() {
		$sform = new icms_form_Theme(_XPY_AM_CREATE_DISCOUNT_CODES, 'create_discount', $_SERVER['PHP_SELF'], 'post');
		$formobj = array();	
		$eletray = array();
		
		$formobj['prefix'] = new icms_form_elements_Text(_XPY_AM_PREFIX_DISCOUNT_CODE, 'prefix', 15, 25, $GLOBALS['xpaymentModuleConfig']['discount_prefix']);
		$formobj['discount'] = new icms_form_elements_Text(_XPY_AM_AMOUNT_DISCOUNT_CODE, 'discount', 15, 25, $GLOBALS['xpaymentModuleConfig']['discount_percentage']);
		$formobj['redeems'] = new icms_form_elements_Text(_XPY_AM_REDEEMS_DISCOUNT_CODE, 'redeems', 15, 25, $GLOBALS['xpaymentModuleConfig']['discount_redeems']);
		$formobj['validtill'] = new icms_form_elements_Tray(_XPY_AM_VALIDTILL_DISCOUNT_CODE, '<br/>');
		$formobj['validtill']->addElement(new icms_form_elements_Datetime('', 'validtill', 15, time() + $GLOBALS['xpaymentModuleConfig']['discount_validtill']));
		$formobj['validtill']->addElement(new icms_form_elements_RadioYN(_XPY_AM_VALIDTILL_NEVERTIMEOUT_DISCOUNT_CODE, 'validtill_infinte', false));
		$formobj['emails'] = new icms_form_elements_TextArea(_XPY_AM_EMAILS_DISCOUNT_CODE, 'emails', '');
		$formobj['emails']->setDescription(_XPY_AM_EMAILS_DISCOUNT_CODE_DESC);
		$formobj['scan'] = new icms_form_elements_RadioYN(_XPY_AM_SCAN_DISCOUNT_CODE, 'scan', false);
		$formobj['groups'] = new icms_form_elements_Tray(_XPY_AM_GROUPS_DISCOUNT_CODE, '&nbsp;');
		//$formobj['groups']->addElement(new icms_form_elements_RadioYN('', 'groups', false));
		$formobj['groups']->addElement(new icms_form_elements_select_Group('', 'groups[]', false, ARRAY(ICMS_GROUP_USERS), 6, true));
		$formobj['since'] = new icms_form_elements_Tray(_XPY_AM_SINCE_DISCOUNT_CODE, '&nbsp;');
		$formobj['since']->addElement(new icms_form_elements_RadioYN('', 'since', false));
		$formobj['since']->addElement(new icms_form_elements_Datetime('', 'since_datetime', 15, time()));
		$formobj['logon'] = new icms_form_elements_Tray(_XPY_AM_LOGON_DISCOUNT_CODE, '&nbsp;');
		$formobj['logon']->addElement(new icms_form_elements_RadioYN('', 'logon', false));
		$formobj['logon']->addElement(new icms_form_elements_Datetime('', 'logon_datetime', 15, time()));
				
		$eletray['buttons'] = new icms_form_elements_Tray('', '&nbsp;');
		$sformobj['buttons']['save'] = new icms_form_elements_Button('', 'submit', _SUBMIT, 'submit');
		$eletray['buttons']->addElement($sformobj['buttons']['save']);
		$formobj['buttons'] = $eletray['buttons'];
	
		$required = array('discount', 'redeems', 'prefix');
		
		foreach($formobj as $id => $obj)			
			if (in_array($id, $required))
				$sform->addElement($formobj[$id], true);			
			else
				$sform->addElement($formobj[$id], false);
		
		$limit = !empty($_REQUEST['limit'])?intval($_REQUEST['limit']):30;
		$start = !empty($_REQUEST['start'])?intval($_REQUEST['start']):0;
		$order = !empty($_REQUEST['order'])?$_REQUEST['order']:'DESC';
		$sort = !empty($_REQUEST['sort'])?$_REQUEST['sort']:'created';
		$filter = !empty($_REQUEST['filter'])?$_REQUEST['filter']:'1,1';
		$sform->addElement(new icms_form_elements_Hidden('limit', $limit));
		$sform->addElement(new icms_form_elements_Hidden('start', $start));
		$sform->addElement(new icms_form_elements_Hidden('order', $order));
		$sform->addElement(new icms_form_elements_Hidden('sort', $sort));
		$sform->addElement(new icms_form_elements_Hidden('filter', $filter));
		$sform->addElement(new icms_form_elements_Hidden('op', 'discounts'));	
		$sform->addElement(new icms_form_elements_Hidden('fct', 'create'));	
		
		return $sform->render();
				
	}
?>