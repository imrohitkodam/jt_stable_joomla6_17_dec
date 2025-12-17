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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * JLike Rating Type Form model.
 *
 * @since  3.0.0
 */
class JlikeModelRatingtype extends AdminModel
{
	private $item = null;

	/**
	 * Get an instance of Table class
	 *
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'ratingtype', $prefix = 'JlikeTable', $config = array())
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
	 * @since   3.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.ratingtype', 'ratingtype', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   3.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jlike.edit.' . $this->getName() . '.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to save the data to jtable
	 *
	 * @param   Array  $data  array of the data
	 *
	 * @return  boolean
	 *
	 * @since   3.0.0
	 */
	public function save($data)
	{
		$data['title_required'] = $data['rating_required'] = $data['review_required'] = 0;

		if ($data['show_title'] == 2)
		{
			$data['show_title'] = $data['title_required'] = 1;
		}

		if ($data['show_rating'] == 2)
		{
			$data['show_rating'] = $data['rating_required'] = 1;
		}

		if ($data['show_review'] == 2)
		{
			$data['show_review'] = $data['review_required'] = 1;
		}

		return parent::save($data);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   3.0.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if ($item->title_required)
			{
				$item->show_title = 2;
			}

			if ($item->rating_required)
			{
				$item->show_rating = 2;
			}

			if ($item->review_required)
			{
				$item->show_review = 2;
			}
		}

		return $item;
	}

	/**
	 * Method to set a default rating type.
	 *
	 * @param   integer  $id  The primary key ID for the rating type.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	Exception
	 */
	public function setDefaultRatingType($id = 0)
	{
		$db   = $this->getDbo();

		// Reset the home fields for the client_id.
		$query = $db->getQuery(true)
			->update('#__jlike_rating_types')
			->set('is_default = ' . $db->q('0'));
		$db->setQuery($query);
		$db->execute();

		// Set the new home style.
		$query = $db->getQuery(true)
			->update('#__jlike_rating_types')
			->set('is_default = ' . $db->q('1'))
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to unset a rating type as default.
	 *
	 * @param   integer  $id  The primary key ID for the rating type.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws	Exception
	 */
	public function unsetDefaultRatingType($id = 0)
	{
		$db         = $this->getDbo();
		$ratingType = $this->getItem($id);

		if ($ratingType->is_default == '1')
		{
			throw new Exception(Text::_('COM_JLIKE_ERROR_CANNOT_UNSET_DEFAULT_RATING_TYPE'));
		}

		// Set the new home style.
		$query = $db->getQuery(true)
			->update('#__jlike_rating_types')
			->set('is_default = ' . $db->q('0'))
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Publish/Unpublish a Rating type.
	 *
	 * @param   array  &$eid   Rating type ids to un/publish
	 * @param   int    $value  Publish value
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function publish(&$eid = array(), $value = 1)
	{
		$result = true;

		if (!is_array($eid))
		{
			$eid = array($eid);
		}

		if (!empty($eid))
		{
			foreach ($eid as $i => $id)
			{
				$table = $this->getTable();

				$table->load($id);

				if ($table->is_default == 1 && $value == 0)
				{
					$app = Factory::getApplication();
					$app->enqueueMessage(Text::_('COM_JLIKE_ERROR_DISABLE_DEFAULT_RATING_TYPE_NOT_PERMITTED'), 'error');

					unset($eid[$i]);
					continue;
				}

				$table->state = $value;

				if (!$table->store())
				{
					$this->setError($table->getError());
					$result = false;
				}
			}
		}

		return $result;
	}
}
