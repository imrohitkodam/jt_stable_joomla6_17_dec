<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\Data\DataObject;
use Joomla\CMS\Table\Table;

/**
 * Featured Table class.
 *
 * @since  1.6
 */
class JlikeTablePathNodeGraph extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DataObjectbaseDriver  &$db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jlike_pathnode_graph', 'pathnode_graph_id', $db);
	}
}
