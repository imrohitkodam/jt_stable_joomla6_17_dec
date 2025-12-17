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
use Joomla\CMS\Language\Text;


use Joomla\Utilities\ArrayHelper;

/**
 * JLike Path Form model.
 *
 * @since  1.6
 */
class JLikeModelPath extends FormModel
{
	private $item = null;

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
		parent::__construct();
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
	public function getTable($type = 'Path', $prefix = 'JlikeTable', $config = array())
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
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array|boolean  The default data is an empty array.
	 *
	 * @since   12.2
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jlike.edit.path.data', array());

		return $data = empty($data) ? $this->getData() : $data;
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
				$id = $this->getState('path.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, CMSObject::class);
			}
		}

		return $this->item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		$table = $this->getTable();

		if ($table->save($data))
		{
			return $table->path_id;
		}

		$this->setError(Text::_('COM_JLIKE_PATH_ERROR_MSG_SAVE'));

		return false;
	}

	/**
	 * Method to check the path is path or content.
	 *
	 * @param   INT  $pathId  pathId
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function hasPaths($pathId)
	{
		if (!$pathId)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select all records from the todo rules table where rule_set_id is exactly equal to path_id .
		$query->select('npr.isPath');
		$query->from($this->db->quoteName('#__jlike_pathnode_graph', 'npr'));
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
		' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('npr.path_id') . ')');
		$query->where($this->db->quoteName('p.path_id') . ' = ' . (int) $pathId);

		// Reset the query using our newly populated query object.
		$this->db->setQuery($query);

		return $result = $this->db->loadResult();
	}

	/**
	 * Method to check the path is path of paths or todos.
	 *
	 * @param   INT  $pathId  path id
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function isPathOfPaths($pathId)
	{
		if (!$pathId)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select all records from the todo rules table where rule_set_id is exactly equal to path_id .
		$query->select('npr.*');
		$query->from($this->db->quoteName('#__jlike_pathnode_graph', 'npr'));
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
		' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('npr.path_id') . ')');
		$query->where($this->db->quoteName('npr.path_id') . ' = ' . (int) $pathId);
		$query->where($this->db->quoteName('npr.lft') . ' = ' . $this->db->quote("0"));

		// Reset the query using our newly populated query object.
		$this->db->setQuery($query);

		$result = $this->db->loadAssoc();

		if (!empty($result))
		{
			if ($result['isPath'] == 1)
			{
				return true;
			}
		}
	}

	/**
	 * Method to get next path
	 *
	 * @param   INT  $pathId  path id
	 *
	 * @return Result|boolean Result on success, false on failure
	 *
	 * @throws Exception
	 */
	public function nextPath($pathId)
	{
		if (!$pathId)
		{
			$this->setError(Text::_('COM_JLIKE_INVALID_PATH_ID'));

			return false;
		}

		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select all records from the todo rules table where rule_set_id is exactly equal to path_id .
		$query->select('npr.rgt');
		$query->from($this->db->quoteName('#__jlike_pathnode_graph', 'npr'));
		$query->join('INNER', $this->db->quoteName('#__jlike_paths', 'p') .
		' ON (' . $this->db->quoteName('p.path_id') . ' = ' . $this->db->quoteName('npr.path_id') . ')');
		$query->where($this->db->quoteName('npr.node') . ' = ' . (int) $pathId);
		$query->where($this->db->quoteName('npr.isPath') . ' = ' . $this->db->quote("1"));

		// Reset the query using our newly populated query object.
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Function used check if Path's subscription dates are open for subscription.
	 *
	 * @return  Object
	 *
	 * @since  1.0.0
	 */
	public function isPathOpenForSubscription()
	{
		$returnObj = new stdClass;
		$returnObj->allowedToSubscribe = false;
		$nullDate = $subStartDate = $subEndDate = Factory::getDbo()->getNullDate();
		$nowDate = Factory::getDate()->toUnix();

		if (is_object($this->item))
		{
			$subStartDate = $this->item->subscribe_start_date;
			$subEndDate = $this->item->subscribe_end_date;
		}

		if ($subStartDate === null || empty($subStartDate))
		{
			$subStartDate = $nullDate;
		}

		if ($subEndDate === null || empty($subEndDate))
		{
			$subEndDate = $nullDate;
		}

		// Check if path is open
		if (($nullDate == $subStartDate	|| Factory::getDate($subStartDate, 'UTC')->toUnix() <= $nowDate)
			&& ($nullDate == $subEndDate || Factory::getDate($subEndDate, 'UTC')->toUnix() >= $nowDate))
		{
			$returnObj->allowedToSubscribe = true;
		}
		// Check if path is closed
		elseif (($nullDate != $subStartDate	&& Factory::getDate($subStartDate, 'UTC')->toUnix() < $nowDate)
			&& ($nullDate != $subEndDate && Factory::getDate($subEndDate, 'UTC')->toUnix() < $nowDate))
		{
			$returnObj->pathClosed = true;
		}
		// Check if path opening soon
		elseif (($nullDate != $subStartDate	&& Factory::getDate($subStartDate, 'UTC')->toUnix() > $nowDate)
			&& ($nullDate == $subEndDate || Factory::getDate($subEndDate, 'UTC')->toUnix() > $nowDate))
		{
			$returnObj->pathOpeningSoon = true;
		}

		return $returnObj;
	}
}
