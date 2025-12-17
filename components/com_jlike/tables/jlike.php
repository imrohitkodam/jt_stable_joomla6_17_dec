<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die ( 'Restricted access' );
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of Jlike records.
 *
 * @since  1.5
 */

class TableJLike extends Table
{
	public $id = null;

	/**
	 * Constructor.
	 *
	 * @param   array  &$db  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike', 'id', $db);
	}
}
