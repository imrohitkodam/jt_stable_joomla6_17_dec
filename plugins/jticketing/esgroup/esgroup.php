<?php
/**
 * @package     JTicketing.Plugin
 * @subpackage  JTicketing,EasySocial
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }

require_once JPATH_ROOT . '/plugins/jticketing/esgroup/elements/groupcategory.php';
include_once JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';

/**
 * Joomla user group tjintegration Plugin
 *
 * @since  3.2.0
 */
class PlgJticketingEsgroup extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.2.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @return  array
	 *
	 * @since   3.2.0
	 */
	public function onPrepareIntegrationField()
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$app    = Factory::getApplication();

		$cid = $app->getInput()->get('id', 0, 'INT');

		// To get the event information
		$eventFormModel = JT::model('eventform');
		$event          = $eventFormModel->getItem($cid);

		// To get the orders against event
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'JticketingModel');
		$orderModel     = BaseDatabaseModel::getInstance('orders', 'JticketingModel', array('ignore_request' => true));

		if ($app->isClient("administrator"))
		{
			$orderModel->setState('filter.events', (int) $cid);
		}
		else
		{
			$orderModel->setState('search_event', (int) $cid);
		}

		$enrolled_users = count($orderModel->getItems());

		if (!empty($event->params['esgroup']['onAfterEnrollEsGroups']))
		{
			$groups = $event->params['esgroup']['onAfterEnrollEsGroups'];
			$groups = count($groups);
		}

		if (empty($groups))
		{
			$groups = 0;
		}

		$document = Factory::getDocument();
		HTMLHelper::_('script', 'plugins/jticketing/esgroup/esgroup.js');
		$document->addScriptDeclaration("jQuery(document).ready(function() {");
		$document->addScriptDeclaration('esGroup.enrolledUsers= "' . $enrolled_users . '";');
		$document->addScriptDeclaration('esGroup.groups= ' . $groups . ';');
		$document->addScriptDeclaration('esGroup.init();');
		$document->addScriptDeclaration("});");

		if (ComponentHelper::isEnabled('com_easysocial', true))
		{
			return array(
				'path' => JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/esgroup.xml', 'name' => $this->_name
			);
		}
	}

	/**
	 * Function used as a trigger after user successfully compelted  order for a event.
	 *
	 * @param   ARRAY  $data  event data.
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function onAfterJtOrderComplete($data)
	{
		// If for whatever reason, ES component is not installed then return error
		if (!ComponentHelper::isEnabled('com_easysocial', true))
		{
			$application = Factory::getApplication();

			// Add a message to the message queue
			$application->enqueueMessage(Text::_('COM_EASYSOCIAL_NOT_INSTALLED'), 'error');
		}

		// To get event information
		$eventModel = JT::model('eventform');
		$eventData  = $eventModel->getItem($data->event_details_id);

		// To save the users to easysocial group
		$this->onJtSaveEventUsersToEsGroup($eventData, $data->user_id);
	}

	/**
	 * Common function to save user after order is completed and after enrollment
	 *
	 * @param   Array    $eventInfo  event details object array
	 * @param   Integer  $userId     userid ID
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function onJtSaveEventUsersToEsGroup($eventInfo,$userId)
	{
		// To get the user object through id
		$user = Factory::getUser($userId);

		$arr = !empty($eventInfo->params['esgroup']['onAfterEnrollEsGroups']) ? $eventInfo->params['esgroup']['onAfterEnrollEsGroups'] : array();

		// To enter user in each selected group
		foreach ($arr as $value)
		{
			$group = ES::group($value);

			if (!$group->createMember($user->id, true))
			{
				$application = Factory::getApplication();

				// Add a message to the message queue
				$application->enqueueMessage(Text::_('COM_EASYSOCIAL_MEMBER_NOT_ADDED'), 'error');
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
	 * @since   3.2.0
	 */
	public function onAfterJtEnrollment($post, $attendeeId)
	{
		// If for whatever reason, ES component is not installed then return error
		if (!ComponentHelper::isEnabled('com_easysocial', true))
		{
			$application = Factory::getApplication();

			// Add a message to the message queue
			$application->enqueueMessage(Text::_('COM_EASYSOCIAL_NOT_INSTALLED'), 'error');
		}

		// To get attendee information
		$attendeeData = JT::attendee($attendeeId);

		// To get the event information
		$eventFormModel = JT::model('eventform');
		$event          = $eventFormModel->getItem($attendeeData->event_id);

		// To save the users to easysocial group
		$this->onJtSaveEventUsersToEsGroup($event, $attendeeData->owner_id);
	}

	/**
	 * On Creating a event
	 *
	 * Method is called after a event create.
	 * This method create a EasySocial group.
	 *
	 * @param   ARRAY  $data  event data.
	 *
	 * @return  false
	 *
	 * @since   3.2.0
	 */
	public function onAfterJtEventSave($data)
	{
		$db = Factory::getDbo();

		$autoCreateGroup = $data['params']['esgroup']['eventgroup'];

		if (!empty($data['id']))
		{
			$oldParams        = $data['eventOldData']->params;
			$data['group_id'] = $oldParams['esgroup']['onAfterEnrollEsGroups'][0];
		}

		$table = Table::getInstance('Event', 'JTicketingTable', array('dbo', $db));
		$group_created = '';

		if ($autoCreateGroup == 'create')
		{
			if (empty($this->groupId))
			{
				$group_created = $this->onJtSaveEventGroup($data);
			}

			if ($group_created)
			{
				// Save group ID in events table
				$obj      = new stdclass;
				$obj->id  = !empty($data['eventId']) ? $data['eventId'] : $data['id'];

				$table->load($obj->id);

				$onAfterEnrollEsGroups                          = array();
				$esgArray                                       = array();
				$onAfterEnrollEsGroups['onAfterEnrollEsGroups'] = (array) $group_created;
				$esgArray['esgroup']                            = $onAfterEnrollEsGroups;

				if (!empty($table->params))
				{
					$cparams            = (array) json_decode($table->params);
					$cparams['esgroup'] = $onAfterEnrollEsGroups;
					$obj->params        = json_encode($cparams);
				}
				else
				{
					$obj->params = json_encode($esgArray);
				}

				$data['params'] = $obj->params;
				$data['id']     = $obj->id;

				// To save the group
				$eventobj             = new JTicketingEventJticketing($obj->id);
				$eventobj->params     = $obj->params;
				$eventobj->save($data);
			}
		}
	}

	/**
	 * Create group depending upon the integration set
	 *
	 * @param   ARRAY  $data  event data
	 *
	 * @return  INT  Group ID
	 *
	 * @since   3.2.0
	 */
	public function onJtSaveEventGroup($data)
	{
		$easySocialObj = new JSocialEasysocial;

		$groupId = '';
		$options = array();

		$catId = $data['params']['esgroup']['groupCategory'];

		if ($catId)
		{
			$data['uid']      = $data['created_by'];
			$data['type']     = 1;
			$options['catId'] = $catId;
			$this->groupId    = $easySocialObj->createGroup($data, $options);

			// Add event creator to the created group
			$easySocialObj->addMemberToGroup($groupId, Factory::getUser($data['created_by']));
		}

		return $this->groupId;
	}
}
