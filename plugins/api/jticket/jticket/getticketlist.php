<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Class for getting ticket list which are chekin or not checkin
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceGetticketlist extends ApiResource
{
	/**
	 * Get Ticket list
	 *
	 * @return  json ticket list list
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$lang      = Factory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir  = JPATH_SITE;
		$input     = Factory::getApplication()->input;
		$lang->load($extension, $base_dir);

		$eventid              = $input->get('eventid', '0', 'INT');
		$var['attendtype']    = $input->get('attendtype', 'all', 'STRING');
		$var['tickettypeid']  = $input->get('tickettypeid', '0', 'INT');
		$limitstart         = $input->get('limitstart', 0, 'INT');
		$limit              = $input->get('limit', 0, 'INT');
		$attendeeModel = JT::model('attendees', array("ignore_request" => true));
		$attendeeModel->setState('filter.events', $eventid);
		$attendeeModel->setState('filter.status', 'A');
		$attendeeModel->setState('list.limit', $limit);
		$attendeeModel->setState('list.start', $limitstart);
		$attendeeModel->setState('callFromApi', '1');

		$results = $attendeeModel->getItems();

		$res                  =	new stdClass;
		$res->result          = array();
		$res->empty_message   = '';

		if (empty($results))
		{
			$res->empty_message = Text::_("COM_JTICKETING_INVALID_EVENT");

			return $this->plugin->setResponse($res);
		}

		$table = JT::table("integrationxref");
		$table->load(array("eventid" => $eventid));

		$event		  = JT::event()->loadByIntegration($table->id);
		$eventCreator = $event->getCreator();

		$apiUserId = (int) $this->plugin->get('user')->id;
		$user      = Factory::getUser($apiUserId);
		$isAdmin   = $user->authorise('core.admin');

		if (($apiUserId != $eventCreator) && empty($isAdmin))
		{
			$res->empty_message = Text::_("COM_JTICKETING_NOT_AUTHORIED");

			return $this->plugin->setResponse($res);
		}

		if ($eventid)
		{
			$data = array();

			foreach ($results as $result)
			{
				if ($eventid == $result->event_id && $result->order_status == 'C')
				{
					$obj          = new stdClass;

					$obj->checkin = $result->checkin;

					if (empty($result->checkin) && $result->checkin == 0)
					{
						$obj->checkin = 0;
					}

					$attendee_nm = '';

					if (!empty($result->fname))
					{
						$attendee_nm = ucfirst($result->fname) . ' ' . ucfirst($result->lname);
					}
					elseif (!empty($result->firstname))
					{
						$attendee_nm = htmlspecialchars($result->firstname . ' ' . $result->lastname);
					}

					$attendee_email = '';

					if (!empty($result->email))
					{
						$attendee_email = $result->email;
					}

					$obj->ticketid          = $result->enrollment_id;
					$obj->attendee_nm       = $attendee_nm;
					$obj->ticket_type_title = $result->ticket_type_title;
					$obj->event_title       = $result->title;
					$obj->email   			= $attendee_email;

					if ($var['attendtype'] == "all")
					{
						$data[] = $obj;
					}

					elseif ($var['attendtype'] == "attended" && $obj->checkin == 1)
					{
						$data[] = $obj;
					}
					elseif ($var['attendtype'] == "notattended" && $obj->checkin == 0)
					{
						$data[] = $obj;
					}
				}
			}

			if ($data)
			{
				$res->result    = $data;
			}
			else
			{
				$res->empty_message = Text::_("COM_JTICKETING_NO_EVENT_DATA_USER");
			}
		}
		else
		{
			$res->err_message = Text::_("COM_JTICKETING_INVALID_EVENT");
		}

		$this->plugin->setResponse($res);
	}

	/**
	 * Post Method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}

	/**
	 * Put method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function put()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}

	/**
	 * Delete method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}
}
