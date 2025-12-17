<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\Table\Table;

/**
 * reminder Table class
 *
 * @since  1.6
 */
class JlikeTableRemindersent extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DataObjectbaseDriver  &$db  Database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike_reminder_sent', 'id', $db);
	}
}
