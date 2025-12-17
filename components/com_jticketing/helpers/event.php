<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

// Component Helper
if (file_exists(JPATH_SITE . '/components/com_jticketing/events/order.php')) { require_once JPATH_SITE . '/components/com_jticketing/events/order.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeefields.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeefields.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/models/tickettype.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/tickettype.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jticketing/models/attendeecorefields.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/attendeecorefields.php'; }
if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }

/**
 * JteventHelper
 *
 * @since       1.0
 *
 * @deprecated  2.5.0 use the alternative methods from the libraries
 */
class JteventHelper
{
	public $jtTriggerOrder, $sociallibraryobj;
 
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		// Add social library according to the social integration
		$Params = ComponentHelper::getParams('com_jticketing');
		$socialintegration = $Params->get('integrate_with', 'none');

		// Load main file
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jsocial.php'; }
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php'; }

		if ($socialintegration != 'none')
		{
			if ($socialintegration == 'JomSocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php'; }
			}
			elseif ($socialintegration == 'EasySocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }
			}
		}

		$integrationHelper = JPATH_ROOT . '/components/com_jticketing/helpers/integrations.php';

		if (!class_exists('JTicketingIntegrationsHelper'))
		{
			JLoader::register('JTicketingIntegrationsHelper', $integrationHelper);
			JLoader::load('JTicketingIntegrationsHelper');
		}

		$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $path);
			JLoader::load('Jticketingmainhelper');
		}

		$this->jtTriggerOrder = new JticketingTriggerOrder;
	}

	/**
	 * cancal ordered ticket
	 *
	 * @param   integer  $order_id  order_id
	 *
	 * @deprecated  2.5.0 use the alternative methods from the order libraries
	 *
	 * @return  object void
	 *
	 * @since   1.0
	 */
	public static function cancelTicket($order_id)
	{
		$path                     = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingmainhelper'))
		{
		JLoader::register('jticketingmainhelper', $path);
		JLoader::load('jticketingmainhelper');
		}

		if (!class_exists('jticketingfrontendhelper'))
		{
		JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
		JLoader::load('jticketingfrontendhelper');
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/orders.php';

		/* Check Dependancy and then remove code

		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/controllers/attendee_list.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/attendee_list.php';

		$JticketingModelattendee_List = new JticketingModelattendee_List;
		$JticketingModelattendee_List->cancelTicket($order_id);
		*/

		$paymentHelper = JPATH_SITE . '/components/com_jticketing/models/payment.php';

		if (!class_exists('jticketingModelpayment'))
		{
			JLoader::register('jticketingModelpayment', $paymentHelper);
			JLoader::load('jticketingModelpayment');
		}

		$orderobj = new jticketingModelorders;
		$status    = $orderobj->getOrderStatus($order_id);

		$obj       = new jticketingModelpayment;
		$orderobj->eventsTypesCountIncrease($order_id);
		$orderobj->updateOrderStatus($order_id, 'D', 1);
	}

	/**
	 * Get Social library object
	 *
	 * @param   integer  $integration_option  this may be joomla,jomsocial,Easysocial
	 *
	 * @return  object social library
	 *
	 * @since   1.0
	 *
	 * @deprecated  2.5.0  getJticketSocialLibObj method will be replaced with getJticketSocialLibObj in order's model.
	 */
	public function getJticketSocialLibObj($integration_option = '')
	{
		$jtParams = ComponentHelper::getParams('com_jticketing');
		$integration_option = $jtParams->get('integrate_with', 'none');

		if ($integration_option == 'Community Builder')
		{
			$SocialLibraryObject = new JSocialCB;
		}
		elseif ($integration_option == 'JomSocial')
		{
			$SocialLibraryObject = new JSocialJomsocial;
		}
		elseif ($integration_option == 'Jomwall')
		{
			$SocialLibraryObject = new JSocialJomwall;
		}
		elseif ($integration_option == 'EasySocial')
		{
			$SocialLibraryObject = new JSocialEasysocial;
		}
		elseif ($integration_option == 'none')
		{
			$SocialLibraryObject = new JSocialJoomla;
		}

		return $SocialLibraryObject;
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @param   INT  $xrefid  xrefid
	 *
	 * @deprecated  2.5.0 Moved code in JLIKE native
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateReminderQueue($xrefid ='')
	{
		$db = Factory::getDbo();

		// Delete entries which are present for that reminder and still not sent
		$query = 'SELECT id FROM #__jticketing_queue
			WHERE sent=0';

		if ($xrefid)
		{
			$query .= " and event_id = " . $xrefid;
		}

		$db->setQuery($query);
		$reminder_queue_ids = $db->loadObjectList();

		if (!empty($reminder_queue_ids))
		{
			foreach ($reminder_queue_ids AS $qid)
			{
				// Update entries for existing reminder
				$this->addPendingEntriestoQueue($xrefid, $qid->id);
			}
		}
		else
		{
			$this->addPendingEntriestoQueue($xrefid);
		}
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @param   INT  $xrefid             xrefid
	 * @param   INT  $reminder_queue_id  reminder_queue_id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::model('orders')->addPendingEntriesToQueue(); instead
	 */
	public function addPendingEntriestoQueue($xrefid = '', $reminder_queue_id = '')
	{
		$db     = Factory::getDbo();
		$client = JT::getIntegration();

		/*$events = $jticketingmainhelper->getEvents();

		$query                = "select max(remtypes.days)
		from #__jticketing_reminder_types AS remtypes
		WHERE state=1";

		$db->setQuery($query);
		$days = $db->loadResult();
		$today        = date('Y-m-d');
		$date_expires = strtotime($today. ' +'.$days.'day');
		$date_expires = date('Y-m-d', $date_expires);

		$date_expires_old = strtotime($today. ' -2day');
		$date_expires_old = date('Y-m-d', $date_expires_old);

		$newevent = array();
		$i =0;

		foreach ($events AS $event)
		{

			$evstartdate = $event['startdate'];

			if ($evstartdate >= $date_expires_old and $evstartdate <= $date_expires)
			{
				$newevent[$i] =new stdclass;
				$newevent[$i]->startdate = $evstartdate;
				$newevent[$i]->eventid =  $event['id'];
				$i++;
			}
		}*/

		$jticketingmainhelper = new jticketingmainhelper;
		$query = "SELECT orderd.*,xref.eventid AS eventid
		FROM  #__jticketing_order AS orderd,  #__jticketing_integration_xref AS xref
		WHERE STATUS =  'C'
		AND orderd.event_details_id = xref.id";

		if ($xrefid)
		{
			$query .= " AND xref.id=" . $xrefid;
		}

		if (!empty($eventdt->eventid))
		{
			$query .= " AND xref.eventid=" . $eventdt->eventid;
		}

		$query .= " AND xref.source LIKE '" . $client . "'";

		$db->setQuery($query);
		$orders = $db->loadObjectList();

		if (!empty($orders))
		{
			foreach ($orders AS $orderdata)
			{
				$order = JT::order($orderdata->id);
				$event = JT::event()->loadByIntegration($order->event_details_id);

				// TODO insertion
				$eventData               = array();
				$eventData['eventId']    = $event->getId();
				$eventData['eventTitle'] = $event->getTitle();
				$eventData['startDate']  = $event->getStartDate();
				$eventData['endDate']    = $event->getEndDate();

				// Insert todo or update todo
				$eventData['assigned_to'] = $order->user_id;

				// Delete todo related to that order
				$this->jtTriggerOrder->onOrderStatusChange($order, $eventData);
			}
		}
	}

	/**
	 * Function to idetify passed field hidden or not from component config.
	 *
	 * @param   String  $field_name  Description
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @return void
	 */
	public function filedToShowOrHide($field_name)
	{
		$params       = ComponentHelper::getParams('com_jticketing');
		$creatorfield = array();
		$creatorfield = $params->get('creatorfield');

		$show_selected_fields = $params->get('show_selected_fields');

		if ($show_selected_fields AND (!empty($creatorfield)))
		{
			// If field is hidden & not to show on form
			if (in_array($field_name, $creatorfield))
			{
				return false;
			}
		}

		// If field is to show on form
		return true;
	}

	/**
	 * Get Event Categories description
	 *
	 * @param   String  $firstOption  Description
	 *
	 * @deprecated  2.5.0 use JT::model('events')->getEventCategories(); instead
	 *
	 * @return  array
	 */
	public function getEventCategories($firstOption = '')
	{
		$db = Factory::getDbo();
		$app     = Factory::getApplication();
		$com_params = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($integration == 4)
		{
			$source = 'com_easysocial';
		}

		$cat_options = array();

		if ($source == 'com_jticketing' or  $source == 'com_jevents')
		{
			$categories  = HTMLHelper::_('category.options', $source, array('filter.published' => array(1)));

			if (!empty($categories))
			{
				foreach ($categories as $category)
				{
					if (!empty($category))
					{
						$cat_options[] = HTMLHelper::_('select.option', $category->value, $category->text);
					}
				}
			}
		}
		else
		{
			if ($source == 'com_easysocial')
			{
				$query = "Select id,title FROM #__social_clusters_categories WHERE type LIKE 'event'";
			}

			if ($source == 'com_community')
			{
				$query = "Select id,name AS title FROM #__community_events_category";
			}

			$db->setQuery($query);
			$categories = $db->loadObjectlist();

			if (!empty($categories))
			{
				$cat_options[] = HTMLHelper::_('select.option', "0", Text::_('COM_JTICKETING_FILTER_SELECT_ALL_CATEGORY'));

				foreach ($categories as $category)
				{
					$cat_options[] = HTMLHelper::_('select.option', $category->id, $category->title);
				}
			}
		}

		return $cat_options;
	}

	/**
	 * Get Event Type description
	 *
	 * @return  array
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function getEventType()
	{
		$online_events   = array();
		$online_events[] = HTMLHelper::_('select.option', '', Text::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
		$online_events[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
		$online_events[] = HTMLHelper::_('select.option', '1', Text::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

		return $online_events;
	}

	/**
	 * EventsToShowOptions description
	 *
	 * @return  Array  Options
	 *
	 * @deprecated  3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function eventsToShowOptions()
	{
		$options = array();
		$app     = Factory::getApplication();
		$options[] = HTMLHelper::_('select.option', 'featured', Text::_('COM_JTK_FEATURED_CAMP'));
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_JTK_FILTER_ONGOING'));
		$options[] = HTMLHelper::_('select.option', '-1', Text::_('COM_JTK_FILTER_PAST_EVNTS'));

		return $options;
	}

	/**
	 * SaveCustom Attendee fields description
	 *
	 * @param   Array  $ticket_fields  Tickets fiedls
	 * @param   INT    $eventid        Event Id
	 * @param   INT    $userid         User Id
	 *
	 * @deprecated  2.5.0 use the alternative methods from the attendee libraries
	 *
	 * @return void
	 */
	public function saveCustomAttendee_fields($ticket_fields, $eventid, $userid)
	{
		$integration = JT::getIntegration();
		$XrefID = JT::event($eventid, $integration)->integrationId;

		$db                       = Factory::getDbo();
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$fields_selected          = $fields_in_DB = array();
		$attendee_fields          = $jticketingfrontendhelper->getAllfields($eventid);

		// Firstly Delete Attendee Fields That are Removed
		foreach ($attendee_fields['attendee_fields'] as $atkey => $atvalue)
		{
			if ($atvalue->id)
			{
				$fields_in_DB[] = $atvalue->id;
			}
		}

		$fields_selected[] = '';

		foreach ($ticket_fields AS $key => $value)
		{
			if ($value['id'])
			{
				$fields_selected[] = $value['id'];
			}
		}

		if (!empty($fields_in_DB))
		{
			$diff_ids = array_diff($fields_in_DB, $fields_selected);

			if (!empty($diff_ids))
			{
				$this->delete_Ateendee_fields($diff_ids);
			}
		}

		// Now Insert or Update New Fields
		foreach ($ticket_fields AS $tkey => $tvalue)
		{
			$ticket_field_to_insert = new StdClass;

			foreach ($tvalue AS $ntkey => $nvalue)
			{
				$ticket_field_to_insert->$ntkey = $nvalue;
			}

			$ticket_field_to_insert->eventid = $XrefID;
			$ticket_field_to_insert->state   = 1;

			if (!$ticket_field_to_insert->id)
			{
				// Create Unique Name
				$ticket_field_to_insert->name = $this->CreateField_Name($ticket_field_to_insert->label);

				if ($ticket_field_to_insert->label)
				{
					if (!$db->insertObject('#__jticketing_attendee_fields', $ticket_field_to_insert, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					$tickettypeid = $db->insertid();
				}
			}
			else
			{
				$db->updateObject('#__jticketing_attendee_fields', $ticket_field_to_insert, 'id');
			}
		}
	}

	/**
	 * CreateField_Name description
	 *
	 * @param   String  $string  Description
	 *
	 * @deprecated  2.5.0 use the alternative methods from the libraries
	 *
	 * @return string
	 *
	 * @since  1.0
	 */
	public function CreateField_Name($string)
	{
		$string = strtolower($string);

		// Replaces all spaces with hyphens.
		$string = str_replace(' ', '_', $string);

		// Removes special chars.
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	}

	/**
	 * Delete_Ateendee_fields description
	 *
	 * @param   Array  $delete_ids  Delete attendees ids
	 *
	 * @deprecated  2.5.0 use the alternative methods from the attendee libraries
	 *
	 * @return void
	 */
	public function delete_Ateendee_fields($delete_ids)
	{
		$db = Factory::getDbo();

		foreach ($delete_ids as $key => $value)
		{
			$query = 'DELETE FROM #__jticketing_attendee_fields
				WHERE id = "' . $value . '"';
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}
	}

	/**
	 * Function that allows child controller access to model data
	 *
	 * @param   array  	$integration_ids  array of id of integration xref table
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return   1 or 0
	 *
	 * @since   1.5.1
	 */
	public function delete_Event($integration_ids)
	{
		$db = Factory::getDbo();

		// Delete From universal field values which are saved against that event
		$TjfieldsHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$content_id_array = $integration_ids;
		$TjfieldsHelper   = new TjfieldsHelper;
		$JteventHelper   = new JteventHelper;

		$this->deleteFieldValues($content_id_array, 'com_jticketing.event');

		foreach ($integration_ids AS $xrefid)
		{
			// Find main order
			$query = "SELECT id FROM #__jticketing_order WHERE event_details_id=" . $xrefid;
			$db->setQuery($query);
			$order_ids = $db->loadColumn();

			if (!empty($order_ids))
			{
				foreach ($order_ids AS $oid)
				{
					$query = "SELECT attendee_id FROM #__jticketing_order_items WHERE attendee_id<>0 AND attendee_id<>'' AND order_id=" . $oid;
					$db->setQuery($query);
					$attendee_ids     = $db->loadColumn();

					if (!empty($attendee_ids))
					{
						$attendee_ids_str = implode("','", $attendee_ids);

						// Delete From attendee field values
						$query = "DELETE FROM #__jticketing_attendee_field_values	WHERE attendee_id IN ('" . $attendee_ids_str . "') ";
						$db->setQuery($query);
						$db->execute();

						// Delete From attendees
						$query = "DELETE FROM #__jticketing_attendees	WHERE id IN ('" . $attendee_ids_str . "') ";
						$db->setQuery($query);
						$db->execute();
					}

					// Delete From order items
					$query = "SELECT id FROM #__jticketing_order_items WHERE order_id=" . $oid;
					$db->setQuery($query);
					$order_items_id     = $db->loadColumn();
					$order_items_id_str = implode("','", $order_items_id);

					$query = "DELETE FROM #__jticketing_order_items	WHERE id IN ('" . $order_items_id_str . "') ";
					$db->setQuery($query);
					$db->execute();
				}
			}

			// Delete From attendee fields per event
			$query = "DELETE FROM #__jticketing_attendee_fields	WHERE  eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete Ticket Types
			$query = "DELETE FROM #__jticketing_types	WHERE  eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From Checkin Details Table
			$query = "DELETE FROM #__jticketing_checkindetails	WHERE eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From order table
			$query = "DELETE FROM #__jticketing_order	WHERE event_details_id=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From xref table
			$query = "DELETE FROM #__jticketing_integration_xref	WHERE id=" . $xrefid;
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * UpdatePaypalEmail description
	 *
	 * @param   INT     $userid        UserId
	 * @param   String  $paypal_email  Email
	 *
	 * @deprecated  2.5.0 use the alternative methods from the order libraries
	 *
	 * @return void
	 */
	public function updatePaypalEmail($userid, $paypal_email)
	{
		$db    = Factory::getDbo();
		$paypal_email = trim($paypal_email);

		if (!empty($paypal_email))
		{
			$query = "UPDATE #__jticketing_integration_xref SET paypal_email='" . $paypal_email . "' WHERE userid=" . $userid;
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * FixavailableSeats description
	 *
	 * @param   INT     $available_current    Current available tickets
	 * @param   Object  $ticket_type_info_db  Ticket info
	 * @param   INT     $xrefid               Xref id
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return void
	 */
	public function fixavailableSeats($available_current, $ticket_type_info_db, $xrefid)
	{
		$db         = Factory::getDbo();
		$difference = 0;
		$difference = $available_current - $ticket_type_info_db->count;

		$query = "SELECT id from #__jticketing_order WHERE status LIKE 'C' AND event_details_id=" . $xrefid;
		$db->setQuery($query);
		$orders    = $db->loadObjectlist();
		$soldcount = '';

		if ($orders)
		{
			$soldcounts = '';

			foreach ($orders AS $order)
			{
				$soldres = '';
				$query   = "SELECT count(id) from #__jticketing_order_items WHERE order_id=" . $order->id . " AND type_id=" . $ticket_type_info_db->id;
				$db->setQuery($query);
				$soldres = $db->loadResult();

				if ($soldres)
				{
					$soldcounts[] = $soldres;
				}
			}

			$finalsoldcount = 0;

			foreach ($soldcounts AS $soldcount)
			{
				$finalsoldcount = $finalsoldcount + $soldcount;
			}

			$available_current = $available_current + $finalsoldcount;
		}
		else
		{
			if ($difference > 0)
			{
				$available_current = $ticket_type_info_db->count + $difference;
			}
			elseif ($difference < 0)
			{
				$positive_diff = ($difference * (-1));

				if ($ticket_type_info_db->count != 0)
				{
					$final_diff = $ticket_type_info_db->count - $positive_diff;

					// Do not make available as 0 since it will becomes unlimited seats
					if ($final_diff != 0)
					{
						$available_current = $ticket_type_info_db->count - $positive_diff;
					}
				}
			}
		}

		return $available_current;
	}

	/**
	 * SaveEvent description
	 *
	 * @param   INT     $eventid              Event Id
	 * @param   INT     $backend_integration  Integration set
	 * @param   String  $ev_creator           Event creator
	 * @param   int     $ticketAllow          Ticket Allow
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return false
	 */
	public function saveEvent($eventid, $backend_integration = 1, $ev_creator = '', $ticketAllow = '')
	{
		$jteventHelper      = new jteventHelper;
		$integrationsHelper = new JTicketingIntegrationsHelper;

		$app = Factory::getApplication();
		$input = $app->input;
		$userName = Factory::getUser();
		$post  = $input->post;
		$this->onLoadJTclasses();
		$source = array(
			1 => 'com_community',
			2 => 'com_jticketing',
			3 => 'com_jevents',
			4 => 'com_easysocial'
		);

		// Get creator of event
		if (empty($ev_creator))
		{
			$userid = Factory::getUser()->id;
		}

		// This is the case when the other user edits event not belonging to him. Maintaining creator for the same vendor ID
		if (!empty(JT::event($eventid)->getId()))
		{
			$userid = JT::event($eventid)->getCreator();
		}

		$dat                            = new Stdclass;
		$dat->source                    = $source[$backend_integration];
		$dat->userid                    = $userid;
		$com_params                     = ComponentHelper::getParams('com_jticketing');
		$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');
		$attendee_fields                = $post->get('attendeefields', '', 'array');
		$ticket_fields                  = $post->get('tickettypes', '', 'array');
		$eventLevelStartNumber          = $post->get('start_number_for_event_level_sequence', '', 'STRING');
		$frontHelper                    = new Jticketingfrontendhelper;
		$jteventHelper                  = new jteventHelper;

		$totalSeats = 0;

		// Jomsocial
		if ($backend_integration == 1)
		{
			$totalSeats = JT::event($eventid, 'com_community')->getEventTotalSeats();
		}
		// Easysocial
		elseif ($backend_integration == 4)
		{
			$totalSeats = JT::event($eventid, 'com_easysocial')->getEventTotalSeats();
		}

		$xrefData = array();

		$tjvendorFrontHelper = new tjvendorFrontHelper;
		$getVendorId = $tjvendorFrontHelper->checkVendor($dat->userid, 'com_jticketing');

		if (empty($getVendorId))
		{
			$vendorPermission = JT::event()->checkVendorPermission($dat->userid);

			if (!$vendorPermission)
			{
				if ($app->isClient("administrator")) 
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_CREATE_VENDOR_PERMISSION_ERROR'), 'error');
				} 
				else 
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_VENDOR__NOT_EXIST_ERROR'), 'error');
				}

				return false;
			}

			$vendorData['vendor_client'] = 'com_jticketing';
			$vendorData['user_id']       = $userid;
			$vendorData['vendor_title']  = $userName->name;
			$vendorData['state']         = "1";
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
			$TjvendorsModelVendors = BaseDatabaseModel::getInstance('Vendor', 'TjvendorsModel');

			$TjvendorsModelVendors->save($vendorData);
			$xrefData['vendor_id'] = $tjvendorFrontHelper->checkVendor($userid, 'com_jticketing');
		}
		else
		{
			$xrefData['vendor_id'] = $getVendorId;
		}

		if ($eventid)
		{
			$xrefData['eventid'] = $eventid;
			$xrefData['source'] = $dat->source;
			$xrefData['userid'] = $dat->userid;
			$xrefData['enable_ticket'] = (int) $ticketAllow;
			$xrefId = JT::event($xrefData['eventid'], $dat->source)->integrationId;
			$xrefData['id'] = $xrefId;
			$jticketingModelIntegrationXref = JT::model('Integrationxref', array('ignore_request' => true));
			if (!empty($xrefId))
			{
				$xrefData['id'] = $xrefId;
			}
			else
			{
				$xrefData['id'] = '';
			}

			if (empty($xrefId->eventid))
			{
				$jticketingModelIntegrationXref->save($xrefData);
			}
		}

		$xrefId = empty($xrefId) ?
					(int) $jticketingModelIntegrationXref->getState(
						$jticketingModelIntegrationXref->getName() . '.id'
					) : $xrefId;

		// Save Attendee fields
		$attendeeFields = $attendee_fields;
		$attendeeFieldsModel = JT::model('Attendeefields');
		$attendeeCoreFieldsModel = JT::model('AttendeeCoreFields');
		$existingAttendeeFields = $attendeeCoreFieldsModel->getAttendeeFields($xrefId);
		$attendeeFieldsArray = array();
		$newAttendeeField = array();
		$existingId = array();
		$newCount = 0;
		$existingCount = 0;

		// Saving new Attendee fields
		foreach ($attendeeFields as $attendeeField)
		{
			if (!empty($attendeeField['id']))
			{
				$attendeeFieldsArray['id'] = $attendeeField['id'];
			}
			else
			{
				$attendeeFieldsArray['id'] = '';
			}

			$attendeeFieldsArray['label'] = $attendeeField['label'];
			$attendeeFieldsArray['type'] = $attendeeField['type'];
			$attendeeFieldsArray['core'] = 0;
			$attendeeFieldsArray['default_selected_option'] = $attendeeField['default_selected_option'];
			$attendeeFieldsArray['required'] = $attendeeField['required'];
			$attendeeFieldsArray['eventid'] = $xrefId;
			$attendeeFieldsArray['state'] = 1;

			if (!empty($attendeeFieldsArray['label']))
			{
				$string = strtolower($attendeeField['label']);
				$string = str_replace(' ', '_', $string);
				$attendeeFieldsArray['name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
				$return = $attendeeFieldsModel->save($attendeeFieldsArray);
			}
		}

		// Collecting existing attendee fields
		foreach ($existingAttendeeFields as $existingAttendeeField)
		{
			$existingId[$existingCount] = $existingAttendeeField['id'];
			$existingCount++;

			foreach ($attendeeFields as $attendeeField)
			{
				if ($attendeeField['id'] == $existingAttendeeField['id'])
				{
					$newAttendeeField[$newCount] = $attendeeField['id'];
					$newCount++;
				}
			}
		}

		// Collecting attendee fields to be deleted
		$invalidAttendeeFieldIds = array_diff($existingId, $newAttendeeField);

		// Deleting attendee fields
		foreach ($invalidAttendeeFieldIds as $invalidId)
		{
			// A check to see if this particular attendee field has any attendee field values against it.
			$attendeeFieldCheck = $attendeeFieldsModel->checkAttendeeFieldValue($invalidId);

			if (empty($attendeeFieldCheck))
			{
				$attendeeFieldsModel->delete($invalidId);
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_ATENDEE_FIELDS_DELETE_ERROR'), 'warning');

				return false;
			}
		}

		$ticketTypes = $ticket_fields;
		$ticketTypeModel = JT::model('Tickettype');
		$existingTicketTypes = $ticketTypeModel->getTicketTypes($xrefId);
		$totalTickets = 0;

		foreach ($ticket_fields as $ticketField)
		{
			if (!empty($ticketField['count']) && $ticketField['count'] > 0 && empty($ticketField['unlimited_seats']) && !empty($ticketField['id']))
			{
				$totalTickets = $totalTickets + $ticketField['count'];
			}

			if (!empty($ticketField['available']) && $ticketField['available'] > 0 && empty($ticketField['unlimited_seats']) && empty($ticketField['id']))
			{
				$totalTickets = $totalTickets + $ticketField['available'];
			}
		}

		$tickets = array();
		$newTicketType = array();
		$existingId = array();
		$newCount = 0;
		$existingCount = 0;
		$totalTicketsCount = 0;

		if ($totalTickets <= $totalSeats || empty($totalSeats))
		{
			if ($com_params->get('entry_number_assignment', 0,'INT'))
			{
				// check start number of sequence in event level input only number and alphabetical present
				if ($eventLevelStartNumber && !preg_match('/^[a-zA-Z0-9]+$/', $eventLevelStartNumber))
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_EVENT_LEVEL_SEQUENCE') , 'error');

					return false;
				}

				// check start number of sequence in ticket level input only number and alphabetical present
				foreach ($ticketTypes as $key => $tickettype) {
					if(isset($tickettype['allow_ticket_level_sequence']) && $tickettype['allow_ticket_level_sequence']) {
						if ($tickettype['start_number_for_sequence'] && !preg_match('/^[a-zA-Z0-9]+$/', $tickettype['start_number_for_sequence']))
						{
							$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_START_NUMBER_FOR_SEQUENCE') , 'error');

							return false;
						}
					}
				}
			}

			// Saving new ticket types
			foreach ($ticketTypes as $ticketType)
			{
				if (!empty($ticketAllow) && empty($ticketType['title']))
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_TICKET_TITLE_EMPTY'), 'error');

					return false;
				}

				if (empty($ticketType['available']) && $ticketType['unlimited_seats'] == 0)
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_TICKET_SEATS_EMPTY'), 'error');

					return false;
				}

				// check start number of maximum ticket per order input only number
				if ($ticketType['maximum_ticket_per_order'] && !preg_match('/^[0-9]+$/', $ticketType['maximum_ticket_per_order']))
				{
					$app->enqueueMessage(Text::_('COM_JTICKETING_INVALID_FIELD') . ': ' .Text::_('COM_JTICKETING_TICKET_LEVEL_MAXIMUM_TICKET_PER_ORDER') , 'error');

					return false;
				}

				if (!empty($ticketType['title']))
				{
					if (!empty($ticketType['id']))
					{
						$tickets['id'] = $ticketType['id'];

						// Ticket count should be remaining tickets.
						if (is_numeric($ticketType['count']))
						{
							$tickets['count'] = $ticketType['count'];
						}
					}
					else
					{
						$tickets['id'] = '';
						$tickets['count'] = $ticketType['available'];

						if (!empty($ticketType['count']))
						{
							$tickets['count'] = $ticketType['count'];
						}
					}

					$tickets['title'] = $ticketType['title'];
					$tickets['desc'] = $ticketType['desc'];

					if (!empty($ticketType['ticket_enddate']))
					{
						$config = Factory::getConfig();
						$user = Factory::getUser($userid);
						$ticketEndDt = Factory::getDate($ticketType['ticket_enddate'], $user->getParam('timezone', $config->get('offset')));
						$ticketEndDt->setTimezone(new DateTimeZone('UTC'));
						$ticketEndDt = $ticketEndDt->toSql(true);

						$tickets['ticket_enddate'] = $ticketEndDt;
					}
					else
					{
						$tickets['ticket_enddate'] = '';
					}

					if (isset($ticketType['ticket_startdate']) && !empty($ticketType['ticket_startdate']))
					{
						$config = Factory::getConfig();
						$user = Factory::getUser($userid);
						$ticketEndDt = Factory::getDate($ticketType['ticket_startdate'], $user->getParam('timezone', $config->get('offset')));
						$ticketEndDt->setTimezone(new DateTimeZone('UTC'));
						$ticketEndDt = $ticketEndDt->toSql(true);

						$tickets['ticket_startdate'] = $ticketEndDt;
					}
					else 
					{
						$tickets['ticket_startdate'] = '';
					}

					$tickets['unlimited_seats'] = $ticketType['unlimited_seats'];
					$tickets['available'] = $ticketType['available'];

					// If ticket count is 0 while editing ticket type
					if (!empty($ticketType['id']) && empty($ticketType['count']) && $ticketType['unlimited_seats'] == 0)
					{
						$ticketDetails = $ticketTypeModel->getItem($ticketType['id']);

						if ($ticketDetails->available < $ticketType['available'])
						{
							$tickets['count'] = $ticketType['available'] - $ticketDetails->available;
						}
						elseif($ticketDetails->available != $ticketType['available'])
						{
							$ticketType['available'] = $ticketDetails->available;

							$app->enqueueMessage(Text::_('COM_JTICKETING_TICKET_REMAINING_SEATS_COUNT_ERROR'));

							return false;
						}
					}

					// If remaining ticket count available while editing ticket type
					if (!empty($ticketType['id']) && !empty($ticketType['count']) && $ticketType['unlimited_seats'] == 0)
					{
						$ticketDetails = $ticketTypeModel->getItem($ticketType['id']);

						if ($ticketDetails->available < $ticketType['available'])
						{
							$tickets['count'] = ($ticketType['available'] - $ticketDetails->available) + $ticketDetails->count;
						}
						elseif($ticketDetails->available > $ticketType['available'] && $ticketDetails->count < $ticketType['available'])
						{
							$tickets['count'] = $ticketDetails->count - ($ticketDetails->available - $ticketType['available']);
						}
						elseif ($ticketDetails->available > $ticketType['available'] && $ticketDetails->count > $ticketType['available'])
						{
							$tickets['count'] = $ticketDetails->count - ($ticketDetails->available - $ticketType['available']);
						}
					}

					$tickets['state'] = $ticketType['state'];
					$tickets['price'] = $ticketType['price'];
					$tickets['max_ticket_per_order'] = $ticketType['max_ticket_per_order'];

					if ((int) $com_params->get('show_access_level') === 0)
					{
						$tickets['access'] = $com_params->get('default_accesslevels', '1');
					}
					else
					{
						$tickets['access'] = $ticketType['access'];
					}

					if ($com_params->get('entry_number_assignment', 0,'INT'))
					{
						if (isset($ticketType['allow_ticket_level_sequence']) && $ticketType['allow_ticket_level_sequence'])
						{
							$tickets['allow_ticket_level_sequence'] = 1;
							$tickets['start_number_for_sequence'] = $ticketType['start_number_for_sequence'] ? $ticketType['start_number_for_sequence'] : '1';
						}
						else
						{
							$tickets['allow_ticket_level_sequence'] = 0;
							$tickets['start_number_for_sequence'] = $eventLevelStartNumber ? $eventLevelStartNumber : '1';
						}
					}
					else
					{
						$tickets['start_number_for_sequence'] = 0;
						$tickets['allow_ticket_level_sequence'] = 0;
					}

					$tickets['eventid'] = $xrefId;
					$totalTicketsCount++;

					if (!$ticketTypeModel->save($tickets))
					{
						return false;
					}
				}
			}
		}
		else
		{
			if ($backend_integration == 1)
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_JOMSOCIAL_EVENT_TICKET_TYPES_SAVE_ERROR'), 'error');
			}
			elseif ($backend_integration == 4)
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_EASYSOCIAL_EVENT_TICKET_TYPES_SAVE_ERROR'), 'error');
			}

			return false;
		}

		// For jevent ticket_enable if ticket is present
		if (($backend_integration == '3' || $backend_integration == '1') && $totalTicketsCount)
		{
			$xrefData['enable_ticket'] = 1;
			$xrefData['id'] = $xrefId;
			$jticketingModelIntegrationXref->save($xrefData);
		}

		// Collecting existing ticket types
		foreach ($existingTicketTypes as $existingTicketType)
		{
			$existingId[$existingCount] = $existingTicketType['id'];
			$existingCount++;

			foreach ($ticketTypes as $ticketType)
			{
				if ($ticketType['id'] == $existingTicketType['id'])
				{
					$newTicketType[$newCount] = $ticketType['id'];
					$newCount++;
				}
			}
		}

		// Collecting ticket types to be deleted
		$invalidTicketTypeIds = array_diff($existingId, $newTicketType);

		// Deleting ticket types
		foreach ($invalidTicketTypeIds as $invalidId)
		{
			// A check to see if this particular ticket type has any orders against it.
			$ticketOrder = $ticketTypeModel->checkOrderExistsTicketType($invalidId);

			if (empty($ticketOrder))
			{
				$ticketTypeModel->delete($invalidId);
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_TICKET_TYPES_DELETE_ERROR'), 'warning');

				return false;
			}
		}
	}

	/**
	 * Saveintegration description
	 *
	 * @param   INT  $eventid  Event id
	 * @param   INT  $dat      Description
	 *
	 * @return  INT  Integration xref if
	 *
	 * @deprecated  3.2.0 use the alternative methods from the event libraries
	 */
	public function saveintegration($eventid, $dat)
	{
		$db                        = Factory::getDbo();
		$integration               = new stdClass;
		$integration->id           = '';
		$integration->eventid      = $eventid;
		$integration->source       = $dat->source;
		$integration->paypal_email = $dat->paypal_email;
		$integration->userid       = $dat->userid;

		if (!$db->insertObject('#__jticketing_integration_xref', $integration, 'id'))
		{
			return false;
		}
		else
		{
			return $db->insertid();
		}
	}

	/**
	 * Updateintegration description
	 *
	 * @param   INT     $xrefid  Description
	 * @param   STRING  $dat     Description
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return  void
	 */
	public function updateintegration($xrefid, $dat)
	{
		$db                        = Factory::getDbo();
		$integration               = new stdClass;
		$integration->id           = $xrefid;

		// If (!empty($dat->paypal_email))
		{
			$integration->paypal_email = $dat->paypal_email;
		}

		$db->updateObject('#__jticketing_integration_xref', $integration, 'id');

		return $xrefid;
	}

	/**
	 * onLoadJTclasses description
	 *
	 * @deprecated  2.5.0 use the alternative methods from bootstrap file
	 *
	 * @return void
	 */
	public function onLoadJTclasses()
	{
		// Load all required helpers.
		$jticketingmainhelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $jticketingmainhelperPath);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jteventHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}
	}

	/**
	 * Validate JomSocial integration.
	 *
	 * @param   String  $backend_integration  Integration set
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return  Boolean  Depend on the result
	 */
	public function onJtValidateIntegration($backend_integration)
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration != $backend_integration)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete field values in tjfields table
	 *
	 * @param   array   $content_id_array  array of content ID
	 * @param   string  $client            Client Name()
	 *
	 * @return  void
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function deleteFieldValues($content_id_array,$client)
	{
		$db = Factory::getDbo();

		if (!empty($content_id_array))
		{
			$content_id_str = implode("','", $content_id_array);
			$query = "DELETE FROM #__tjfields_fields_value
			WHERE  content_id IN ('" . $content_id_str . "') AND client LIKE '" . $client . "'";
			$db->setQuery($query);

			if (!$db->execute())
			{
			}
		}
	}

	/**
	 * Function to get specific col of specific event
	 *
	 * @param   int  $event_id       id of event
	 * @param   ARR  $columns_array  array of teh columns
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return  Object  $statusDetails
	 *
	 * @since  1.0.0
	 */
	public function getEventColumn($event_id,$columns_array)
	{
		if (empty($event_id))
		{
			return;
		}

		$db   = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select($columns_array);
		$query->from($db->quoteName('#__jticketing_events'));
		$query->where($db->quoteName('id') . " = " . (int) $event_id);

		$db->setQuery($query);
		$event = $db->loadObject();

		return $event;
	}

	/**
	 * Function getCoordinates
	 *
	 * @param   int     $id     id of event
	 * @param   string  $venue  array of teh columns
	 *
	 * @deprecated  2.5.0 use the alternative methods from the venue libraries
	 *
	 * @return  Array|boolean  on sucess data object and on failure flase
	 *
	 * @since  1.0.0
	 */
	public function getCoordinates($id, $venue)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($venue == '0')
		{
			$query->select($db->quoteName('location'));
			$query->from($db->quoteName('#__jticketing_events'));
			$query->where($db->quoteName('id') . ' = ' . (int) $id);
		}
		else
		{
			$query->select($db->quoteName('address'));
			$query->from($db->quoteName('#__jticketing_venues'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($venue));
		}

		$db->setQuery($query);
		$res = $db->loadResult();
		$string = str_replace(",", "+", $res);
		$array = explode(" ", $string);
		$address = implode($array);
		$request = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=" . $address . "&sensor=false");
		$decodedCoOrdinates = json_decode($request);

		if (!empty($decodedCoOrdinates->results[0]))
		{
			$coOrdinates = array();
			$coOrdinates['latitude'] = $decodedCoOrdinates->results[0]->geometry->location->lat;
			$coOrdinates['longitude'] = $decodedCoOrdinates->results[0]->geometry->location->lng;

			return $coOrdinates;
		}

		return false;
	}

	/**
	 * function for generating ICS file.
	 *
	 * @param   integer  $data  data of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->generateIcs($data); instead
	 */
	public static function generateIcs($data)
	{
		ob_start();
		include JPATH_SITE . '/components/com_jticketing/views/event/tmpl/eventIcs.php';
		$html .= ob_get_contents();
		ob_end_clean();

		$file    = str_replace(" ", "_", $data['title']);
		$file    = str_replace("/", "", $data['title']);
		$file = preg_replace('/\s+/', '', $file);
		$icsFileName = $file . "" . $data['created_by'] . ".ics";
		$icsname = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . $icsFileName;
		$file    = fopen($icsname, "w");

		if (!empty($file))
		{
			fwrite($file, $html);
			fclose($file);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $data  data of event
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->deleteIcs($data); instead
	 */
	public static function deleteIcs($data)
	{
		$file = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . preg_replace('/\s+/', '', $data['eventTitle'] . '' . $data['createdBy'] . '.ics');

		if (!($file))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_EVENT_NO_FILE_FOUND'));
		}
		elseif (unlink($file))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTICKETING_MEDIA_FILE_DELETED'));
		}
	}

	/**
	 * Get recommend user for the course.
	 *
	 * @param   int  $courseId  id of course
	 * @param   int  $userId    id of user
	 *
	 * @return  ARRAY $record
	 *
	 * @since   1.0.0
	 * @deprecated  3.2.0  Use JT::model('Enrollment')->getuserRecommendedUsers($courseId, $userId); instead.
	 */
	public function getuserRecommendedUsers($courseId, $userId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('lr.assigned_to');
		$query->from('`#__jlike_todos` as lr');
		$query->join('INNER', '`#__jlike_content` as lc ON lc.id=lr.content_id');
		$query->where('lr.assigned_by=' . (int) $userId);
		$query->where('lr.type="reco"');
		$query->where('lc.element_id=' . (int) $courseId . ' LIMIT 0,5');
		$orderModel = JT::model('order');

		// Set the query for execution.
		$db->setQuery($query);

		$recommendedusers = $db->loadColumn();

		foreach ($recommendedusers as $index => $recommend_userid)
		{
			$this->sociallibraryobj = $orderModel->getJticketSocialLibObj();
			$recommendedusers[$index] = new stdClass;
			$student = Factory::getUser($recommend_userid);
			$recommendedusers[$index]->username = Factory::getUser($recommend_userid)->username;
			$recommendedusers[$index]->name = Factory::getUser($recommend_userid)->name;
			$recommendedusers[$index]->avatar = $this->sociallibraryobj->getAvatar($student, 50);

			$link = '';

			if ($this->sociallibraryobj->getProfileUrl($student))
			{
				$link = Uri::root() . substr(Route::_($this->sociallibraryobj->getProfileUrl($student)), strlen(Uri::base(true)) + 1);
			}

			$recommendedusers[$index]->profileurl = $link;
		}

		return $recommendedusers;
	}

	/**
	 * Method getEventAttendeeInfo
	 *
	 * @param   integer  $eventId      event Id
	 * @param   integer  $limit_start  limit start value
	 * @param   integer  $limit        limit
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @return	array|boolean
	 *
	 * @since	1.6
	 */
	public function getEventAttendeeInfo($eventId, $limit_start = 0, $limit = null)
	{
		if ($eventId)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			$columns = array('attendee.id', 'attendee.event_id', 'attendee.status', 'attendee.owner_id',
			'attendee.owner_email', 'user.firstname',
			'user.lastname', 'users.username'
			);
			$query->select($db->quoteName($columns));
			$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));

			$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'intxref')
			. ' ON (' . $db->quoteName('intxref.id') . ' = ' . $db->quoteName('attendee.event_id') . ')');

			$query->join('LEFT', $db->qn('#__jticketing_order_items', 'oitem') . 'ON (' . $db->qn('oitem.attendee_id')
				. ' = ' . $db->qn('attendee.id') . ')');

			$query->join('LEFT', $db->qn('#__jticketing_order', 'order') . 'ON (' . $db->qn('order.id')
				. ' = ' . $db->qn('oitem.order_id') . ')');

			$query->join('LEFT', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('user.order_id') . ' = ' . $db->qn('order.id') . ')');
			$query->select('IF(attendee.owner_id=0,order.name,users.name) as name');
			$query->join('LEFT', $db->quoteName('#__users', 'users') . 'ON ( ' . $db->quoteName('users.id') . '=' . $db->quoteName('attendee.owner_id') . ')');
			$query->where($db->qn('intxref.eventid') . ' = ' . $db->quote($eventId));
			$query->where($db->qn('attendee.status') . ' = ' . $db->quote('A'));
			$query->order($db->qn('order.id') . 'DESC');

			if ($limit != null)
			{
				$query->setLimit($limit, $limit_start);
			}

			$db->setQuery($query);
			$results = $db->loadObjectList();

			return $results;
		}

		return false;
	}

	/**
	 * Method to get Buyers count
	 *
	 * @param   integer  $id  event id
	 *
	 * @deprecated  3.2.0 Use JT::event($eventid, $integration)->soldTicketCount(); instead
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 */
	public function getBuyersCount($id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('count(attendee.id) AS buyersCount');
		$query->from($db->quoteName('#__jticketing_attendees', 'attendee'));
		$query->where($db->quoteName('attendee.event_id') . ' = ' . $db->quote($id));
		$query->where($db->quoteName('attendee.status') . ' = ' . $db->quote('A'));

		$db->setQuery($query);
		$buyersCount = $db->loadResult();

		return $buyersCount;
	}

	/**
	 * Get event details
	 *
	 * @param   integer  $eventId  Event id
	 *
	 * @return  Array
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event libraries
	 *
	 * @since   1.0
	 */
	public function getEventData($eventId)
	{
		if (!empty($eventId))
		{
			$integration = JT::getIntegration(true);
			$db          = Factory::getDbo();

			$query = $db->getQuery(true);

			$query->select('*');

			if ($integration == 1)
			{
				$query->from($db->quoteName('#__community_events'));
				$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
				$query->where($db->quoteName('published') . ' = 1');
			}
			elseif ($integration == 2)
			{
				$query->from($db->quoteName('#__jticketing_events'));
				$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
				$query->where($db->quoteName('state') . ' = 1');
			}
			elseif ($integration == 3)
			{
				$query->select($db->quoteName('jevent.ev_id', 'id'));
				$query->select($db->quoteName('jevdet.summary', 'title'));
				$query->from($db->quoteName('#__jevents_vevdetail', 'jevdet'));
				$query->join('LEFT', $db->quoteName('#__jevents_vevent', 'jevent') .
				' ON (' . $db->quoteName('jevent.detail_id') . ' = ' . $db->quoteName('jevdet.evdet_id') . ')');
				$query->where($db->quoteName('jevent.ev_id') . ' = ' . $db->quote($eventId));
				$query->where($db->quoteName('jevdet.state') . ' = 1');
			}
			elseif ($integration == 4)
			{
				$query->from($db->quoteName('#__social_clusters'));
				$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
				$query->where($db->quoteName('state') . ' = 1');
			}

			$db->setQuery($query);
			$eventData = $db->loadObject();

			if (!empty($eventData))
			{
				return $eventData;
			}
		}
	}

	/**
	 * Get all events data/options including integrations events
	 *
	 * @param   Boolean  $all           If true return all events, if false return options
	 *
	 * @param   integer  $creator       Event Creator
	 * @param   string   $ongoingEvent  Ongoing event state
	 *
	 * @return  Array
	 *
	 * @deprecated  3.2.0 Use JT::model('events')->getItems() also set state of creator if needed
	 *
	 * @since   2.1
	 */
	public function getIntegratedEvents($all=true, $creator=0, $ongoingEvent = '')
	{
		$integration = JT::getIntegration(true);
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);

		if ($integration == 1)
		{
			// Jom social
			if ($all)
			{
				$query->select('*');
			}
			else
			{
				$query->select('events.id, events.title, intxref.vendor_id, intxref.id as xrefId');
			}

			$query->from($db->quoteName('#__community_events', 'events'));

			$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.eventid') . ' = ' . $db->quoteName('events.id') . ')');

			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_community'));

			if ($creator)
			{
				$query->where($db->quoteName('events.creator') . ' = ' . (int) $creator);
			}

			if ($ongoingEvent)
			{
				$query->where($db->quoteName('events.enddate') . ' >= UTC_TIMESTAMP()');
			}

			$query->where($db->quoteName('events.published') . ' = 1');
		}
		elseif ($integration == 2)
		{
			// Native
			if ($all)
			{
				$query->select('*');
			}
			else
			{
				$query->select('events.id, events.title, events.startdate, intxref.vendor_id, intxref.id as xrefId');
			}

			$query->from($db->quoteName('#__jticketing_events', 'events'));

			$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.eventid') . ' = ' . $db->quoteName('events.id') . ')');

			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jticketing'));

			if ($creator)
			{
				$query->where($db->quoteName('events.created_by') . ' = ' . (int) $creator);
			}

			if ($ongoingEvent)
			{
				$query->where($db->quoteName('events.booking_end_date') . ' >= UTC_TIMESTAMP()');
			}

			$query->where($db->quoteName('events.state') . ' = 1');
		}
		elseif ($integration == 3)
		{
			// Jevent
			if ($all)
			{
				$query->select('*');
			}
			else
			{
				$query->select('jev.ev_id as id, jevd.summary as title, intxref.vendor_id, intxref.id as xrefId');
			}

			$query->from($db->quoteName('#__jevents_vevent', 'jev'));
			$query->join('INNER', $db->quoteName('#__jevents_vevdetail', 'jevd') . ' ON (' . $db->quoteName('jevd.evdet_id')
			. ' = ' . $db->quoteName('jev.detail_id') . ')');

			$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'intxref')
		. ' ON (' . $db->quoteName('intxref.eventid') . ' = ' . $db->quoteName('jev.ev_id') . ')');

			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_jevents'));
			$query->where($db->quoteName('jevd.state') . ' = 1');

			if ($creator)
			{
				$query->where($db->quoteName('jev.created_by') . ' = ' . (int) $creator);
			}

			if ($ongoingEvent)
			{
				$currentTime = strtotime(Factory::getDate('now', 'UTC', true));
				$query->where($db->quoteName('jevd.dtend') . ' >= ' . $currentTime);
			}
		}
		elseif ($integration == 4)
		{
			// Easy social
			if ($all)
			{
				$query->select('*');
			}
			else
			{
				$query->select('events.id, events.title, intxref.vendor_id, intxref.id as xrefId');
			}

			$query->from($db->quoteName('#__social_clusters', 'events'));
			$query->join('INNER', $db->quoteName('#__jticketing_integration_xref', 'intxref')
			. ' ON (' . $db->quoteName('intxref.eventid') . ' = ' . $db->quoteName('events.id') . ')');
			$query->join('INNER', $db->quoteName('#__social_events_meta', 'sem')
			. ' ON (' . $db->quoteName('sem.cluster_id') . ' = ' . $db->quoteName('events.id') . ')');

			if ($creator)
			{
				$query->where($db->quoteName('events.creator_uid') . ' = ' . (int) $creator);
			}

			if ($ongoingEvent)
			{
				$query->where($db->quoteName('sem.start') . ' >= UTC_TIMESTAMP()');
			}

			$query->where($db->quoteName('events.state') . ' = 1');
			$query->where($db->quoteName('intxref.source') . ' = ' . $db->quote('com_easysocial'));
		}

		$db->setQuery($query);
		$eventData = $db->loadObjectList();

		if (!empty($eventData))
		{
			return $eventData;
		}
	}

	/**
	 * Function to get category specific event
	 *
	 * @param   int  $catId  category id
	 *
	 * @deprecated  2.5.0 use the alternative methods from the event models
	 *
	 * @return  Array  $eventList
	 *
	 * @since  1.0.0
	 */
	public function getCategorySpecificEvents($catId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from($db->quoteName('#__jticketing_events'));
		$query->where($db->quoteName('catid') . " = " . (int) $catId);
		$query->where($db->quoteName('state') . " = " . 1);

		$db->setQuery($query);
		$events = $db->loadObjectlist();

		return $events;
	}
}
