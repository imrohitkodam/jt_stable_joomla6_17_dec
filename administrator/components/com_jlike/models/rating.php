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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjlike/includes/rating.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_tjlike/includes/rating.php';
}
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjfields/models/fields.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_tjfields/models/fields.php';
}
if (file_exists(JPATH_SITE . '/components/com_tjucm/models/itemform.php')) {
	require_once JPATH_SITE . '/components/com_tjucm/models/itemform.php';
}

/**
 * Rating Model for an Dashboard.
 *
 * @since  3.0.0
 */
class JlikeModelRating extends AdminModel
{
	/**
	 * Method to get the record form.
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
		$form         = $this->loadForm('com_jlike.rating', 'rating', array('control' => 'jform', 'load_data' => $loadData));
		$ratingTypeId = $this->getState('filter.ratingTypeId');

		if ($ratingTypeId)
		{
			$ratingTypeModel = BaseDatabaseModel::getInstance('ratingtype', 'JLikeModel', array('ignore_request' => true));
			$ratingType = $ratingTypeModel->getItem($ratingTypeId);

			if (!$ratingType->show_title)
			{
				$form->removeField('title');
			}

			if (!$ratingType->show_rating)
			{
				$form->removeField('rating');
			}

			if (!$ratingType->show_review)
			{
				$form->removeField('review');
			}

			if ($ratingType->title_required)
			{
				$form->setFieldAttribute('title', 'required', 'required');
			}

			if ($ratingType->rating_required)
			{
				$form->setFieldAttribute('rating', 'required', 'required');
			}

			if ($ratingType->review_required)
			{
				$form->setFieldAttribute('review', 'required', 'required');
			}

			// Check if Rating has additional UCM type fields
			if ($ratingType->tjucm_type_id)
			{
				$ucmType = $this->getState('filter.ucmType');
				$type    = explode(".", $ucmType);

				// Get UCM data Id
				$ucmDataId = $form->getValue('tjucm_content_id');

				// Authorise UCM use
				$authoriseUCM = $this->authoriseUCM($ratingType->tjucm_type_id, $ucmDataId);

				if (!empty($type[1]) && $authoriseUCM === true)
				{
					$path = JPATH_SITE . '/components/com_tjucm/models/forms/' . $type[1] . 'form_extra.xml';
					$form->loadFile($path, true, '/form/*');
				}
			}
		}

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
	public function getTable($type = 'Rating', $prefix = 'JlikeTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	3.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_jlike.edit.rating.data', array());

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
	 * @since 3.0.0
	 */
	public function save($data)
	{
		$data['submitted_by']  = Factory::getUser()->id;
		$ucmData               = array();
		$ucmData['id']         = 0;
		$ucmData['client']     = $data['ucmType'];
		$ucmData['state']      = 1;
		$ucmData['created_by'] = $data['submitted_by'];
		$ucmData['draft']      = 0;
		$ucmData['status']     = 'save';
		$data['id']            = 0;

		$currentDateTime = Factory::getDate()->toSql();

		$data['created_date'] = $currentDateTime;

		// Trim title and review
		$data['title']  = trim($data['title']);
		$data['review'] = trim($data['review']);

		if (!empty($data['ucmType']))
		{
			$isCompInstalled = ComponentHelper::isEnabled('com_tjucm', true);

			// Authorise UCM use
			$authoriseUCM = $this->authoriseUCM($data['rating_type_id'], $data['tjucm_content_id']);

			if ($isCompInstalled && $authoriseUCM === true)
			{
				$fieldsModel = BaseDatabaseModel::getInstance('Fields', 'TjfieldsModel', array('ignore_request' => true));
				$fieldsModel->setState('filter.client', $data['ucmType']);
				$fields = $fieldsModel->getItems();

				$ucmFields = array();

				foreach ($fields as $field)
				{
					if (array_key_exists($field->name, $data))
					{
						$ucmFields[$field->name] = $data[$field->name];
					}
				}

				$ucmModel = BaseDatabaseModel::getInstance('ItemForm', 'TjucmModel', array('ignore_request' => true));
				$ucmModel->setClient($ucmData['client']);

				$recordId = $ucmModel->save($ucmData, $ucmFields);

				$data['tjucm_content_id'] = $recordId;
			}
		}

		if (parent::save($data))
		{
			PluginHelper::importPlugin("content");
			Factory::getApplication()->triggerEvent('onJlikeRatingAfterSave', array($data));
		}
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
	public function validate($form, $data)
	{
		$return = true;
		$return = parent::validate($form, $data);

		if (!trim($data['title']) || !trim($data['review']))
		{
			$return = false;
		}

		return $return;
	}

	/**
	 * Method to authorise UCM use
	 *
	 * @param   int  $ucmTypeId  UCM type ID.
	 *
	 * @param   int  $ucmDataId  UCM record ID.
	 *
	 * @return  boolean  True if successful.
	 */
	public function authoriseUCM($ucmTypeId, $ucmDataId)
	{
		$user = Factory::getUser();

		if (!empty($ucmDataId))
		{
			// Check the user can edit this item
			$canEdit = $user->authorise('core.type.edititem', 'com_tjucm.type.' . $ucmTypeId);
			$canEditOwn = $user->authorise('core.type.editownitem', 'com_tjucm.type.' . $ucmTypeId);

			// Get the UCM item details
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjucm/tables');
			$itemDetails = Table::getInstance('Item', 'TjucmTable');
			$itemDetails->load(array('id' => $ucmDataId));

			if ($canEdit)
			{
				return true;
			}
			elseif ($canEditOwn && ($itemDetails->created_by == $user->id))
			{
				return true;
			}
		}
		else
		{
			return $user->authorise('core.type.createitem', 'com_tjucm.type.' . $ucmTypeId);
		}
	}
}
