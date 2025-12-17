<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// no direct access
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
