<?php
/**
 * @package     JTicketing.Plugin
 * @subpackage  JTicketing,Joomlauser
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserHelper;

include_once JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';

/**
 * Joomla user group tjintegration Plugin
 *
 * @since  DEPLOY_VERSION
 */
class PlgJticketingJoomlaUsergroup extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  DEPLOY_VERSION
	 */
	protected $autoloadLanguage = true;

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @return  array
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function onPrepareIntegrationField()
	{
		return array(
				'path' => JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/joomlausergroup.xml', 'name' => $this->_name
			);
	}

	/**
	 * Function used as a trigger after user successfully compelted  order for a event.
	 *
	 * @param   ARRAY  $data  event data.
	 *
	 * @return  void
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function onAfterJtOrderComplete($data)
	{
		// To get event information
		$eventModel = JT::model('eventform');
		$eventData  = $eventModel->getItem($data->event_details_id);

		// To save the users to joomlauser group
		$this->saveEventUsersToJoomlaUserGroup($eventData, $data->user_id);
	}

	/**
	 * Common function to save user after order is completed and after enrollment
	 *
	 * @param   Array    $eventInfo  event details object array
	 * @param   Integer  $userId     userid ID
	 *
	 * @return  void
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function saveEventUsersToJoomlaUserGroup($eventInfo,$userId)
	{
		$user = Factory::getUser($userId);

		if ($user->id && !empty($eventInfo->params['joomlausergroup']['onAfterEnrolUserGroup']))
		{
			try
			{
				$groups = array_merge($user->groups, $eventInfo->params['joomlausergroup']['onAfterEnrolUserGroup']);

				return UserHelper::setUserGroups($user->id, $groups);
			}
			catch (Exception $e)
			{
				return false;
			}
		}
	}

	/**
	 * On after enrolling to event
	 *
	 * Method is called after enrolling to event.
	 *
	 * @param   Array    $post        enrollment details array
	 * @param   Integer  $attendeeId  Attendee ID
	 *
	 * @return  void
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function onAfterJtEnrollment($post, $attendeeId)
	{
		// To get attendee information
		$attendeeData = JT::attendee($attendeeId);

		// To get the event information
		$eventFormModel = JT::model('eventform');
		$event          = $eventFormModel->getItem($attendeeData->event_id);

		// To save the users to joomlauser group
		$this->saveEventUsersToJoomlaUserGroup($event, $attendeeData->owner_id);
	}
}
