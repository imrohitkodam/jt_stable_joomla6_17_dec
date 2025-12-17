<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceCheckin extends ApiResource
{
	/**
	 * Checkin to tickets for mobile APP
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = 'Get method not allow, Use post method.';
		$this->plugin->setResponse(null);
	}

	/**
	 * Checkin to tickets for mobile APP
	 *
	 * @return  json event details
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$input                = Factory::getApplication()->input;
		$db                   = Factory::getDbo();
		$attendeeIds          = $input->get('ticketid', array(), 'post', 'array');

		if (empty($attendeeIds))
		{
			$attendeeIds = $input->get('attendee_id', array(), 'post', 'array');
		}

		$versionCheck         = $input->get('versionCheck', '', 'boolean');
		$jticketingmainhelper = new jticketingmainhelper;
		$lang                 = Factory::getLanguage();
		$extension            = 'com_jticketing';
		$base_dir             = JPATH_SITE;
		$lang->load($extension, $base_dir);
		$eventId = $input->get('eventid', '0', 'int');
		$state = $input->get('state', '0', 'int');

		$tempDuplicate = array();
		$tempCheckin = array();
		$wrongEvent = array();
		$model = JT::model('Checkin');
		$data = array();
		$result = new stdClass;

		foreach ($attendeeIds as $attendeeId)
		{
			if ($versionCheck === false || $versionCheck === 'false')
			{
				$jticketingOrderItemModel = JT::model('OrderItem');
				$orderItemData            = $jticketingOrderItemModel->getItem($attendeeId);
				$attendeeIdFromOrderID    = $orderItemData->attendee_id;

				$jticketingAttendeeFormModel = JT::model('AttendeeForm');
				$EnrollmentData              = $jticketingAttendeeFormModel->getItem($attendeeIdFromOrderID);
				$attendeeId                  = $EnrollmentData->enrollment_id;
			}

			$integration         = JT::getIntegration();
			$data                = array();
			$data['eventid']     = JT::event($eventId, $integration)->integrationId;
			$data['state']       = $state;
			$data['attendee_id'] = $model->getAttendeeID($attendeeId);
			$data['user_id']     = $input->get('user_id', (int) $this->plugin->get('user')->id, 'int');

			$attendeeFormModel = JT::model('AttendeeForm');
			$attendeeData      = $attendeeFormModel->getItem($data['attendee_id']);

			$ticketTypeModel = JT::model('Tickettype');
			$ticketData      = $ticketTypeModel->getItem($attendeeData->ticket_type_id);

			$checkindone = $model->getCheckinStatus($data['attendee_id'], $data['eventid']);

			if ($attendeeData->event_id != $data['eventid'])
			{
				array_push($wrongEvent, $attendeeId);
			}
			elseif ($checkindone)
			{
				array_push($tempDuplicate, $attendeeId);
			}
			else
			{
				if ($model->save($data))
				{
					array_push($tempCheckin, $attendeeId);
				}
				else
				{
					$result->status = 0;
					$result->result = $model->getError();
				}
			}
		}

		$DuplicateCheckIn = '';
		if ($tempDuplicate)
		{
			$DuplicateCheckIn = Text::_('COM_JTICKETING_CHECKIN_DUPLICATE') . ' ' . implode(' ', $tempDuplicate);
			$result->status = 0;
			$result->result = $DuplicateCheckIn;
		}

		if ($wrongEvent)
		{
			$result->status = 0;
			$result->result = Text::_('COM_JTICKETING_CHECKIN_WRONG_EVENT');
		}

		if ($tempCheckin)
		{
			$tempCheckinRes = sprintf(Text::_('COM_JTICKETING_CHECKIN_SUCCESS'), implode(' ', $tempCheckin), $ticketData->title);

			$result->status = 1;
			$result->result = $tempCheckinRes . '  ' . $DuplicateCheckIn;
		}

		if (!isset($result->result))
		{
			$result->status = 0;
			$result->result = Text::_('COM_JTICKETING_SOMETHING_WENT_WRONG');
		}

		$this->plugin->setResponse($result);
	}
}
