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
use Joomla\CMS\Language\Text;


require_once JPATH_SITE . '/components/com_jlike/models/content.php';

use Joomla\Utilities\ArrayHelper;
/**
 * Jlike model.
 *
 * @since  1.6
 */
class JlikeModelLikeForm extends FormModel
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
		$app = Factory::getApplication('com_jlike');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->getInput()->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_jlike.edit.like.id');
		}
		else
		{
			$id = Factory::getApplication()->getInput()->get('id');
			Factory::getApplication()->setUserState('com_jlike.edit.like.id', $id);
		}

		$this->setState('like.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('like.id', $params_array['item_id']);
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
				$id = $this->getState('like.id');
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
	 * Get is like
	 *
	 * @return boolean
	 *
	 * @since 1.2.2
	 */
	public function isLiked()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$content_id    = $this->getState("content_id", '');
		$userid        = $this->getState("userid", '');

		try
		{
			$query = $db->getQuery(true);

			// Query to get data from stats table
			$query->select($db->quoteName('like'));
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('userid') . ' = ' . $db->q($userid));
			$query->where($db->qn('content_id') . ' = ' . $db->q($content_id));
			$query->where($db->qn('like') . ' = 1');

			$db->setQuery($query);

			$count = $db->loadObjectList();

			if ($count)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Get Is Dislike
	 *
	 * @return  boolean
	 *
	 * @since 1.2.2
	 */
	public function isDisLiked()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$content_id    = $this->getState("content_id", '');
		$userid        = $this->getState("userid", '');

		try
		{
			$query = $db->getQuery(true);

			$query->select($db->qn('dislike'));
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('userid') . ' = ' . $db->q($userid));
			$query->where($db->qn('content_id') . ' = ' . $db->q($content_id));
			$query->where($db->qn('dislike') . ' = 1 ');

			$db->setQuery($query);

			$count = $db->loadObjectList();

			if ($count)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Get Id
	 *
	 * @return  int  Id id
	 *
	 * @since 1.2.2
	 */
	public function getId()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$content_id    = $this->getState("content_id", '');
		$userid        = $this->getState("userid", '');

		try
		{
			$query = $db->getQuery(true);

			$query->select($db->qn('id'));
			$query->from($db->qn('#__jlike_likes'));
			$query->where($db->qn('userid') . ' = ' . $db->q($userid));
			$query->where($db->qn('content_id') . ' = ' . $db->q($content_id));

			$db->setQuery($query);

			$result = $db->loadResult();

			if ($result)
			{
				return $result;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Get Total like
	 *
	 * @return  int  Like count
	 *
	 * @since 1.2.2
	 */
	public function getTotalsLike()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$content_id    = $this->getState("content_id", '');
		$annotation_id = $this->getState("annotation_id", '');

		try
		{
			$query = $db->getQuery(true);

			$query->select("COUNT(`id`)");
			$query->from($db->quoteName('#__jlike_likes'));

			if (!empty($content_id))
			{
				$query->where($db->qn('content_id') . ' = ' . $db->q($content_id));
			}

			if (!empty($annotation_id))
			{
				$query->where($db->qn('annotation_id') . ' = ' . $db->q($annotation_id));
			}

			$query->where($db->quoteName('like') . ' = 1');

			$db->setQuery($query);

			return $count = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Get Total dislike
	 *
	 * @return  int  Like count
	 *
	 * @since 1.2.2
	 */
	public function getTotalsDisLike()
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		$content_id    = $this->getState("content_id", '');
		$annotation_id = $this->getState("annotation_id", '');

		try
		{
			$query = $db->getQuery(true);

			$query->select("COUNT(`id`)");
			$query->from($db->quoteName('#__jlike_likes'));

			if (!empty($content_id))
			{
				$query->where($db->qn('content_id') . ' = ' . $db->q($content_id));
			}

			if (!empty($annotation_id))
			{
				$query->where($db->qn('annotation_id') . ' = ' . $db->q($annotation_id));
			}

			$query->where($db->quoteName('dislike') . ' = 1');

			$db->setQuery($query);

			return $count = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
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
	public function getTable($type = 'Like', $prefix = 'JlikeTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('like.id');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('like.id');

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
	 * @return    Form    A Form object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.like', 'likeform', array(
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
		$data = Factory::getApplication()->getUserState('com_jlike.edit.like.data', array());

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
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('like.id');
		$state = (!empty($data['state'])) ? 1 : 0;
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

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			// Create object of content model to get comment data
			$JlikeModelContent = new JlikeModelContent;

			$commentData = (array) $JlikeModelContent->getData($data['content_id']);

			// Append inserted comment entry id in action log data
			$commentData['entry_id'] = $table->id;

			// Trigger the after save event.
			Factory::getApplication()->triggerEvent('onAfterJlikeLikeDislikeSave', array($commentData, true));

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
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('like.id');

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
}
