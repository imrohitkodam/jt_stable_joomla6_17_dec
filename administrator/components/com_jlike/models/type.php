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
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

require_once JPATH_ADMINISTRATOR . '/' . 'components/com_jlike/helpers/jlike.php';

/**
 * JLike Path Form model.
 *
 * @since  1.6
 */
class JlikeModelType extends AdminModel
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
	public function getTable($type = 'type', $prefix = 'JlikeTable', $config = array())
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
		$form = $this->loadForm('com_jlike.type', 'type', array('control' => 'jform', 'load_data' => $loadData));

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

		// Ensure params is always a string for the textarea field
		if (isset($data->params))
		{
			if (is_array($data->params))
			{
				// If params is an array, encode it to JSON string
				$data->params = json_encode($data->params, JSON_PRETTY_PRINT);
			}
			elseif (!is_string($data->params))
			{
				// If params is not a string or array, set it to empty string
				$data->params = '';
			}
			// If params is already a string, leave it as is
		}
		else
		{
			// If params doesn't exist, set it to empty string
			$data->params = '';
		}

		return $data;
	}

	/**
	 * Method to save the data to jtable
	 *
	 * @param   array  $data  array of the data
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	public function save($data)
	{
		return parent::save($this->check($data));
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @param   array  $data  array of the data
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   12.2
	 */
	protected function check($data)
	{
		$JLikeHelper = new JLikeHelper;

		if (!$JLikeHelper->isJSON($data['params']))
		{
			$data['params'] = '';
		}

		$data['identifier'] = trim(str_replace(' ', '-', strtolower($data['identifier'])));

		return $data;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \Form  $form  The form to validate against.
	 * @param   array   $data  The data to validate.
	 * @param null|mixed $group
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$return = parent::validate($form, $data);

		// Check if the params value is in json format.
		$JLikeHelper = new JLikeHelper;

		if (!empty($data['params']) && !$JLikeHelper->isJSON($data['params']))
		{
			$this->setError(Text::_("COM_JLIKE_PATH_VIEW_PARAMS_FIELD_INVALID"));
			$return = false;
		}

		$table = $this->getTable();

		$table->load(array('identifier' => $data['identifier']));

		if (empty($data['path_type_id']) && $table->identifier)
		{
			$this->setError(Text::_("COM_JLIKE_PATH_TYPE_VIEW_IDENTIFIER_DUPLICATE"));
			$return = false;
		}
		elseif ($table->identifier && ($table->path_type_id != $data['path_type_id']))
		{
			$this->setError(Text::_("COM_JLIKE_PATH_TYPE_VIEW_IDENTIFIER_DUPLICATE"));
			$return = false;
		}

		return $return;
	}
}
