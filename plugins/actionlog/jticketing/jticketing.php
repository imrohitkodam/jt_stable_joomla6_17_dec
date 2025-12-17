<?php
/**
 * @package     JTicketing
 * @subpackage  Actionlog.jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Database\DatabaseInterface;

Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_jticketing/tables');
/**
 * JTicketing Actions Logging Plugin.
 *
 * @since  2.3.4
 */
class PlgActionlogJTicketing extends CMSPlugin
{
	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  2.3.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * On saving event data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   array    $data   Holds the new event data.
	 * @param   boolean  $isNew  True if a new event is stored.
	 *
	 * @return  void
	 *
	 * @since    2.3.4
	 */
	public function onAfterJtEventSave($data, $isNew)
	{
		if (!$this->params->get('logActionForEventCreate', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');

		$jUser = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_UPDATED';
			$action             = 'update';
		}

		$userId   = $jUser->id;
		$userName = $jUser->username;

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_EVENT',
			'id'          => $data['eventId'],
			'title'       => $data['title'],
			'itemlink'    => 'index.php?option=com_jticketing&task=event.edit&id=' . $data['eventId'],
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after deleting event data logging method
	 *
	 * Method is called after event data is deleted from  the database.
	 *
	 * @param   array  $data  Holds the event data.
	 *
	 * @return  void
	 *
	 * @since    2.3.4
	 */
	public function onAfterJtEventDelete($data)
	{
		if (!$this->params->get('logActionForEventDelete', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_DELETED';
		$action             = 'delete';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		$message = array(
				'action'      => $action,
				'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_EVENT',
				'id'          => $data->id,
				'title'       => $data->title,
				'userid'      => $userId,
				'username'    => $userName,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after changing event state logging method
	 *
	 * Method is called after event state is changed from  the database.
	 *
	 * @param   String   $context  extension name
	 * @param   Array    $pks      Holds the events id
	 * @param   Integer  $value    0-indicate unpublish 1-indicate publish.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtEventChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForEventChangeState', 1))
		{
			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableEvent = Table::getInstance('Event', 'JticketingTable', array('dbo' => $db));
		$context              = Factory::getApplication()->getInput()->get('option');
		$jUser                = Factory::getUser();
		$userId               = $jUser->id;
		$userName             = $jUser->username;

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_EVENT_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		foreach ($pks as $eventID)
		{
			$jticketingTableEvent->load(array('id' => $eventID));

			$message = array(
					'action'      => $action,
					'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_EVENT',
					'id'          => $jticketingTableEvent->id,
					'title'       => $jticketingTableEvent->title,
					'itemlink'    => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On after saving venue data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   array    $data   Holds the new venue data.
	 * @param   boolean  $isNew  True if a new venue is stored.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtVenueSave($data, $isNew)
	{
		if (!$this->params->get('logActionForVenueCreate', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');

		$jUser = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_UPDATED';
			$action             = 'update';
		}

		$userId   = $jUser->id;
		$userName = $jUser->username;

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_VENUE',
			'id'          => $data['venueId'],
			'title'       => $data['name'],
			'itemlink'    => 'index.php?option=com_jticketing&view=venue&layout=edit&id=' . $data['venueId'],
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after deleting venue data logging method
	 *
	 * Method is called after venue data is deleted from  the database.
	 *
	 * @param   array  $venue  Holds the venues data.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtDeleteVenue($venue)
	{
		if (!$this->params->get('logActionForVenueDelete', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');

		$jUser              = Factory::getUser($venue->created_by);
		$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_DELETED';
		$action             = 'delete';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_VENUE',
			'id'          => $venue->id,
			'title'       => $venue->name,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after changing venue state logging method
	 *
	 * Method is called after venue state is changed from  the database.
	 *
	 * @param   String   $context  extension name
	 * @param   Array    $pk       Holds the venue id
	 * @param   Integer  $value    0-indicate unpublish 1-indicate publish.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtVenueChangeState($context, $pk, $value)
	{
		if (!$this->params->get('logActionForVenueChangeState', 1))
		{
			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTablevenue = Table::getInstance('Venue', 'JticketingTable', array('dbo' => $db));
		$context              = Factory::getApplication()->getInput()->get('option');
		$jUser                = Factory::getUser();
		$userId               = $jUser->id;
		$userName             = $jUser->username;

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_VENUE_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		$jticketingTablevenue->load(array('id' => $pk));
		$message = array(
				'action'      => $action,
				'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_VENUE',
				'id'          => $jticketingTablevenue->id,
				'title'       => $jticketingTablevenue->name,
				'itemlink'    => 'index.php?option=com_jticketing&view=venue&layout=edit&id=' . $jticketingTablevenue->id,
				'userid'      => $userId,
				'username'    => $userName,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On saving coupon data logging method
	 *
	 * Method is called after coupon data is stored in the database.
	 *
	 * @param   Array    $data   Holds the coupon data.
	 * @param   Boolean  $isNew  True if a new coupon is stored.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtCouponSave($data, $isNew)
	{
		if (!$this->params->get('logActionForCouponCreate', 1))
		{
			return;
		}

		$context = Factory::getApplication()->getInput()->get('option');
		$jUser = Factory::getUser();

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_COUPON_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_COUPON_UPDATED';
			$action             = 'update';
		}

		$userId   = $jUser->id;
		$userName = $jUser->username;

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_COUPON',
			'id'          => $data['couponId'],
			'title'       => $data['name'],
			'itemlink'    => 'index.php?option=com_jticketing&view=coupon&layout=edit&id=' . $data['couponId'],
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after deleting coupon data logging method
	 *
	 * Method is called after coupon data is deleted from  the database.
	 *
	 * @param   string  $context  com_jticketing.
	 * @param   Object  $table    Holds the coupon data.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtCouponDelete($context, $table)
	{
		if (!$this->params->get('logActionForCouponDelete', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_COUPON_DELETED';
		$action             = 'delete';
		$userId             = $jUser->id;
		$userName           = $jUser->username;

		$message = array(
				'action'      => $action,
				'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_COUPON',
				'id'          => $table->id,
				'title'       => $table->name,
				'userid'      => $userId,
				'username'    => $userName,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after changing coupon state logging method
	 *
	 * Method is called after coupon state is changed from  the database.
	 *
	 * @param   String   $context  extension name
	 * @param   Array    $pks      Holds the coupon id
	 * @param   Integer  $value    0-indicate unpublish 1-indicate publish.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtCouponChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForCouponChangeState', 1))
		{
			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTablecoupon = Table::getInstance('Coupon', 'JticketingTable', array('dbo' => $db));
		$context               = Factory::getApplication()->getInput()->get('option');
		$jUser                 = Factory::getUser();
		$userId                = $jUser->id;
		$userName              = $jUser->username;

		switch ($value)
		{
			case 0:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_COUPON_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_COUPON_PUBLISHED';
				$action             = 'publish';
				break;
			default:
				$messageLanguageKey = '';
				$action             = '';
				break;
		}

		foreach ($pks as $pk)
		{
			$jticketingTablecoupon->load(array('id' => $pk));
			$message = array(
					'action'      => $action,
					'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_COUPON',
					'id'          => $jticketingTablecoupon->id,
					'title'       => $jticketingTablecoupon->name,
					'itemlink'    => 'index.php?option=com_jticketing&view=coupon&layout=edit&id=' . $jticketingTablecoupon->id,
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On after changing core attendee fields state logging method
	 *
	 * Method is called after core attendee fields state is changed from  the database.
	 *
	 * @param   String   $context  extension name
	 * @param   Array    $pks      Holds the core attendee fields id
	 * @param   Integer  $value    0-indicate unpublish 1-indicate publish.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtAttendeeFieldsChangeState($context, $pks, $value)
	{
		if (!$this->params->get('logActionForAttendeeFieldsChangeState', 1))
		{
			return;
		}

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableAttendeefields = Table::getInstance('Attendeefields', 'JticketingTable', array('dbo' => $db));
		$context              = Factory::getApplication()->getInput()->get('option');
		$jUser                = Factory::getUser();
		$messageLanguageKey   = 'PLG_ACTIONLOGS_JTICKETING_ATTENDEE_FIELDS_CHANGE_STATE';
		$action               = 'update';
		$userId               = $jUser->id;
		$userName             = $jUser->username;

		foreach ($pks as $attendeeFieldsId)
		{
			$jticketingTableAttendeefields->load(array('id' => $attendeeFieldsId));

			$message = array(
					'action'      => $action,
					'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ATTENDEE_FIELDS',
					'id'          => $jticketingTableAttendeefields->id,
					'title'       => Text::_($jticketingTableAttendeefields->label),
					'itemlink'    => 'index.php?option=com_jticketing&task=event.edit&id=' . $jticketingTableAttendeefields->id,
					'state'       => $value ? 'PLG_ACTIONLOGS_JTICKETING_PUBLISH_STATE' : 'PLG_ACTIONLOGS_JTICKETING_UNPUBLISH_STATE',
					'userid'      => $userId,
					'username'    => $userName,
					'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				);

			$this->addLog(array($message), $messageLanguageKey, $context, $userId);
		}
	}

	/**
	 * On after checkin or checkout logging method
	 *
	 * Method is called after checkin or checkout to event.
	 *
	 * @param   Array  $checkinData  Attendee Checking data
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtAttendeeCheckin($checkinData)
	{
		if (!$this->params->get('logActionForAttendeeCheckin', 1))
		{
			return;
		}

		$jUser     = Factory::getUser();
		$userId    = $jUser->id;
		$userName  = $jUser->username;
		$context   = Factory::getApplication()->getInput()->get('option');
		$action    = 'update';

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableEvent = Table::getInstance('Event', 'JticketingTable', array('dbo' => $db));
		$jticketingTableEvent->load(array('id' => $checkinData['eventid']));

		$attendeeUserData = Factory::getUser($checkinData['owner_id']);
		$attendeeUserName = $attendeeUserData->username;
		$attendeeUserId   = $attendeeUserData->id;

		$messageLanguageKey     = 'PLG_ACTIONLOGS_JTICKETING_ATTENDEE_CHECKIN';
		$message = array(
			'action'            => $action,
			'id'                => $jticketingTableEvent->id,
			'title'             => $jticketingTableEvent->title,
			'itemlink'          => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
			'attendeeuserid'   => $attendeeUserId,
			'attendeeusername' => $attendeeUserName,
			'attendeeuserlink' => 'index.php?option=com_users&task=user.edit&id=' . $attendeeUserId,
			'checkstatus'       => $checkinData['checkin'] ? 'PLG_ACTIONLOGS_JTICKETING_CHECK_IN' : 'PLG_ACTIONLOGS_JTICKETING_CHECK_OUT',
			'userid'            => $userId,
			'username'          => $userName,
			'accountlink'       => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after enrolling to event logging method
	 *
	 * Method is called after enrolling to event.
	 *
	 * @param   Array    $post        enrollment details array
	 * @param   Integer  $attendeeId  Attendee ID
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtEnrollment($post, $attendeeId)
	{
		if (!$this->params->get('logActionForEnrollment', 1))
		{
			return;
		}

		$jUser                = Factory::getUser();
		$userId               = $jUser->id;
		$userName             = $jUser->username;
		$context               = Factory::getApplication()->getInput()->get('option');
		$action                = 'update';

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
		$attendeeDetails   = $attendeeFormModel->getItem($attendeeId);

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableEvent = Table::getInstance('Event', 'JticketingTable', array('dbo' => $db));
		$jticketingTableEvent->load(array('id' => $attendeeDetails->event_id));

		// Enrolled for self
		if ($jUser->id == $attendeeDetails->owner_id)
		{
			$messageLanguageKey    = 'PLG_ACTIONLOGS_JTICKETING_USER_SELF_ENROLLED_FOR_EVENT';
			$message = array(
				'action'      => $action,
				'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ENROLLED',
				'id'          => $jticketingTableEvent->id,
				'title'       => $jticketingTableEvent->title,
				'itemlink'    => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
				'userid'      => $userId,
				'username'    => $userName,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);
		}
		// Enrolled for other
		else
		{
			$attendeeUserData  = Factory::getUser($attendeeDetails->owner_id);
			$attendeeId = $attendeeUserData->id;
			$attendeeName = $attendeeUserData->username;

			$messageLanguageKey     = 'PLG_ACTIONLOGS_JTICKETING_USER_ENROLLED_TO_OTHER_USER_FOR_EVENT';
			$message = array(
				'action'            => $action,
				'type'              => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ENROLLED',
				'id'                => $jticketingTableEvent->id,
				'title'             => $jticketingTableEvent->title,
				'itemlink'          => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
				'attendeeuserid'   => $attendeeId,
				'attendeeusername' => $attendeeName,
				'attendeeuserlink' => 'index.php?option=com_users&task=user.edit&id=' . $attendeeId,
				'userid'            => $userId,
				'username'          => $userName,
				'accountlink'       => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);
		}

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after ticket order placed logging method
	 *
	 * Method is called after ticket order is placed of event.
	 *
	 * @param   Array    $post      Event post data
	 * @param   Integer  $orderId   Event order id
	 * @param   String   $pgPlugin  Plugin Name
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtProcessPayment($post, $orderId, $pgPlugin = null)
	{
		if (!$this->params->get('logActionForOrderPlaced', 1))
		{
			return;
		}

		$context     = Factory::getApplication()->getInput()->get('option');
		$jUser       = Factory::getUser();
		$userId      = $jUser->id;

		$action       = 'add';
		$orderDetails = JT::order()->loadByOrderId($orderId);

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableEvent = Table::getInstance('Event', 'JticketingTable', array('dbo' => $db));
		$jticketingTableEvent->load(array('id' => $orderDetails->event_details_id));

		if ($orderDetails->user_id != 0)
		{
			$messageLanguageKey   = 'PLG_ACTIONLOGS_JTICKETING_ORDER_PLACED';
			$message = array(
				'action'    => $action,
				'type'      => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ORDER',
				'id'        => $jticketingTableEvent->id,
				'title'     => $jticketingTableEvent->title,
				'itemlink'  => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
				'orderid'   => $orderDetails->order_id,
				'orderlink' => 'index.php?option=com_jticketing&view=orders&layout=order&event='
				. $jticketingTableEvent->id . '&orderid=' . $orderDetails->order_id . '&Itemid=&tmpl=component',
				'buyername' => $orderDetails->name,
				'buyerlink' => 'index.php?option=com_users&view=user&layout=edit&id=' . $orderDetails->user_id,
			);
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_ORDER_PLACED_BY_GUEST_USER';
			$message = array(
				'action'    => $action,
				'type'      => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ORDER',
				'id'        => $jticketingTableEvent->id,
				'title'     => $jticketingTableEvent->title,
				'itemlink'  => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
				'orderid'   => $orderDetails->order_id,
				'orderlink' => 'index.php?option=com_jticketing&view=orders&layout=order&event='
				. $jticketingTableEvent->id . '&orderid=' . $orderDetails->order_id . '&Itemid=&tmpl=component',
				'buyername' => $orderDetails->name,
			);
		}

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after ticket order placed logging method
	 *
	 * Method is called after ticket order status is changed.
	 *
	 * @param   Integer  $orderId  Order id
	 * @param   String   $status   Order Status
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtOrderStatusChange($orderId, $status)
	{
		if (!$this->params->get('logActionForOrderPlaced', 1))
		{
			return;
		}

		$context  = Factory::getApplication()->getInput()->get('option');
		$jUser    = Factory::getUser();
		$userId   = $jUser->id;
		$userName = $jUser->username;

		$orderDetails         = JT::order()->loadByOrderId($orderId);
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$jticketingTableEvent = Table::getInstance('Event', 'JticketingTable', array('dbo' => $db));
		$jticketingTableEvent->load(array('id' => $orderDetails->event_details_id));
		$messageLanguageKey   = 'PLG_ACTIONLOGS_JTICKETING_ORDER_STATUS_CHANGED';
		$action               = 'update';

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ORDER',
			'eventtitle'  => $jticketingTableEvent->title,
			'eventlink'   => 'index.php?option=com_jticketing&view=event&layout=edit&id=' . $jticketingTableEvent->id,
			'orderid'     => $orderDetails->order_id,
			'orderlink'   => 'index.php?option=com_jticketing&view=orders&layout=order&event=' . $jticketingTableEvent->id .
			'&orderid=' . $orderDetails->order_id . '&Itemid=&tmpl=component',
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&view=user&layout=edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after deleting order data logging method
	 *
	 * Method is called after order data is deleted from  the database.
	 *
	 * @param   array  $orderData  Holds the orders data.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtOrderDelete($orderData)
	{
		if (!$this->params->get('logActionForOrderDeleted', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$userId             = $jUser->id;
		$userName           = $jUser->username;
		$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_ORDER_DELETE';
		$action             = 'delete';

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_ORDER',
			'id'          => $orderData->id,
			'title'       => $orderData->order_id,
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&view=user&layout=edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after add to wait list data logging method
	 *
	 * Method is called after user add into wait list.
	 *
	 * @param   Array    $waitListData  Holds the waitList data.
	 * @param   boolean  $isNew         True if a new venue is stored.
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	public function onAfterJtAddToWaitList($waitListData, $isNew)
	{
		if (!$this->params->get('logActionForAddToWaitList', 1))
		{
			return;
		}

		$context            = Factory::getApplication()->getInput()->get('option');
		$jUser              = Factory::getUser();
		$messageLanguageKey = '';

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_USER_ADDED_TO_WAIT_LIST';
			$action             = 'add';
		}
		else
		{
			if ($waitListData['status'] == 'CA')
			{
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_USER_CANCLED_FROM_WAIT_LIST';
			}
			elseif ($waitListData['status'] == 'C')
			{
				$messageLanguageKey = 'PLG_ACTIONLOGS_JTICKETING_USER_CLEARED_FROM_WAIT_LIST';
			}

			$action             = 'update';
		}

		$userId             = $jUser->id;
		$userName           = $jUser->username;

		$eventData = JT::event()->loadByIntegration($waitListData['event_id']);

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOGS_JTICKETING_TYPE_WAITING_LIST',
			'id'          => $waitListData['waitlistId'],
			'title'       => $eventData->getTitle(),
			'itemlink'    => 'index.php?option=com_jticketing&task=event.edit&id=' . $waitListData['event_id'],
			'userid'      => $userId,
			'username'    => $userName,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   2.3.4
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		// Joomla 6: Use MVCFactory for model instantiation
		$model = Factory::getApplication()->bootComponent('com_actionlogs')
			->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);

		/* @var ActionlogsModelActionlog $model */
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}
}
