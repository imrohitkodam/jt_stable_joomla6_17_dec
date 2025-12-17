<?php
/**
 * @version     1.2
 * @package     com_jlike
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

if (JVERSION < '4.0.0')
{
	echo $this->loadTemplate('bs2');
}
else
{
	echo $this->loadTemplate('bs5');
}
