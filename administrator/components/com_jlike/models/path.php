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

require_once JPATH_ADMINISTRATOR . '/' . 'components/com_jlike/helpers/jlike.php';

/**
 * JLike Path Form model.
 *
 * @since  1.6
 */
class JlikeModelPath extends AdminModel
{
	protected $item = null;

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
	 * @since   12.2
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jlike.edit' . $this->getName() . '.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$data->params = "";

		if (!empty($data->path_id))
		{
			// Encode the incoming json data to show in edit mode.
			if (!empty($data->params))
			{
				$temp = $data->params;
				$data->params = json_encode($temp, JSON_PRETTY_PRINT);
			}
		}

		$date = Factory::getDate()->toSql();

		$user = Factory::getUser();

		// Check that if path_id is not empty
		if (!empty($data->path_id))
		{
			// Existing item
			$data->modified_date = $date;
			$data->modified_by = $user->id;
		}
		else
		{
			// New path. A path created_date and created_by are autoset
			// So we don't touch either of these if they are set.

			if (!intval($data->created_date))
			{
				$data->created_date = $date;
			}

			if (empty($data->created_by))
			{
				$data->created_by = $user->id;
			}
		}

		// Make title as alias if its empty.
		if (empty($data->alias))
		{
			$data->alias = strtolower($data->path_title);
		}

		$data->alias = trim(str_replace(' ', '-', $data->alias));

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \Form  $form  The form to validate against.
	 * @param   Array   $data  The data to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   12.2
	 */
	public function validate($form, $data, $group = NULL)
	{
		$return = true;
		$return = parent::validate($form, $data);

		// Check if the params value is in json format.
		$JLikeHelper = new JLikeHelper;

		if (!$JLikeHelper->isJSON($data['params']))
		{
			$this->setError(Text::_("COM_JLIKE_PATH_VIEW_PARAMS_FIELD_INVALID"));
			$return = false;
		}

		if (!$data['path_type'])
		{
			$this->setError(Text::_("COM_JLIKE_PATH_TYPE_EMPTY"));
			$return = false;
		}

		return $return;
	}
}
