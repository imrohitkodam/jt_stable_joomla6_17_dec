<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;


use Joomla\Utilities\ArrayHelper;

/**
 * Pip model.
 *
 * @since  1.6
 */
class JLikeModelPathDetail extends FormModel
{
	private $item = null;

	protected $app;

	protected $user;

	protected $db;
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->_params = ComponentHelper::getParams('com_jlike');
		$this->user = Factory::getUser();
		$this->db = Factory::getDbo();
		$this->app = Factory::getApplication();

		parent::__construct();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 *
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();
		
		// Load state from the request userState on edit or from the passed variable on default
		if ($app->getInput()->get('layout') == 'edit')
		{
			$id = $app->getUserState('com_jlike.edit.path.path_id');
		}
		else
		{
			$id = $app->getInput()->get('path_id');
			$app->setUserState('com_jlike.edit.path.path_id', $id);
		}

		$this->setState('path.path_id', $id);

		// Load the parameters.
		$params = $app->getParams();

		$this->setState('params', $params);
	}

	/**
	 * Get an instance of Table class
	 *
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'path', $prefix = 'JlikeTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.path', 'path', array('control' => 'jform', 'load_data' => $loadData));

		return $form = empty($form) ? false : $form;
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('path.path_id');
			}

			// Ruleset_module.id it used in mod_todo module and rulesets conteroller
			$pathModuleId = $this->getState('path_module.path_id');

			if (!empty($pathModuleId))
			{
				$id = $pathModuleId;
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Convert the JTable to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, CMSObject::class);

				$pathId = $this->item->path_id;
				$userId = $this->user->id;

				// Check & set access filter
				$levels = $this->user->getAuthorisedViewLevels();
				$this->item->set('access-view', in_array($this->item->access, $levels));

				// Load pathuser model
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jlike/models');
				$JLikePathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');

				// Attach path status to path details
				$pathUserDetail = $JLikePathUserModel->getPathUserDetails($pathId, $userId);
				$this->item->path_status = $pathUserDetail->status;

				// TODO : This code needs to be re-look. Remove this view if possible and check with paths model/view
				$query = $this->db->getQuery(true);
				$query->select('pu.path_user_id as pathuserid,pu.status as isSubscribedPath,pg.visibility');
				$query->select('p.path_id, p.path_title AS path_title, p.path_description AS path_description');
				$query->select('lft.path_id AS lft_path_id, lft.path_title AS lft_path_title, lft.path_description AS lft_path_description');
				$query->select('node.path_id AS node_path_id, node.path_title AS node_path_title, node.path_description AS node_path_description');
				$query->select('node.subscribe_start_date AS node_subscribe_start_date, node.subscribe_end_date AS node_subscribe_end_date');
				$query->select('rgt.path_id AS rgt_path_id, rgt.path_title AS rgt_path_title, rgt.path_description AS rgt_path_description');

				$query->from($this->db->quoteName('#__jlike_pathnode_graph', 'pg'));
				$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
				' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('pg.path_id') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jlike_paths', 'lft') .
				' ON (' . $this->db->quoteName('lft.path_id') . ' = ' . $this->db->quoteName('pg.lft') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jlike_paths', 'node') .
				' ON (' . $this->db->quoteName('node.path_id') . ' = ' . $this->db->quoteName('pg.node') . ')');
				$query->join('LEFT', $this->db->quoteName('#__jlike_path_user', 'pu') .
				' ON (' . $this->db->quoteName('pu.path_id') . ' = ' . $this->db->quoteName('node.path_id') . ')' .
				' AND (' . $this->db->quoteName('pu.user_id') . ' = ' . (int) $userId . ')');
				$query->join('LEFT', $this->db->quoteName('#__jlike_paths', 'rgt') .
				' ON (' . $this->db->quoteName('rgt.path_id') . ' = ' . $this->db->quoteName('pg.rgt') . ')');
				$query->where($this->db->quoteName('pg.path_id') . ' = ' . (int) $id);
				$query->where($this->db->quoteName('pg.isPath') . ' = ' . $this->db->quote(1));
				$query->where($this->db->quoteName('p.state') . ' = ' . $this->db->quote(1));
				$query->group($this->db->quoteName('pg.node'));
				$query->order($this->db->quoteName('node.order') . ' ASC');

				$this->db->setQuery($query);

				$this->item->info = $this->db->loadAssoclist();
			}
		}

		return $this->item;
	}
}
