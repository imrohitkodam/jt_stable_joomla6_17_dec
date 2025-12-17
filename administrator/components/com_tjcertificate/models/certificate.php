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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Item Model for an Certificate.
 *
 * @since  1.0.0
 */
class TjCertificateModelCertificate extends AdminModel
{
	/**
	 * Method to get a certificate.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    certificate data object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->id))
			{
				$result->client = $this->getState('certificate.client');
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
		$form = $this->loadForm('com_tjcertificate.certificate', 'certificate', array('control' => 'jform', 'load_data' => $loadData));

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
	public function getTable($type = 'Certificates', $prefix = 'TjCertificateTable', $config = array())
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
		$data = Factory::getApplication()->getUserState('com_tjcertificate.edit.certificate.data', array());

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
		$pk   = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('certificate.id');
		$certificate = TJCERT::Certificate($pk);

		// Bind the data.
		if (!$certificate->bind($data))
		{
			$this->setError($certificate->getError());

			return false;
		}

		$result = $certificate->save();

		// Store the data.
		if (!$result)
		{
			$this->setError($certificate->getError());

			return false;
		}

		$this->setState('certificate.id', $certificate->id);

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
		$this->setState('certificate.id', $id);

		$client    = $jinput->get('client', '');
		$extension = $jinput->get('extension', '');

		if (!empty($extension))
		{
			$this->setState('certificate.client', $extension);
		}
		else
		{
			$this->setState('certificate.client', $client);
		}
	}

	/**
	 * Method to get certificate provider info HTML.
	 *
	 * This method provides the tjlms course/jt event info HTML.
	 *
	 * @param   int     $contentId  contentId 
	 * @param   string  $client     client
	 *
	 * @since   __DEPLOY_VERSION__ 
	 * 
	 * @return  string
	 */
	public function getCertificateProviderInfo($contentId, $client)
	{
		PluginHelper::importPlugin('content');
		$html = Factory::getApplication()->triggerEvent('onContentPrepareTjHtml', array($contentId, $client));

		return trim(implode("\n", $html));
	}

	/**
	 * Method to delete record
	 *
	 * @param   array  $certificateIds  post data
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete(&$certificateIds)
	{
		$certificateIds  = (array) $certificateIds;
		$table    = $this->getTable('Certificates');

		foreach ($certificateIds as $certificateId)
		{
			$table->load(array('id' => (int) $certificateId));

			if ($table->delete($table->id))
			{
				if ($table->is_external)
				{
					Factory::getApplication()->triggerEvent('onTrainingRecordAfterDelete', array($table));
				}

				// Delete media
				$model = TJCERT::model('TrainingRecord', array('ignore_request' => true));
				require_once JPATH_LIBRARIES . '/techjoomla/media/tables/xref.php';
				$tableXref = Table::getInstance('Xref', 'TJMediaTable');
				$tableXref->load(array('client_id' => $table->id));
				$mediaPath = TJCERT::getMediaPath();
				$client    = TJCERT::getClient();

				if ($tableXref->media_id)
				{
					$model->deleteMedia($tableXref->media_id, $mediaPath, $client, $table->id);
				}
			}
		}

		return true;
	}

	/**
	 * Publish the element
	 *
	 * @param   array  $ids    Item id
	 * 
	 * @param   int    $state  Publish state
	 *
	 * @return  boolean
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	public function publish(&$ids, $state = 1)
	{
		$table = $this->getTable();

		foreach ($ids as $id)
		{
			$table->load($id);
			$oldState = $table->state;
			$table->state = $state;

			if ($table->store())
			{
				if ($table->is_external)
				{
					require_once JPATH_SITE . '/components/com_tjcertificate/events/record.php';
					$tjCertificateTriggerRecord = new TjCertificateTriggerRecord;

					// If record state is pending then only send the approval email
					if ($oldState == -1)
					{
						$tjCertificateTriggerRecord->onRecordStateChange($table, $table->state);
					}


					if ($table->state == 1)
					{
						Factory::getApplication()->triggerEvent('onTrainingRecordAfterPublished', array($table));
					}
					elseif ($table->state == 0)
					{
						Factory::getApplication()->triggerEvent('onTrainingRecordAfterUnpublished', array($table));
					}
				}
			}
		}

		return true;
	}
}
