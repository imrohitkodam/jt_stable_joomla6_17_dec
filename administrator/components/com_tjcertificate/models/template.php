<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Model for Certificate template.
 *
 * @since  1.0.0
 */
class TjCertificateModelTemplate extends AdminModel
{
	/**
	 * Method to get a template.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Template data object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->id))
			{
				$result->client = $this->getState('template.client');
			}
		}

		return $result;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_tjcertificate.template', 'template', array('control' => 'jform', 'load_data' => $loadData));

		return empty($form) ? false : $form;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 */
	public function getTable($type = 'Templates', $prefix = 'TjCertificateTable', $config = array())
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_tjcertificate.edit.template.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
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
	 * @since 1.0.0
	 */
	public function save($data)
	{
		$pk   = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('template.id');
		$template = TJCERT::Template($pk);

		// While editing the template don't allow to edit unique_code
		if ($data['id'])
		{
			unset($data['unique_code']);
		}

		// PDF options
		if (isset($data['params']) && is_array($data['params']))
		{
			$registry = new Registry;
			$registry->loadArray($data['params']);
			$data['params'] = (string) $registry;
		}

		// Bind the data.
		if (!$template->bind($data))
		{
			$this->setError($template->getError());

			return false;
		}

		$result = $template->save();

		// Store the data.
		if (!$result)
		{
			$this->setError($template->getError());

			return false;
		}

		$this->setState('template.id', $template->id);

		return true;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return   void
	 *
	 * @since    1.0.0
	 */

	protected function populateState()
	{
		$jinput = Factory::getApplication()->input;
		$id = ($jinput->get('id'))?$jinput->get('id'):$jinput->get('id');
		$this->setState('template.id', $id);

		$client    = $jinput->get('client', '');
		$extension = $jinput->get('extension', '');

		if (!empty($extension))
		{
			$this->setState('template.client', $extension);
		}
		else
		{
			$this->setState('template.client', $client);
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form  $form  The form to validate against.
	 * @param   Array   $data  The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$return = parent::validate($form, $data);

		$data['template_css'] = trim($data['template_css']);

		return $return;
	}
}
