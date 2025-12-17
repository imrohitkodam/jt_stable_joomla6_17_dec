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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class for getting ticket list which are chekin or not checkin
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceGeteventdetails extends ApiResource
{
	/**
	 * Get Event details based on event id
	 *
	 * @return  json event details
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$com_params           = ComponentHelper::getParams('com_jticketing');
		$integration          = $com_params->get('integration');
		$lang                 = Factory::getLanguage();
		$extension            = 'com_jticketing';
		$base_dir             = JPATH_SITE;
		$jticketingmainhelper = new jticketingmainhelper;
		$lang->load($extension, $base_dir);
		$input   = Factory::getApplication()->input;
		$eventid = $input->get('eventid', '0', 'INT');
		$userid  = $input->get('userid', '', 'INT');

		$res					=	new stdClass;
		$res->result = array();
		$res->empty_message = '';

		if (empty($eventid))
		{
			$res->empty_message = Text::_("COM_JTICKETING_INVALID_EVENT");

			return $this->plugin->setResponse($res);
		}

		$integrationSource = JT::getIntegration();
		$eventClass        = JT::event($eventid, $integrationSource);

		$jticketingEventModel = JT::model('EventForm');
		$eventDetails         = $jticketingEventModel->getItem($eventid);

		$jticketingXrefModel = JT::model('Integrationxref');
		$eventXrefDetails    = $jticketingXrefModel->getItem($eventClass->integrationId);

		$eventInfo[0]['id']              = $eventClass->getId();
		$eventInfo[0]['title']           = $eventClass->getTitle();
		$eventInfo[0]['description']     = $eventClass->long_description ? $eventClass->long_description : '';
		$eventInfo[0]['book_start_date'] = ($eventClass->booking_start_date) ? $eventClass->booking_start_date : '';
		$eventInfo[0]['book_end_date']   = ($eventClass->booking_end_date) ? $eventClass->booking_end_date : '';
		$value = HTMLHelper::date($eventClass->getStartDate(), 'l, jS F Y', true);
		$eventInfo[0]['startdate']       = $value;
		$value = HTMLHelper::date($eventClass->getEndDate(), 'l, jS F Y', true);
		$eventInfo[0]['enddate']         = $value;
		$eventInfo[0]['avatar']          = $eventClass->getAvatar();
		$eventInfo[0]['integrid']        = $eventClass->integrationId;

		if ($eventDetails->venue == "0")
		{
			$eventInfo[0]['location'] = $eventDetails->location;
		}
		else
		{
			$eventInfo[0]['location'] = JT::model('venueform')->getItem($eventDetails->venue)->address;
		}

		if (isset($eventXrefDetails->checkin))
		{
			$eventInfo[0]['checkin'] = $eventXrefDetails->checkin;
		}

		$ticketCount = $jticketingmainhelper->GetTicketcount($eventid);
		$eventInfo[0]['totaltickets'] = (string) $ticketCount;

		$buyersCount   = $eventClass->soldTicketCount();

		if (isset($buyersCount))
		{
			$eventInfo[0]['soldtickets'] = $buyersCount;
		}

		$eventInfo[0] = (object) $eventInfo[0];

		if (!empty($eventInfo))
		{
			$res->result = $eventInfo;
		}
		else
		{
			$res->err_message = Text::_("COM_JTICKETING_NO_EVENT_DATA");
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
