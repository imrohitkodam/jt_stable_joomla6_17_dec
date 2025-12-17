<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/**
 * hierarchy Table class
 * 
 * @since  1.0.4
 */
class HierarchyTableHierarchy extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseInterface  $db  A database connector object
	 */
	public function __construct(DatabaseInterface $db)
	{
		parent::__construct('#__hierarchy_users', 'id', $db);
	}
}
