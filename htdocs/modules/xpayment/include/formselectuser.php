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
class icms_form_elements_SelectGroupedUser extends icms_form_elements_Select
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
    function icms_form_elements_SelectGroupedUser($caption, $name, $value = null, $size = 1, $multiple = false, $group = 0)
    {
        $this->setCaption($caption);
        $this->setName($name);
        $this->_multiple = $multiple;
        $this->_size = intval($size);
        if (isset($value)) {
            $this->setValue($value);
        }
        
        $sql = "SELECT DISTINCT a.uid FROM ".$GLOBALS['xoopsDB']->prefix('groups_users_link').' a WHERE `groupid` = '.$group;
        $result = $GLOBALS['xoopsDB']->query($sql);
        $user_handler =& icms::handler('icms_member_user');
        
        while($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
        	$user =$user_handler->get($row['uid']);
        	if (is_object($user)) 
	        	if (strlen($user->getVar('name')))
	        		$this->addOption($user->getVar('uid'), $user->getVar('name').' ('.$user->getVar('uname').')');
	        	else 
	        		$this->addOption($user->getVar('uid'), $user->getVar('uname'));
        }
    }

}

?>