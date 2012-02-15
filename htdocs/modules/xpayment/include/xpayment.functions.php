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

	function xpayment_getFilterElement($filter, $field, $sort='created', $fct = 'invoice') {
		$components = xpayment_getFilterURLComponents($filter, $field, $sort);
		include_once('xpayment.objects.php');
		switch ($field) {
		    case 'mode':
				$ele = new icms_form_elements_SelectInvoiceMode('', 'filter_'.$field.'', $components['value'], 1, false, true);
		    	$ele->setExtra('onchange="window.open(\''.$_SERVER['PHP_SELF'].'?'.$components['extra'].'&filter='.$components['filter'].(!empty($components['filter'])?'|':'').$field.',\'+this.options[this.selectedIndex].value'.(!empty($components['operator'])?'+\','.$components['operator'].'\'':'').',\'_self\')"');
		    	break;
			case 'remittion':
				$ele = new icms_form_elements_SelectInvoiceRemittion('', 'filter_'.$field.'', $components['value'], 1, false, true);
		    	$ele->setExtra('onchange="window.open(\''.$_SERVER['PHP_SELF'].'?'.$components['extra'].'&filter='.$components['filter'].(!empty($components['filter'])?'|':'').$field.',\'+this.options[this.selectedIndex].value'.(!empty($components['operator'])?'+\','.$components['operator'].'\'':'').',\'_self\')"');
		    	break;
		    case 'invoicenumber':
		    case 'drawfor':
		    case 'drawto':
		    case 'drawto_email':
		    case 'tax':	
		    case 'shipping':
		    case 'handling':
		    case 'amount':
		    case 'grand':
			case 'items':
			case 'weight':
			case 'weight_unit':
			case 'tax':
			case 'currency':
			case 'items':
			case 'transactionid':
			case 'reoccurence':
			case 'reoccurences':
			case 'reoccurence_period_days':
			case 'occurence':
			case 'previous':
			case 'occurence_grand':
			case 'occurence_amount':
			case 'occurence_tax':
			case 'occurence_shipping':
			case 'occurence_handling':
			case 'occurence_weight':
			case 'donation':
			case 'comment':
			case 'user_ip':
			case 'user_netaddy':
			case 'transactionid':
			case 'email':
			case 'code':
			case 'redeems':
			case 'redeemed':
			case 'discount':
		    	$ele = new icms_form_elements_Tray('');
				$ele->addElement(new icms_form_elements_Text('', 'filter_'.$field.'', 3, 40, $components['value']));
				$button = new icms_form_elements_Button('', 'button_'.$field.'', '[+]');
		    	$button->setExtra('onclick="window.open(\''.$_SERVER['PHP_SELF'].'?'.$components['extra'].'&filter='.$components['filter'].(!empty($components['filter'])?'|':'').$field.',\'+$(\'#filter_'.$field.'\').val()'.(!empty($components['operator'])?'+\','.$components['operator'].'\'':'').',\'_self\')"');
		    	$ele->addElement($button);
		    	break;
		}
		return $ele;
	}

	function xpayment_getFilterURLComponents($filter, $field, $sort='created') {
		$parts = explode('|', $filter);
		$ret = array();
		$value = '';
    	foreach($parts as $part) {
    		$var = explode(',', $part);
    		if (count($var)>1) {
	    		if ($var[0]==$field) {
	    			$ele_value = $var[1];
	    			if (isset($var[2]))
	    				$operator = $var[2];
	    		} elseif ($var[0]!=1) {
	    			$ret[] = implode(',', $var);
	    		}
    		}
    	}
    	$pagenav = array();
    	$pagenav['op'] = isset($_REQUEST['op'])?$_REQUEST['op']:"shops";
		$pagenav['fct'] = isset($_REQUEST['fct'])?$_REQUEST['fct']:"list";
		$pagenav['limit'] = !empty($_REQUEST['limit'])?intval($_REQUEST['limit']):30;
		$pagenav['start'] = 0;
		$pagenav['order'] = !empty($_REQUEST['order'])?$_REQUEST['order']:'DESC';
		$pagenav['sort'] = !empty($_REQUEST['sort'])?''.$_REQUEST['sort'].'':$sort;
    	$retb = array();
		foreach($pagenav as $key=>$value) {
			$retb[] = "$key=$value";
		}
		return array('value'=>$ele_value, 'field'=>$field, 'operator'=>$operator, 'filter'=>implode('|', $ret), 'extra'=>implode('&', $retb));
	}
	
	function xpayment_install_gateway($class) {
		$gateways_handler =& icms_getModuleHandler('gateways', 'xpayment');
		$gateways_options_handler =& icms_getModuleHandler('gateways_options', 'xpayment');

		if ($gateways_handler->getCount(new icms_db_criteria_Item('class', $class))==0) {
			include(ICMS_ROOT_PATH.'/modules/xpayment/class/gateway/'.$class.'/gateway_info.php');
			if (!empty($GLOBALS['gateway'])) {
				$gateways = $gateways_handler->create();
				$gateways->setVars($GLOBALS['gateway']);
				if ($gid = $gateways_handler->insert($gateways, true, false)) {
					foreach($GLOBALS['gateway']['options'] as $refereer => $data) {
						$option = $gateways_options_handler->create();
						$option->setVars($data);
						$option->setVar('refereer', $refereer);
						$option->setVar('gid', $gid);
						$gateways_options_handler->insert($option, true, false);
					}
					return true;
				} else 
					return false;
			} else 
				return false;
		} else 
			return false;
	}

	function xpayment_update_gateway($class) {
		$gateways_handler =& icms_getModuleHandler('gateways', 'xpayment');
		$gateways_options_handler =& icms_getModuleHandler('gateways_options', 'xpayment');

		if ($gateways_handler->getCount(new icms_db_criteria_Item('class', $class))==1) {
			include(ICMS_ROOT_PATH.'/modules/xpayment/class/gateway/'.$class.'/gateway_info.php');
			
			if (!empty($GLOBALS['gateway'])) {
				$gatewaysObjs = $gateways_handler->getObjects(new icms_db_criteria_Item('class', $class));
				$gateways = $gatewaysObjs[0];
				$gateways->setVar('name', $GLOBALS['gateway']['name']);
				$gateways->setVar('testmode', $GLOBALS['gateway']['testmode']);
				$gateways->setVar('class', $class);
				$gateways->setVar('description', $GLOBALS['gateway']['description']);
				$gateways->setVar('author', $GLOBALS['gateway']['author']);
				$gateways->setVar('salt', $GLOBALS['gateway']['salt']);
				if ($gid = $gateways_handler->insert($gateways, true, false)) {
					foreach($GLOBALS['gateway']['options'] as $refereer => $data) {
						$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('gid', $gid));
						$criteria->add(new icms_db_criteria_Item('refereer', $refereer));
						if ($gateways_options_handler->getCount($criteria)==0) {
							$option = $gateways_options_handler->create();
							$option->setVar('refereer', $refereer);
							$option->setVar('name', $data['name']);
							$option->setVar('value', $data['value']);
							$option->setVar('gid', $gid);
							$gateways_options_handler->insert($option, true, false);
						} else {
							$optionObjs = $gateways_options_handler->getObjects($criteria);
							$option = $optionObjs[0]; 
							$option->setVar('name', $data['name']);
							$gateways_options_handler->insert($option, true, false);
						}
					}
					return true;
				} else 
					return false;
			} else 
				return false;
		} else 
			return false;
	}
	
	if (!function_exists("getIPData")) {
		function getIPData($ip=false){
			$ret = array();
			if (is_object(icms::$user)) {
				$ret['uid'] = icms::$user->getVar('uid');
				$ret['uname'] = icms::$user->getVar('uname');
			} else {
				$ret['uid'] = 0;
				$ret['uname'] = '';
			}
			if (!$ip) {
				if ($_SERVER["HTTP_X_FORWARDED_FOR"] != ""){ 
					$ip = (string)$_SERVER["HTTP_X_FORWARDED_FOR"]; 
					$ret['is_proxied'] = true;
					$proxy_ip = $_SERVER["REMOTE_ADDR"]; 
					$ret['network-addy'] = @gethostbyaddr($ip); 
					$ret['long'] = @ip2long($ip);
					if (is_ipv6($ip)) {
						$ret['ip6'] = $ip;
						$ret['proxy-ip6'] = $proxy_ip;
					} else {
						$ret['ip4'] = $ip;
						$ret['proxy-ip4'] = $proxy_ip;
					}
				}else{ 
					$ret['is_proxied'] = false;
					$ip = (string)$_SERVER["REMOTE_ADDR"]; 
					$ret['network-addy'] = @gethostbyaddr($ip); 
					$ret['long'] = @ip2long($ip);
					if (is_ipv6($ip)) {
						$ret['ip6'] = $ip;
					} else {
						$ret['ip4'] = $ip;
					}
				} 
			} else {
				$ret['is_proxied'] = false;
				$ret['network-addy'] = @gethostbyaddr($ip); 
				$ret['long'] = @ip2long($ip);
				if (is_ipv6($ip)) {
					$ret['ip6'] = $ip;
				} else {
					$ret['ip4'] = $ip;
				}
			}
			$ret['made'] = time();				
			return $ret;
		}
	}
	
	if (!function_exists("is_ipv6")) {
		function is_ipv6($ip = "") 
		{ 
			if ($ip == "") 
				return false;
				
			if (substr_count($ip,":") > 0){ 
				return true; 
			} else { 
				return false; 
			} 
		} 
	}
	
	if (!function_exists("xpayment_adminMenu")) {
	  function xpayment_adminMenu ($currentoption = 0, $page)  {
		   	echo "<table width=\"100%\" border='0'><tr><td>";
		   	echo "<tr><td>";
		   	$indexAdmin = new ModuleAdmin();
		   	echo $indexAdmin->addNavigation($page);
	  	   	echo "</td></tr>";
		   	echo "<tr'><td><div id='form'>";
	   }
	  
	  function xpayment_footer_adminMenu()
	  {
			echo "</div></td></tr>";
	  		echo "</table>";
			echo "<div align=\"center\"><a href=\"http://community.impresscms.org\" target=\"_blank\"><img src=" . ICMS_URL . '/modules/xpayment/' . $GLOBALS['xpaymentImageAdmin'].'/impresscms.png'.' '." alt='ImpressCMS' title='ImpressCMS'></a></div>";
			echo "<div style=\"text-align:center;\" class='center smallsmall italic pad5'><strong>" . $GLOBALS['xpaymentModule']->getVar("name") . "</strong> is maintained by the <a class='tooltip' rel='external' href='http://community.impresscms.org/' title='Visit ImpressCMS Community'>ImpressCMS Community</a> and <a class='tooltip' rel='external' href='http://www.chronolabs.coop/' title='Visit Chronolabs Co-op'>Chronolabs Co-op</a></div>";
	  }
	}
?>