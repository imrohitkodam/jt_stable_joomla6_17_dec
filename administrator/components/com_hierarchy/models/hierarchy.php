<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Methods supporting a list of Hierarchy records.
 *
 * @since  1.6
 */
class HierarchyModelHierarchy extends AdminModel
{
	/**
	 * @var string The prefix to use with controller messages.
	 *
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_HIERARCHY';

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'hierarchy', $prefix = 'HierarchyTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hierarchy/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_hierarchy.hierarchy', 'hierarchy', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_hierarchy.edit.hierarchy.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Get client and client_id from URL
		$app = Factory::getApplication();
		$input = $app->getInput();

		if (empty($item->context) && $input->get('client'))
		{
			$item->context = $input->get('client');
		}

		if (empty($item->context_id) && $input->get('client_id'))
		{
			$item->context_id = $input->get('client_id');
		}

		if ($item->user_id)
		{
			$hierarchyData = $this->getReportsTo($item->user_id);

			$item->reports_to = array();

			foreach ($hierarchyData as $hierarchy)
			{
				$item->reports_to[] = $hierarchy->reports_to;
			}
		}

		return $item;
	}

	/**
	 * Method to save an event data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		// Validate if user_id is not specified
		if (!$data['user_id'] || !$data['reports_to'])
		{
			$this->setError(Text::_('COM_HIERARCHY_INVALID_USER'));

			return false;
		}

		if (!isset($data['state']))
		{
			$data['state'] = 1;
		}

		// Check hierarchy is already exist or not.
		$hierarchyId = $this->checkIfHierarchyExist($data);

		$date = Factory::getDate();

		if ($hierarchyId)
		{
			$data['id'] = $hierarchyId;
			$data['modified_date'] = $date->toSql(true);
			$data['modified_by'] = $data['created_by'];
		}
		else
		{
			$data['id'] = '';
			$data['created_date'] = $date->toSql(true);
		}

		$isNew = empty($data['id']) ? true : false;

		// On before assigning manager
		PluginHelper::importPlugin("system");
		PluginHelper::importPlugin("actionlog");
		Factory::getApplication()->triggerEvent("onBeforeHierarchySaveHierarchy", array($data, $isNew));

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');

			// On after assigning manager
			PluginHelper::importPlugin("system");
			PluginHelper::importPlugin("actionlog");
			Factory::getApplication()->triggerEvent("onAfterHierarchySaveHierarchy", array($data, $isNew));

			return $id;
		}

		$this->setError(Text::_('COM_HIERARCHY_SAVE_FAIL'));

		return false;
	}

	/**
	 * Method to check hierarchy exist.
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed
	 *
	 * @since    1.6
	 */
	public function checkIfHierarchyExist($data)
	{
		$hierarchyTableObj = $this->getTable();

		if (!$data['context'])
		{
			$hierarchyTableObj->load(array('user_id' => (int) $data['user_id'], 'reports_to' => (int) $data['reports_to'], 'context' => null));
		}
		elseif ($data['context'])
		{
			$hierarchyTableObj->load(
			array(
			'user_id' => (int) $data['user_id'], 'reports_to' => (int) $data['reports_to'], 'context' => $data['context'], 'context_id' => $data['context_id'])
			);
		}

		return $hierarchyTableObj->id;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $id       user id whose sub users we need to get
	 * @param   boolean  $onlyIds  Fetch only users id
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getSubUsers($id, $onlyIds = false)
	{
		$user = Factory::getuser($id);

		if (!$user->id)
		{
			$this->setError(Text::_("COM_HIERARCHY_INVALID_USER"));

			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($onlyIds)
		{
			$query->select('distinct user_id');
		}
		else
		{
			$query->select('*');
		}

		$query->from($db->quoteName('#__hierarchy_users'));
		$query->where($db->quoteName('reports_to') . " = " . $db->quote($id));
		$db->setQuery($query);

		if ($onlyIds)
		{
			$result = $db->loadColumn();
		}
		else
		{
			$result = $db->loadObjectList();
		}

		if ($result)
		{
			return $result;
		}

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $id  user id whose managers we need to get
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getManagers($id)
	{
		$user = Factory::getuser($id);

		if (!$user->id)
		{
			$this->setError(Text::_("COM_HIERARCHY_INVALID_USER"));

			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__hierarchy_users'));
		$query->where($db->quoteName('user_id') . " = " . $db->quote($id));
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($result)
		{
			return $result;
		}

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $reportsTo  user id whose managers we need to get
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getReportsTo($reportsTo)
	{
		$user = Factory::getuser($reportsTo);

		if (!$user->id)
		{
			$this->setError(Text::_("COM_HIERARCHY_INVALID_USER"));

			return false;
		}

		// $db = Factory::getDbo();
		// $db = Factory::getContainer()->get(DatabaseInterface::class);
		$db = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('hu.id', 'user_id','reports_to','name','username','email')));
		$query->select($db->quoteName('hu.id') . ', ' . $db->quoteName('hu.user_id') . ', ' . $db->quoteName('hu.reports_to') . ', ' . $db->quoteName('u.name') . ', ' . $db->quoteName('u.username') . ', ' . $db->quoteName('u.email'));
		$query->from($db->quoteName('#__hierarchy_users', 'hu'));
		$query->join('INNER', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('hu.reports_to') . ')');
		$query->where($db->quoteName('hu.user_id') . " = " . $db->quote($reportsTo));

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to get users to manager hierarchy.
	 *
	 * @param   integer  $userId  userId
	 *
	 * @return  array
	 *
	 * @since    1.6
	 */
	public function getAutoSuggestUsers($userId)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();

		// Get search term
		$searchTerm = $input->get('search', '', 'STRING');

		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('u.id AS value, u.name AS text');
		$query->from('`#__users` AS u');
		$query->where('NOT u.id = ' . $userId);
		$query->where('u.block=0');

		// Search term
		if (!empty($searchTerm))
		{
			$search = $db->Quote('%' . $db->escape($searchTerm, true) . '%');
			$query->where('u.name LIKE ' . $search);
			$query->order('u.name ASC');
		}

		$db->setQuery($query);
		$allUsers = $db->loadObjectList();

		return $allUsers;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   array  &$pks  An array of primary key value to delete.
	 *
	 * @return  int  Returns count of success
	 */
	public function delete(&$pks)
	{
		$user = Factory::getUser();
		$db   = Factory::getDbo();

		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_hierarchy/tables');

		if (is_array($pks))
		{
			foreach ($pks as $id)
			{
				$hierarchyTable = Table::getInstance('Hierarchy', 'HierarchyTable', array('dbo', $db));
				$hierarchyTable->load(array('id' => $id));

				$data = $hierarchyTable->getProperties();

				// On before removing manager
				PluginHelper::importPlugin("system");
				PluginHelper::importPlugin("actionlog");
				Factory::getApplication()->triggerEvent("onBeforeHierarchyDeleteHierarchy", array($data));

				if ($hierarchyTable->delete($data['id']))
				{
					// On after removing manager
					PluginHelper::importPlugin("system");
					PluginHelper::importPlugin("actionlog");
					Factory::getApplication()->triggerEvent("onAfterHierarchyDeleteHierarchy", array($data));
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			$hierarchyTable = Table::getInstance('Hierarchy', 'HierarchyTable', array('dbo', $db));
			$hierarchyTable->load(array('id' => $pks));

			$data = $hierarchyTable->getProperties();

			// On before removing manager
			PluginHelper::importPlugin("system");
			PluginHelper::importPlugin("actionlog");
			Factory::getApplication()->triggerEvent("onBeforeHierarchyDeleteHierarchy", array($data));

			if ($hierarchyTable->delete($data['id']))
			{
				// On after removing manager
				PluginHelper::importPlugin("system");
				PluginHelper::importPlugin("actionlog");
				Factory::getApplication()->triggerEvent("onAfterHierarchyDeleteHierarchy", array($data));
			}
			else
			{
				return false;
			}
		}

		return true;
	}
}
