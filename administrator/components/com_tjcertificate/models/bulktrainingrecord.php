<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use TJQueue\Admin\TJQueueProduce;

if (ComponentHelper::getComponent('com_tjqueue', true)->enabled)
{
	require_once JPATH_ADMINISTRATOR . '/components/com_tjqueue/libraries/tjqueueproduce.php';
}

/**
 * TjCertificate Bulk Training Record Model.
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateModelBulkTrainingRecord extends AdminModel
{
	protected $comMultiAgency = 'com_multiagency';

	public $params;

	public $user;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		$this->params = ComponentHelper::getParams('com_tjcertificate');
		$this->user   = Factory::getuser();

		parent::__construct($config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_tjcertificate.bulktrainingrecord', 'bulktrainingrecord', array('control' => 'jform', 'load_data' => $loadData));

		return empty($form) ? false : $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \Form  $form   The form to validate against.
	 * @param   Array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$return = parent::validate($form, $data, $group);

		if (!empty($data['expired_on']) && $data['expired_on'] != '0000-00-00 00:00:00')
		{
			if ($data['issued_on'] > $data['expired_on'])
			{
				$this->setError(Text::_('COM_TJCERTIFICATE_EXPIRY_DATE_VALIDATION_MESSAGE'));
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Method to push data in queue.
	 *
	 * @param   array  $data  record data
	 *
	 * @return  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function addToQueue($data)
	{
		$return      = array();
		$messageBody = (object) $data;

		try
		{
			$TJQueueProduce = new TJQueueProduce;

			// Set message body
			$TJQueueProduce->message->setBody(json_encode($messageBody));

			// @Params client, value
			$TJQueueProduce->message->setProperty('client', 'certificate.records');
			$TJQueueProduce->produce();
		}
		catch (Exception $e)
		{
			$return['success'] = 0;
			$return['message'] = $e->getMessage();

			return $return;
		}

		$return['success'] = 1;
		$return['message'] = '';

		return $return;
	}
}
