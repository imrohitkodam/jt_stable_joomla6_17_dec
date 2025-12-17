<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Hello Table class
 *
 * @since  0.0.1
 */
class ActivityStreamTableActivity extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \Joomla\Database\DatabaseInterface  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');
		parent::__construct('#__tj_activities', 'id', $db);
	}

	/**
	 * Function to check data
	 *
	 * @return  boolean
	 */
	public function check()
	{
		$jinput = Factory::getApplication()->input;
		$errors = array();

		// Activity type should not be empty
		if (empty($this->type))
		{
			$errors['type'] = Text::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_TYPE_REQUIRED');
		}

		// Actor and actor_id should not be empty
		if (empty($this->actor_id) || empty($this->actor))
		{
			$errors['actor'] = Text::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_ACTOR_REQUIRED');
		}

		// Object and object_id should not be empty
		if (empty($this->object_id) || empty($this->object))
		{
			$errors['object'] = Text::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_OBJECT_REQUIRED');
		}

		// If there is data in target then target_id should not be empty
		if (!empty($this->target))
		{
			if (empty($this->target_id))
			{
				$errors['target_id'] = Text::_('COM_ACTIVITYSTREAM_ACTIVITY_ERROR_TARGET_REQUIRED');
			}
		}

		$errors = isset($errors) && is_array($errors) ? $errors : [];

		if (count($errors))
		{
			$this->setError(implode(', ', $errors));

			return false;
		}

		return true;
	}
}
