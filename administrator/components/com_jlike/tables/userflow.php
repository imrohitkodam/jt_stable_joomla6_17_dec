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

use Joomla\Utilities\ArrayHelper;
/**
 * applicant Table class
 *
 * @since  1.6
 */
class JlikeTableuserflow extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DataObjectbase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike_flow_user', 'id', $db);
	}
}
