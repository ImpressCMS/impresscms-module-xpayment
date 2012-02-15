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

defined('ICMS_ROOT_PATH') or die('Restricted access');

/**
 * A select field
 *
 * @author 		Kazumi Ono <onokazu@icms.org>
 * @author 		Taiwen Jiang <phppp@users.sourceforge.net>
 * @author 		John Neill <catzwolf@icms.org>
 * @copyright   The ICMS Project http://sourceforge.net/projects/icms/
 * @package 	kernel
 * @subpackage 	form
 * @access 		public
 */
class icms_form_elements_SelectGateway extends icms_form_elements_Select
{
    /**
     * Constructor
     *
     * @param string $caption Caption
     * @param string $name "name" attribute
     * @param mixed $value Pre-selected value (or array of them).
     * @param int $size Number or rows. "1" makes a drop-down-list
     * @param bool $multiple Allow multiple selections?
     */
    function icms_form_elements_SelectGateway($caption, $name, $value = null, $size = 1, $multiple = false)
    {
        if (is_object(icms::$user))
        	$groups = icms::$user->getGroups();
        else
        	$groups = array(ICMS_GROUP_ANONYMOUS=>ICMS_GROUP_ANONYMOUS);
        	
        $groupperm_handler =& icms::handler('icms_member_groupperm');
        $module_handler =& icms::handler('icms_module');
        $config_handler =& icms::handler('icms_config');
    	$gateways_handler =& icms_getModuleHandler('gateways', 'xpayment');
    	$GLOBALS['xpaymentModule'] = $module_handler->getByDirname('xpayment');
    	$GLOBALS['xpaymentModuleConfig'] = $config_handler->getConfigList($GLOBALS['xpaymentModule']->getVar('mid'));
    	
    	$this->setCaption($caption);
        $this->setName($name);
        $this->_multiple = $multiple;
        $this->_size = intval($size);
        if (isset($value)) {
            $this->setValue($value);
        } else {
       		$criteria = new icms_db_criteria_Item('class', $GLOBALS['xpaymentModuleConfig']['gateway']);
    		$gateways = $gateways_handler->getObjects($criteria);
    		if (is_object($gateways[0])) {
    			if ($groupperm_handler->checkRight('gateway', $gateways[0]->getVar('gid'), $groups, $GLOBALS['xpaymentModule']->getVar('mid'))) {
					$language = $GLOBALS['icmsConfig']['language'];
					$file = $gateways[0]->getVar('class');
					if ( !file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/{$language}/{$file}.php" )){
						if ( file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/english/{$file}.php" )){
							include_once $fileinc;
						}
					} else {
						include_once $fileinc;
					}
    				$this->setValue($gateways[0]->getVar('gid'));
    			}
    		}
        }
        
        if (is_object(icms::$user))
        	$groups = icms::$user->getGroups();
        else
        	$groups = array(ICMS_GROUP_ANONYMOUS=>ICMS_GROUP_ANONYMOUS);
        	
        $gids = $groupperm_handler->getItemIds('gateway', $groups, $GLOBALS['xpaymentModule']->getVar('mid'));
		$gateways = $gateways_handler->getObjects(new icms_db_criteria_Item('gid', '('.implode(',', $gids).')', 'IN'), true);
		foreach($gateways as $gid => $gateway) {
			$language = $GLOBALS['icmsConfig']['language'];
			$file = $gateway->getVar('class');
			if ( !file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/{$language}/{$file}.php" )){
				if ( file_exists($fileinc = ICMS_ROOT_PATH . "/modules/xpayment/language/english/{$file}.php" )){
					include_once $fileinc;
				}
			} else {
				include_once $fileinc;
			}
			$this->addOption($gid, (defined($gateway->getVar('name'))?constant($gateway->getVar('name')):$gateway->getVar('name')));  
		} 		
    }

}

?>