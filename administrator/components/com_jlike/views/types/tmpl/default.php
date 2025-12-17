<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_jlike
 * @author     Sudhir Sapkal <contact@techjoomla.com>
 * @copyright  2016 Sudhir Sapkal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

if (JVERSION < '4.0.0')
{
	echo $this->loadTemplate('bs2');
}
else
{
	echo $this->loadTemplate('bs5');
}
