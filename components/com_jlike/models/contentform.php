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

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;

/**
 * Jlike model.
 *
 * @since  1.6
 */
class JlikeModelContentForm extends FormModel
{
	private $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request userState on edit or from the passed variable on default
		if ($app->getInput()->get('layout') == 'edit')
		{
			$id = $app->getUserState('com_jlike.edit.content.id');
		}
		else
		{
			$id = $app->getInput()->get('id');
			$app->setUserState('com_jlike.edit.content.id', $id);
		}

		$this->setState('content.id', $id);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_jlike');
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('content.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function &getData($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('content.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id))
			{
				$user = Factory::getUser();
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_jlike') || $user->authorise('core.create', 'com_jlike');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_jlike'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, CMSObject::class);
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the Table class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for Table object
	 *
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Content', $prefix = 'JlikeTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('content.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('content.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    false   A Form object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.content', 'contentform', array(
			'control'   => 'jform',
			'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jlike.edit.content.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
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
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('content.id');
		$state = (!empty($data['state'])) ? 1 : 0;

		/**
		$user  = Factory::getUser();

		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_jlike') || $authorised = $user->authorise('core.edit.own', 'com_jlike');
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_jlike');
		}

		if ($authorised !== true)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		* */

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			return $table->id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  $data  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('content.id');

		if (Factory::getUser()->authorise('core.delete', 'com_jlike') !== true)
		{
			throw new Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$table = $this->getTable();

		if ($table->delete($data['id']) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

	/**
	 * Get the Content Entry Id - (DEPRECATED use getContentID)
	 *
	 * @param   Array  $data  Contain element, cont_id, url, title etc
	 *
	 * @return  Jlike Content table entry Id
	 */
	public static function getConentId($data)
	{
		Log::add('getConentId is deprecated. Use getContentID instead.', Log::WARNING, 'deprecated');

		extract($data);

		$db = Factory::getDBO();

		$content_Id = null;

		if (!empty ($element) && !empty ($url) && !empty($element_id))
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->clear();

			$query->select($db->quoteName(array("jc.id")));
			$query->from($db->quoteName("#__jlike_content", "jc"));

			$conditions = array(
				$db->quoteName('jc.element') . ' = ' . $db->quote($element),
				$db->quoteName('jc.url') . ' = ' . $db->quote($url),
				$db->quoteName("jc.element_id") . " = " . $db->quote($element_id),
			);

			$query->where($conditions);
			$db->setQuery($query);

			$content_Id = $db->loadResult();
		}

		// Add entry in content and type table
		if (!$content_Id)
		{
			// Add the content entry
			$model = BaseDatabaseModel::getInstance('contentform', 'JlikeModel');

			// To add new entry of content we have set this id to 0
			$data['id'] = 0;
			$content_Id = $model->save($data);
		}

		// Save the type
		self::setType($data);

		return $content_Id;
	}

	/**
	 * Updated function to get the Content Id
	 *
	 * @param   Array  $data  Contain element, cont_id, url, title etc
	 *
	 * @return  Jlike Content table entry Id
	 */
	public static function getContentID($data)
	{
		$contentId = null;

		$jlikeContentFormModel = BaseDatabaseModel::getInstance('ContentForm', 'JlikeModel');
		$table = $jlikeContentFormModel->getTable();

		if (!empty($data['element']) && !empty($data['element_id']))
		{
			$table->load(array('element' => $data['element'], 'element_id' => (int) $data['element_id']));

			$contentId = $table->id;
		}
		elseif (!empty($data['url']))
		{
			$table->load(array('url' => $data['url']));

			$contentId = $table->id;
		}

		// Add entry in content and type table
		if (!$contentId)
		{
			// Add the content entry
			$model = BaseDatabaseModel::getInstance('contentform', 'JlikeModel');

			// To add new entry of content we have set this id to 0
			$data['id'] = 0;
			$contentId = $model->save($data);
		}

		// Save the type
		self::setType($data);

		return $contentId;
	}

	/**
	 * Get Type Id
	 *
	 * @param   Array  $data  Contain element, cont_id, url, title etc
	 *
	 * @since 1.2
	 *
	 * @return  boolean
	 */
	public static function setType($data)
	{
		extract($data);

		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$type_Id = null;

		try
		{
			if (!empty($type) && !empty($subtype) && !empty($element))
			{
				// Add entry in type table if type is not exist
				$query = $db->getQuery(true);

				$query->select($db->quoteName(array("tp.id")));
				$query->from($db->quoteName("#__jlike_types", "tp"));

				$conditions = array(
					$db->quoteName('tp.type') . ' = ' . $db->quote($type),
					$db->quoteName('tp.subtype') . ' = ' . $db->quote($subtype),
					$db->quoteName("tp.client") . " = " . $db->quote($element),
				);

				$query->where($conditions);
				$db->setQuery($query);

				$type_Id = $db->loadResult();
			}

			if (!$type_Id)
			{
				$typeObj = new stdClass;

				if (isset($type))
				{
					$typeObj->type = $db->quote($type);
				}

				if (isset($subtype))
				{
					$typeObj->subtype  = $db->quote($subtype);
				}

				if (isset($element))
				{
					$typeObj->client   = $db->quote($element);
				}

				$db->insertObject('#__jlike_types', $typeObj);
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');

			throw new Exception($db->getErrorMsg());
		}
	}
}
