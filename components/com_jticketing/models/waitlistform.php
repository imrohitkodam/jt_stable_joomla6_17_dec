<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;


/**
 * model for waitlist form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       2.2
 */

class JTicketingModelWaitlistForm extends AdminModel
{
	public $jtCommonHelper;

	/**
	 * Constructor.
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/common.php';

		if (!class_exists('JticketingCommonHelper'))
		{
			JLoader::register('JticketingCommonHelper', $path);
			JLoader::load('JticketingCommonHelper');
		}

		$this->jtCommonHelper = new JticketingCommonHelper;

		parent::__construct();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   2.2
	 */
	public function getTable($type = 'Waitinglist', $prefix = 'JTicketingTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient('administrator'))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   2.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return parent::getForm();
	}

	/**
	 * Method to save an waitlisted user data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  mixed  Id on success and false on failure
	 *
	 * @since    2.2
	 */
	public function save($data)
	{
		// Get Jticketing config/params
		$com_params = ComponentHelper::getParams('com_jticketing');
		$enableWaitingList = $com_params->get('enable_waiting_list');

		/*
			Extract has following params :
			$id, $eventId, $userId, $status, $behaviour, $createdDate
		*/

		extract($data);

		if (empty($eventId) && empty($userId))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_REQUIRED_VARIABLE_EMPTY'));

			return false;
		}

		if ($enableWaitingList == 'none')
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ENABLE_WAITING_LIST_SETTING'));

			return false;
		}

		// Check is user already in waiting list
		if (isset($id) && (int) $id)
		{
			// When we update the waiting list status
			$isAdded = 0;
		}
		else
		{
			// Check wheather user aleady added in waitlist
			$isAdded = $this->isAlreadyAddedToWaitlist($eventId, $userId);
		}

		if ($isAdded)
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ALREADY_ADDED_WAITING_LIST'));

			return false;
		}

		$integration = JT::getIntegration();
		$xrefId      = JT::event($eventId, $integration)->integrationId;

		if (empty($xrefId))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_SOMETHING_IS_WRONG_WITH_INTEGRATION'));

			return false;
		}

		$userAccess   = 'E-commerce';
		$wailistUser  = Factory::getUser($userId);
		$canEnrollOwn = $wailistUser->authorise('core.enrollown', 'com_jticketing');
		$canEnroll    = $wailistUser->authorise('core.enroll', 'com_jticketing');

		// Check for component level permission for enrollall and enrollown
		if (($canEnrollOwn || $canEnroll) && $com_params->get('enable_self_enrollment'))
		{
			$userAccess = 'classroom_training';

			// Check is user already in waiting list
			if (isset($id) && (int) $id)
			{
				// When we update the waiting list status
				$isEnrolled = 0;
			}
			else
			{
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
				$jticketingModelEnrollment = BaseDatabaseModel::getInstance('Enrollment', 'JticketingModel');

				$isEnrolled = $jticketingModelEnrollment->isAlreadyEnrolled($eventId, $userId);
			}

			if ($isEnrolled)
			{
				$this->setError(Text::_('COM_JTICKETING_ERROR_ALREADY_ENROLLED'));

				return false;
			}
		}

		if ($enableWaitingList == 'E-commerce' &&  $userAccess == "classroom_training" && $com_params->get('enable_self_enrollment'))
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ENABLE_WAITING_LIST_SETTING_CLASSROOM_TRAINING'));

			return false;
		}

		if ($enableWaitingList == 'classroom_training' &&  $userAccess == 'E-commerce')
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_ENABLE_WAITING_LIST_SETTING_ECOMMERCE'));

			return false;
		}

		// Setting up data to store
		$waitinglistData = array();

		// This is xref event id
		$waitinglistData['event_id'] = $xrefId;

		if (isset($id))
		{
			$waitinglistData['id'] = $id;
		}

		if (isset($userId))
		{
			$waitinglistData['user_id'] = $userId;
		}

		if (isset($status) && in_array($status, array('WL', 'C', 'CA')))
		{
			$waitinglistData['status'] = $status;
		}

		if (isset($behaviour) && in_array($behaviour, array('E-commerce', 'classroom_training')) && $behaviour == $userAccess)
		{
			$waitinglistData['behaviour'] = $behaviour;
		}

		$date = Factory::getDate();

		$waitinglistData['created_date'] = $date->toSql(true);

		// Already existed waitlist updated.
		if (isset($waitinglistData['id']))
		{
			$isNew = false;
		}
		// Added new user to waitlist.
		else
		{
			$isNew = true;
		}

		if (parent::save($waitinglistData))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$waitinglistData['waitlistId'] = $id;

			// Trigger - OnAfterAddToWaitList
			PluginHelper::importPlugin('jticketing');
			Factory::getApplication()->triggerEvent('onAfterJtAddToWaitList', array($waitinglistData, $isNew));

			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to check is user already added
	 *
	 * @param   INT  $eventId  event id.
	 *
	 * @param   INT  $userId   user id.
	 *
	 * @return  Boolean
	 *
	 * @since   2.1
	 */
	public function isAlreadyAddedToWaitlist($eventId, $userId)
	{
		$db          = Factory::getDbo();
		$integration = JT::getIntegration();
		$xrefId      = JT::event($eventId, $integration)->integrationId;

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_waiting_list'));
		$query->where($db->quoteName('user_id') . ' = ' . (int) $userId);
		$query->where($db->quoteName('event_id') . ' = ' . (int) $xrefId);
		$db->setQuery($query);
		$addedUser = $db->loadObject();

		if (!empty($addedUser))
		{
			return true;
		}

		return false;
	}
}
