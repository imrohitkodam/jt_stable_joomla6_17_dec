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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_SITE . '/components/com_jlike/models/pathuser.php')) {
	require_once JPATH_SITE . '/components/com_jlike/models/pathuser.php';
}

use Joomla\Utilities\ArrayHelper;
/**
 * Path User Table class
 *
 * @since  1.6
 */
class JlikeTablePathUser extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DataObjectbase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike_path_user', 'path_user_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (empty($this->path_user_id))
		{
			$pathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');
			$pathUserDetail = $pathUserModel->getPathUserDetails($this->path_id, $this->user_id);

			if (!empty($pathUserDetail))
			{
				return false;
			}
		}

		return parent::check();
	}
}
