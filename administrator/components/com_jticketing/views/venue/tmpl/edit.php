<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
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
