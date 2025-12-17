<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jticketing
 * @author     Techjoomla <kiran_l@techjoomla.com>
 * @copyright  2016 techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
{
	echo $this->loadTemplate('bs2');
}
else
{
	echo $this->loadTemplate('bs5');
}