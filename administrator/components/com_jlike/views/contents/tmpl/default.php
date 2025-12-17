<?php
/**
 * @version    SVN: <svn_id>
 * @package    School
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file

defined('_JEXEC') or die('Restricted access');

if (JVERSION < '4.0.0')
{
	echo $this->loadTemplate('bs2');
}
else
{
	echo $this->loadTemplate('bs5');
}
