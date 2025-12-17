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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Filesystem\File;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php'; }
if (file_exists(JPATH_SITE . '/components/com_tjfields/filterFields.php')) { require_once JPATH_SITE . '/components/com_tjfields/filterFields.php'; }
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');

/**
 * mail helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingMailHelper
{
	use TjfieldsFilterField;

	/**
	 * Method to mail pdf to buyer(also populates data)
	 *
	 * @param   array   $ticketid  id of jticketing_order table
	 * @param   string  $type      after order completed or beforeorder
	 *
	 * @return  void
	 */
	public static function sendmailnotify($ticketid, $type = '')
	{
		$com_params           = JT::config();
		$replyToEmail         = $com_params->get('reply_to');
		$fieldsIntegration    = $com_params->get('fields_integrate_with', 'com_tjfields', 'STRING');
		$jticketingmainhelper = new Jticketingmainhelper;
		$app                  = Factory::getApplication();
		$client               = "com_jticketing";
		$replacements         = new stdClass;

		if (isset($replyToEmail))
		{
			$replyToEmail = explode(",", $replyToEmail);
		}

		$integration          = $jticketingmainhelper->getIntegration();
		$source               = $jticketingmainhelper->getSourceName($integration);
		$order                = JT::order($ticketid);
		$event_integration_id = $order->event_details_id;
		$event                = JT::event()->loadByIntegration($event_integration_id);
		$eventid              = $event->getId();
		$orderitems           = $order->getItems();
		$eventpathmodel       = JPATH_SITE . '/components/com_jticketing/models/event.php';

		if (!class_exists('JticketingModelEvent'))
		{
			JLoader::register('JticketingModelEvent', $eventpathmodel);
			JLoader::load('JticketingModelEvent');
		}

		$pdfAttach = array();

		// Get ticket fields of ticket
		foreach ($orderitems AS $orderitem)
		{
			$replacements->ticket   = $jticketingmainhelper->getticketDetails($eventid, $orderitem->attendee_id);
			$date                   = $jticketingmainhelper->getTimezoneString($eventid);
			$replacements->attendee = JT::attendee($orderitem->attendee_id);
			$creator_id             = $jticketingmainhelper->getEventCreator($replacements->ticket->eid);

			if ($creator_id == '')
			{
				$creator_id = $replacements->ticket->creator;
			}

			$link         = $jticketingmainhelper->getEventlink($replacements->ticket->eid);
			$data         = array();
			$data         = $jticketingmainhelper->afterordermail($replacements->ticket, $ticketid, $link, $event_integration_id);
			$billingdata  = $jticketingmainhelper->getbillingdata($ticketid);

			// Load other libraries since we are generating PDF there are some issues after that
			// Add config for from address
			$headers    = '';
			$subject    = $data['subject'];
			$message    = $data['message'];
			PluginHelper::importPlugin('system');

			$eventUrlIcal = Uri::root() . "index.php?option=com_jticketing&view=event&format=ical&id=" . $replacements->ticket->eid;

			$replacements->ticket->site      = $app->get('sitename', '');
			$replacements->ticket->startdate = $date['startdate'];
			$replacements->ticket->enddate   = $date['enddate'];
			$replacements->event             = JT::event($replacements->ticket->eid);
			$link                      = $replacements->event->getUrl(false);
			$itemId                    = JT::utilities()->getItemId($link, 0, 0);
			$replacements->event->link = Route::link('site', $link . '&Itemid=' . $itemId, 0, 0, true);
			$replacements->event->calendar_url = $eventUrlIcal;
			$replacements->event->title        = $replacements->event->getTitle();
			$customFields                      = array();
			$fieldValuesObject                 = new stdClass;
			$replacements->event->site         = $app->get('sitename', '');
			$replacements->buyer               = new stdClass;
			$attendee                          = JT::attendee($orderitem->attendee_id);
			$replacements->ticket->first_name  = $replacements->buyer->first_name = $attendee->getFirstName();
			$replacements->ticket->last_name   = $replacements->buyer->last_name  = $attendee->getLastName();
			$replacements->event->attendeelist = Route::_(
			Uri::root() . 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $event->integrationId, false
			);

			$link   = 'index.php?option=com_jticketing&view=mytickets';
			$itemId = JT::utilities()->getItemId($link, 0, 0);
			$link   = 'index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id=' . $attendee->id;
			$replacements->ticket->ticketLink = Uri::root() . substr(
				Route::_($link . '&Itemid=' . $itemId, true),
				strlen(Uri::base(true)) + 1
			);

			$options = new Registry;
			$options->set('subject', $replacements->ticket);

			if ($fieldsIntegration == 'com_fields')
			{
				// Get Event fields
				$customFieldValues = FieldsHelper::getFields('com_jticketing.event', $replacements->event, true);
				
				// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
				{
					$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
				}
				else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
				{
					
					JLoader::register('FieldModel', JPATH_ADMINISTRATOR . '/components/com_fields/src/Model/FieldModel.php');
					$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
				}
				else
				{
					BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/src/model', 'FieldModel');
					$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
				}

				foreach ($customFieldValues as $field)
				{
					$fieldValues                = $customFieldmodel->getFieldValue($field->id, $replacements->ticket->eid);
					$field->fieldValue          = $fieldValues;
					$customFields[$field->name] = $field;
				}

				$fieldValuesObject = self::getJoomlaFieldsData($customFields);
			}
			else
			{
				$jticketingModelEvent = JT::model('Event', array('ignore_request' => true));
				$extraFields          = $jticketingModelEvent->getDataExtra($replacements->ticket->eid);

				// Formatting event tjfields key value array.
				if (!empty($extraFields))
				{
					foreach ($extraFields as $field)
					{
						if (!is_array($field->value) && !empty($field->value))
						{
							$name                     = $field->name;
							$fieldValuesObject->$name = $field->value;
						}
						else
						{
							$name                     = $field->name;
							$value                    = $field->value;
							$fieldValuesObject->$name = $value[0]->value;
						}
					}
				}
			}

			$adminKey   = "e-ticketsAdmin";
			$creatorKey = "e-ticketsCreator";

			// Get the extra fields data in email.
			$attendeeDetails     = new stdClass;
			$attendeeDetails->id = $orderitem->attendee_id;
			$replacements->attendee = self::getExtrafieldsData($replacements->event, $attendeeDetails);
			$replacements->customFields = $fieldValuesObject;

			if (!empty($replacements->ticket->customfields_ticket))
			{
				$replacements->buyer  = $replacements->ticket->customfields_ticket;
			}

			// Mail to Site administrator
			$adminUsers = JT::utilities()->getAdminUsers();

			foreach ($adminUsers as $user)
			{
				$recipients['email']['to'][] = $user->email;
				$recipients[] = $user;
			}

			$recipients['email']['cc'] = $replyToEmail;

			Tjnotifications::send($client, $adminKey, $recipients, $replacements, $options);

			$recipients                = array();
			$eventCreator              = Factory::getUser($creator_id);
			$recipients['email']['to'][]  = trim($eventCreator->email);
			$recipients[]                 = $eventCreator;
			$replacements->event->creator = $eventCreator->name;

			Tjnotifications::send($client, $creatorKey, $recipients, $replacements, $options);

			$recipients                  = array();
			$recipients['email']['to'][] = $attendee->getEmail();

			if (empty($attendee->getPhoneNumber()))
			{
				$recipients[] = !empty($attendeeDetails->owner_id) ? Factory::getUser($attendeeDetails->owner_id) :  Factory::getUser();
			}
			else
			{
				$recipients['sms'][] = $attendee->getPhoneNumber();
			}

			// Set email client & key
			$key    = "e-tickets#" . $replacements->ticket->eid;

			// Get the email template details by key
			Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_tjnotifications/tables');
			$tjTableTemplate   = Table::getInstance('notification', 'TjnotificationTable', array());
			$tjTableTemplate->load(array('key' => $key));

			// Update the email key value as per the email status
			$key = ((!empty($tjTableTemplate->email_status)? $tjTableTemplate->email_status : '0') == '1') ? $key : "e-tickets";

			Factory::getApplication()->triggerEvent('onJtBeforeTicketEmail', array($recipients, $data['subject'], $data['message']));

			if (!empty($data['ics']))
			{
				$options->set('stringAttachment', $data['ics']);
			}

			if (!empty($data['pdf']))
			{
				$attendeeParams = json_decode($attendee->getParams());

				if ($attendeeParams && isset($attendeeParams->sendTicket) && $attendeeParams->sendTicket == 'ticketToAttendee')
				{
					$options->set('attachment', $data['pdf']);
					$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
				}
				else
				{
					// Add all the attendees PDF into a single mail of Event Buyer
					if (!empty($data['pdf'][0]))
					{
						array_push($pdfAttach, $data['pdf'][0]);
					}
				}
			}
			else
			{
				$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
			}
		}

		if (!empty($pdfAttach))
		{
			$key = 'e-ticketsforEventBuyer';
			$recipients                       = array();
			$recipients['email']['to'][]      = $order->email;
			$replacements->buyer->eventBuyer  = $order->name;
			$replacements->buyer->ticketCount = count($orderitems);
			$options->set('attachment', $pdfAttach);
			$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
		}

		// Update mailsent flag if email sent
		if ($result['success'])
		{
			$obj     = new StdClass;
			$obj->id = $ticketid;
			$obj->ticket_email_sent = 1;
			$db = Factory::getDbo();

			if ($db->updateObject('#__jticketing_order', $obj, 'id'))
			{
			}
		}

		return $result;
	}

	/**
	 * Method to sendMail
	 *
	 * @param   string  $from     from
	 * @param   string  $fromnm   fromname
	 * @param   string  $recept   recipient
	 * @param   string  $subject  subject
	 * @param   string  $body     body
	 * @param   string  $mode     mode
	 * @param   string  $cc       cc
	 * @param   string  $bcc      bcc
	 * @param   string  $attach   attachment
	 * @param   array   $repto    repto
	 * @param   string  $rpnm     replytoname
	 * @param   string  $headr    headers
	 *
	 * @return  boolean  true/false
	 */
	public static function sendMail($from, $fromnm, $recept, $subject, $body,
		$mode, $cc = '', $bcc = '', $attach = '', $repto = array(), $rpnm = '', $headr = '')
	{
		// Get a JMail instance
		try
		{
			$mail = Factory::getMailer(true);
			$mail->setSender(array($from, $fromnm));
			$mail->setSubject($subject);
			$mail->setBody($body);

			// Are we sending the email as HTML?
			if ($mode)
			{
				$mail->IsHTML(true);
			}

			if (!empty($cc))
			{
				$mail->addCC($cc);
			}

			if (!empty($recept))
			{
				$mail->addRecipient($recept);
			}

			if (!empty($bcc))
			{
				$mail->addBCC($bcc);
			}

			if (!empty($attach))
			{
				$mail->addAttachment($attach);
			}

			// Take care of reply email addresses
			if (is_array($repto))
			{
				$numReplyTo = count($repto);

				for ($i = 0; $i < $numReplyTo; $i++)
				{
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						$mail->addReplyTo($repto[$i], $rpnm[$i]);
					}
					else
					{
						$mail->addReplyTo(array($repto[$i], $rpnm[$i]));
					}
				}
			}
			elseif (!empty($repto))
			{
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$mail->addReplyTo($repto, $rpnm);
				}
				else
				{
					$mail->addReplyTo(array($repto, $rpnm));
				}
			}

			if ($mail->Send())
			{
				return 1;
			}

			return 0;
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			Factory::getApplication()->enqueueMessage($msg, 'error');
		}
	}

	/**
	 * Order status Email change
	 *
	 * @param   integer  $order_id  order_id for successed
	 * @param   string   $status    step no
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function sendOrderStatusEmail($order_id, $status)
	{
		$order       = JT::order($order_id);
		$billingdata = $order->getbillingdata();
		$eventObj    = JT::event()->loadByIntegration($order->event_details_id);
		$app          = Factory::getApplication();
		$replacements = new stdClass;
		$options      = new Registry;
		$recipients   = array();
		$client       = "com_jticketing";

		// Get the CC
		PluginHelper::importPlugin('system');
		$resp = Factory::getApplication()->triggerEvent('onJtBeforeOrderStatusChangeEmail', array($order));

		if (!empty($resp['0']))
		{
			$recipients['email']['cc'] = array ($resp['0']);
		}

		$opt = new stdClass;
		$opt->orderid_with_prefix   = $order->order_id;
		$order->orderid_with_prefix = $order->order_id;
		$opt->title = $eventObj->getTitle();

		// Replacement tag for subject
		$options->set('subject', $opt);

		// Add replacement tag for body
		$replacements->order               = $order;
		$replacements->order->newStatus    = JT::utilities()->getPaymentStatus($status);
		$replacements->event               = $eventObj;
		$replacements->event->site         = $app->get('sitename');
		$url                               = $eventObj->getUrl(true);
		$replacements->event->event_url    = Uri::root() . substr(
			$url,
			strlen(Uri::base(true)) + 1
		);

		$replacements->event->title = $eventObj->getTitle();

		// Mail to Site administrator
		$options = new Registry;
		$key = "admin_order_status";
		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;

			// Send SMS to creator
			$recipients[] = $user;
		}
		
		$options->set('subject', $opt);
		$options->set('to', $user->id);
		$options->set('from', $user->id);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$recipients = array();

		// Mail to Event Creator
		$options = new Registry;
		$key = "creator_order_status";
		$creator = Factory::getUser($eventObj->getCreator());
		$recipients['email']['to'] = array($creator->email);
		$recipients['web']['to'] = array($creator->email);

		// Send SMS to creator
		$recipients[] = $creator;
		$replacements->event->creator = $creator->name;
		
		$options->set('subject', $opt);
		$options->set('to', $eventObj->getCreator());
		$options->set('from', $eventObj->getCreator());

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$recipients = array();

		// Mail to Event Buyer
		$options = new Registry;
		$key = "any_order_status";
		$recipients['email']['to'] = array(trim($billingdata->user_email));
		$recipients['web']['to'] = array(trim($billingdata->user_email));
		$recipients['sms'] = array('+' . $billingdata->country_mobile_code . $billingdata->phone);
		
		$options->set('from', $billingdata->user_id);
		$options->set('to', $billingdata->user_id);
		$options->set('url', $replacements->event->event_url);
		$options->set('subject', $opt);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send invoice email after payment
	 *
	 * @param   string  $id  id in jticketing_order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function sendInvoiceEmail($id)
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$app         = Factory::getApplication();
		$menu        = $app->getMenu();
		$TjGeoHelper = new TjGeoHelper;
		$db          = Factory::getDbo();
		$comParams   = JT::config();
		$jinput      = Factory::getApplication()->getInput();
		$client      = "com_jticketing";

		/** @var $order JTicketingOrder */
		$orderinfo = JT::order($id);
		$eventinfo = JT::event()->loadByIntegration($orderinfo->event_details_id);
		$eventsUrl    = 'index.php?option=com_jticketing&view=events&layout=default&catid=' . (!empty($eventinfo->catid) ? $eventinfo->catid : '');
		$menuItem     = $menu->getItems('link', $eventsUrl, true);
		$userInfo     = $orderinfo->getbillingdata();
		$replacements = new stdclass;
		$replacements->order = new stdclass;
		$utilities           = JT::utilities();
		$eventCreator        = Factory::getUser($eventinfo->getCreator());
		$eventOwnerEmail     = $eventCreator->email;

		// Add single or multiple tax of order.
		$replacements->order->taxPercent = '';

		if (isset($orderinfo->order_tax) && $orderinfo->order_tax > 0)
		{
			$taxArr = new Registry($orderinfo->getTaxDetails());

			foreach ($taxArr as $tax)
			{
				foreach ($tax->breakup as $key => $breakup)
				{
					$delimeter = '';
					$length    = count($tax->breakup);
					($length == 1) ? $delimeter = '%' : $delimeter = '% + ';

					if (($length - 1) == $key)
					{
						$replacements->order->taxPercent .= $breakup->percentage . '%';
					}
					else
					{
						$replacements->order->taxPercent .= $breakup->percentage . $delimeter;
					}
				}
			}
		}

		// Add order related data.
		$replacements->order->newStatus           = $utilities->getPaymentStatus($orderinfo->getStatus());

		if ($comParams->get('admin_fee_mode'))
		{
			$replacements->order->amountwithFee = $utilities->getFormattedPrice($orderinfo->getOriginalAmount());
		}
		else 
		{
			$replacements->order->amountwithFee = $utilities->getFormattedPrice($orderinfo->getOriginalAmount() + $orderinfo->getFee(false));
		}

		$replacements->order->amountDisc       = $utilities->getFormattedPrice($orderinfo->getOriginalAmount() + $orderinfo->getFee(false));
		$replacements->order->taxAmount           = $utilities->getFormattedPrice($orderinfo->getOrderTax());
		$replacements->order->subTotal            = $utilities->getFormattedPrice($orderinfo->getOriginalAmount());
		$replacements->order->platform_fee        = $utilities->getFormattedPrice($orderinfo->getFee());
		$replacements->order->orderid_with_prefix = $orderinfo->order_id;
		$replacements->order->cdate               = $orderinfo->cdate;
		$replacements->order->coupon              = $orderinfo->getCouponCode();
		$replacements->order->discount            = $orderinfo->getCouponDiscount();

		// Add user related data.
		$eventinfo->firstname = $replacements->order->firstname = isset($userInfo->firstname) ?
		$userInfo->firstname : '';
		$eventinfo->lastname = $replacements->order->lastname = isset($userInfo->lastname) ?
		$userInfo->lastname : '';
		$replacements->order->phone         = isset($userInfo->phone) ? $userInfo->phone : '';
		$replacements->order->user_email    = isset($userInfo->user_email) ? $userInfo->user_email : '';
		$replacements->order->address       = isset($userInfo->address) ? $userInfo->address : '';
		$replacements->order->business_name = ($userInfo->business_name) ?
		Text::_('COM_JTICKETING_ORDER_COMPANY_NAME') . ' - ' . $userInfo->business_name : '';
		$replacements->order->vat_number    = ($userInfo->vat_number) ?
		Text::_('COM_JTICKETING_ORDER_VAT_NUMBER') . ' - ' . $userInfo->vat_number : '';
		$replacements->order->city          = isset($userInfo->city) ? $userInfo->city : '';
		$replacements->order->zipcode       = isset($userInfo->zipcode) ? $userInfo->zipcode : '';
		$replacements->order->state         = isset($userInfo->state_code) ? $TjGeoHelper->getRegionNameFromId($userInfo->state_code) : '';
		$replacements->order->country       = isset($userInfo->country_code) ? $TjGeoHelper->getCountryNameFromId($userInfo->country_code) : '';

		$tagConstant = Text::_('COM_JTICKETING_EMAIL_TICKET_INFO');
		$tags        = explode(",", $tagConstant);
		$tags        = str_replace(array('<table>', '</table>'), '', $tags);

		$headConstant            = [];
		$innerConstant           = str_replace(array('[', ']'), '', $tags[0]);
		$headConstant['header']  = Text::_($innerConstant);
		$innerConstant1          = str_replace(array('[', ']'), '', $tags[1]);
		$headConstant['replace'] = Text::_($innerConstant1);
		$matches                 = $newMatches = array();
		$pattern                 = "/\[([\w.]+)\]/";
		preg_match_all($pattern, $headConstant['replace'], $matches);
		preg_match_all($pattern, $headConstant['header'], $newMatches);

		$replaceTags = new stdclass;

		// Get the ticket types user booked.
		$ticketTypes = $orderinfo->getItemTypes();
		$i           = 1;

		foreach ($ticketTypes as $item)
		{
			// Get the total prize, count and free related to order item and type id.
			$orderItemDetails      = $orderinfo->getItemsByType($item->id);
			$item->currencyPrice   = $item->getPrice();

			if ($comParams->get('admin_fee_mode'))
			{
				$item->totalPrice = $utilities->getFormattedPrice($orderItemDetails->totalPrize);
			}
			else
			{
				$item->totalPrice = $utilities->getFormattedPrice(
					$orderItemDetails->totalPrize +
					$orderItemDetails->totalFee);
			}

			$item->no              = $i++;
			$item->ticketcount     = $orderItemDetails->count;
			$item->order_item_name = $item->title;
			$item->priceColumnName = Text::_('COM_JTICKETING_EMAIL_INVOICE_COLUMN_TOTAL_PRICE');

			if (!$comParams->get('admin_fee_mode') && $comParams->get('admin_fee_level') == 'orderitem')
			{
				$item->priceColumnName = Text::_('COM_JTICKETING_EMAIL_INVOICE_COLUMN_TOTAL_PRICE_WITH_FEE');
			}

			$replaceTags->{'ticket' . $i} = $item;
		}

		// Get the total price column name dynamically.
		$totalPriceColName = ($comParams->get('admin_fee_level') == 'order') ? Text::_('COM_JTICKETING_EMAIL_INVOICE_COLUMN_TOTAL_PRICE')
		: Text::_('COM_JTICKETING_EMAIL_INVOICE_COLUMN_TOTAL_PRICE_WITH_FEE');

		$template1      = Text::_($innerConstant);
		$template1      = str_replace('[' . $newMatches[1][0] . ']', $totalPriceColName, $template1);
		$appendTemplate = '';

		foreach ($replaceTags as $replaceTag)
		{
			$template = Text::_($innerConstant1);

			foreach ($replaceTag as $i => $index)
			{
				foreach ($matches[1] as $values)
				{
					if ($i == $values)
					{
						$template = str_replace('[' . $values . ']', $index, $template);
					}
				}
			}

			$appendTemplate .= $template;
		}

		// Add template in mail data.
		$appendTemplate       = $template1 . $appendTemplate;
		$replacements->event  = new stdclass;
		$replacements->ticket = new stdclass;

		if ($appendTemplate)
		{
			$replacements->ticket->TICKET_INFO = new stdclass;
			$replacements->ticket->TICKET_INFO = $appendTemplate;
		}

		$replacements->order->total        = $orderinfo->getAmount();
		$eventinfo->site                   = $app->get('sitename');
		$replacements->event               = $eventinfo;
		$replacements->event->creator      = $eventCreator->name;
		$replacements->event->title = $eventinfo->title = $eventinfo->getTitle();
		$eventinfo->orderid_with_prefix = $replacements->order->orderid_with_prefix;

		if ($comParams->get('enable_eventstartdateinname'))
		{
			$eventStartDate   = $eventinfo->getStartdate();
			$replacements->event->title = $eventinfo->getTitle() . " (" . $utilities->getFormatedDate($eventStartDate) . ")";
		}

		if ($comParams->get('display_vendor_details'))
		{
			$vendor = $eventinfo->getVendorDetails();
			$replacements->event->organizer        = $vendor->vendor_title ? $vendor->vendor_title : '';
			$replacements->event->organizer_detail = $vendor->vendor_description ? $vendor->vendor_description : '';
		}
		else
		{
			$replacements->event->organizer        = $comParams->get('company_name') ?
			$comParams->get('company_name') : '';
			$replacements->event->organizer_detail = $comParams->get('company_address') ?
			$comParams->get('company_address') : '';
		}

		$jtRouteHelper = new JTRouteHelper;
		$orderUrl      = 'index.php?option=com_jticketing&view=orders&layout=order';
		$linkForOrders = $jtRouteHelper->JTRoute($orderUrl . '&orderid=' . $orderinfo->order_id . '&tmpl=component', false);
		$replacements->order->url = Uri::root() . substr(Route::_($linkForOrders), strlen(Uri::base(true)) + 1);

		$options = new Registry;
		$options->set('subject', $eventinfo);

			// Mail to Site administrator
		$key = "paymentInvoiceAdmin";
		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;
			$recipients[] = $user;
		}
		
		$options->set('from', $user->id);
		$options->set('to', $user->id);
		$options->set('url', $replacements->order->url);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		// Mail to Event Creator
		$options = new Registry;
		$recipients                = array();
		$key                       = "paymentInvoiceCreator";
		$recipients['email']['to'][]  = trim($eventOwnerEmail);
		$recipients['web']['to'][]  = trim($eventOwnerEmail);
		$recipients[]                 = $eventCreator;
		
		$options->set('from', $eventinfo->getCreator());
		$options->set('to', $eventinfo->getCreator());
		$options->set('url', $replacements->order->url);
		
		$options->set('subject', $eventinfo);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		// Mail to Event buyer
		$options = new Registry;
		$recipients                  = array();
		$key                         = "payment";
		$recipients['email']['to'][] = $userInfo->user_email;
		$recipients['web']['to'][] = $userInfo->user_email;
		$recipients['sms'][] = '+' . $userInfo->country_mobile_code . $userInfo->phone;
		
		$options->set('from', $userInfo->user_id);
		$options->set('to', $userInfo->user_id);
		$options->set('url', $replacements->order->url);
		$options->set('subject', $eventinfo);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send invoice email after payment
	 *
	 * @param   JTicketingOrder     $order     Jticketing order details
	 * @param   JTicketingAttendee  $attendee  Attendee object who have purchased event
	 * @param   JTicketingEvent     $event     Event object (online)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function onlineEventNotify(JTicketingOrder $order, JTicketingAttendee $attendee, JTicketingEvent $event)
	{
		$config               = JT::config();
		$replyToEmail         = $config->get('reply_to');
		$dateFormat           = $config->get('date_format_show');
		if ($dateFormat == "custom")
		{
			$dateFormat = $config->get('custom_format');
		}

		$fieldsIntegration    = $config->get('fields_integrate_with', 'com_tjfields', 'STRING');
		$app                  = Factory::getApplication();
		$oldEventId           = $app->getUserState('attendee.oldEventId');

		if (isset($replyToEmail))
		{
			$replyToEmail = explode(",", $replyToEmail);
		}

		$app             = Factory::getApplication();
		$sitename        = $app->get('sitename');
		$eventOwnerEmail = Factory::getUser($event->getCreator())->email;
		$client          = "com_jticketing";
		$replacements    = new stdclass;
		$replacements->event       = new stdclass;
		$replacements->event       = $event;
		$replacements->event->site = $sitename;
		$eventUrlIcal              = Uri::root() . "index.php?option=com_jticketing&view=event&format=ical&id=" . $event->eventid;
		$replacements->event->calendar_url = $eventUrlIcal;
		$replacements->event->creator      = Factory::getUser($event->getCreator())->name;

		$replacements->user             = new stdClass;

		// Get meeting/webinar url
		$replacements->user->url        = Uri::root() . 'index.php?option=com_jticketing&task=attendees.getAuthorisedUrl&attendeeId=' . $attendee->id;
		$replacements->event->first_name = $replacements->user->first_name = $attendee->getFirstName();
		$replacements->event->last_name  = $replacements->user->last_name  = $attendee->getLastName();
		$details                        = $event->getReplacementTags($attendee);
		$replacements->user->name       = $attendee->getFirstName() . ' ' . $attendee->getLastName();
		$replacements->event->name      = $replacements->user->name;
		$replacements->event->attendeelist = Route::_(
			Uri::root() . 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $event->integrationId, false
			);

		// User details provided by online providers
		$replacements->user->onlineProviderDetails = isset($details)?$details:'';
		$customFields      = array();
		$fieldValuesObject = new stdClass;
		$linkMode          = Factory::getConfig()->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;
		$url               = $event->getUrl(true);
		$replacements->event->url          = Route::link('site', $url, false, $linkMode, true);
		$replacements->event->timezone     = Factory::getUser()->getTimezone()->getName();
		$replacements->event->attendeelist = Route::_(
			Uri::root() . 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $event->integrationId, false
		);

		$jteventHelper = new jteventHelper;
		$eventData = $jteventHelper->getEventData($event->id);
		$replacements->event->startdate = HTMLHelper::date($eventData->startdate, $dateFormat, true);
		$replacements->event->enddate   = HTMLHelper::date($eventData->enddate, $dateFormat, true);

		if ($fieldsIntegration == 'com_fields')
		{
			// Get Event fields
			$customFieldValues = FieldsHelper::getFields('com_jticketing.event', $replacements->event, true);
			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}
			else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
			{
				
				JLoader::register('FieldModel', JPATH_ADMINISTRATOR . '/components/com_fields/src/Model/FieldModel.php');
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}
			else
			{
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/src/model', 'FieldModel');
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}

			foreach ($customFieldValues as $field)
			{
				$fieldValues                = $customFieldmodel->getFieldValue($field->id, $replacements->event->id);
				$field->fieldValue          = $fieldValues;
				$customFields[$field->name] = $field;
			}

			$fieldValuesObject = self::getJoomlaFieldsData($customFields);
		}
		else
		{
			$jticketingModelEvent = JT::model('Event', array('ignore_request' => true));
			$extraFields          = $jticketingModelEvent->getDataExtra($replacements->event->id);

			// Formatting event tjfields key value array.
			if (!empty($extraFields))
			{
				foreach ($extraFields as $field)
				{
					if (!is_array($field->value) && !empty($field->value))
					{
						$name                     = $field->name;
						$fieldValuesObject->$name = $field->value;
					}
					else
					{
						$name                     = $field->name;
						$value                    = $field->value;
						$fieldValuesObject->$name = $value[0]->value;
					}
				}
			}
		}

		ob_start();
		require JPATH_SITE . '/components/com_jticketing/views/event/tmpl/default_ical.php';
		$output = ob_get_contents();
		$output = strtr($output, array("\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n"));
		ob_end_clean();

		$data = array();
		$random_no = UserHelper::genRandomPassword(3);

		$data['ics']['content'] = $output;
		$data['ics']['encoding'] = 'base64';
		$data['ics']['type'] = 'text/calendar';
		$file    = str_replace(" ", "_", $replacements->event->title);
		$icsnm   = $file . '_' . $random_no . '_' . ".ics";
		$data['ics']['name'] = $icsnm;
		$icsname = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . $icsnm;

		if (file_exists($icsname))
		{
			$file = fopen($icsname, "w");

			if (!empty($file))
			{
				fwrite($file, $output);
				fclose($file);
				$data['pdf']['ics_file']      = $icsname;
			}
		}

		$replacements->customFields = $fieldValuesObject;

		$options = new Registry;
		$options->set('subject', $replacements->event);

		// Mail to Site administrator
		$adminKey   = "e-ticketsOnlineEventAdmin";
		$creatorKey = "e-ticketsOnlineEventCreator";
		$buyerKey   = "e-ticketsOnlineEvent";

		if (!empty($oldEventId))
		{
			$adminKey   = "m-ticketsOnlineEventAdmin";
			$creatorKey = "m-ticketsOnlineEventCreator";
			$buyerKey   = "m-ticketsOnlineEvent";
			$replacements->oldevent = JT::event($oldEventId);

			$app->setUserState('attendee.oldEventId', 0);
		}

		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;
			$recipients[] = $user;
		}
		
		$options->set('from', $user->id);
		$options->set('to', $user->id);

		Tjnotifications::send($client, $adminKey, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $replacements->event);

		$recipients                = array();
		$eventCreator              = Factory::getUser($event->getCreator());
		$recipients['email']['to'][]  = trim($eventOwnerEmail);
		$recipients['web']['to'][]  = trim($eventOwnerEmail);
		$recipients[]                 = $eventCreator;
		
		$options->set('from', $event->getCreator());
		$options->set('to', $event->getCreator());

		Tjnotifications::send($client, $creatorKey, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $replacements->event);

		$recipients                  = array();
		$recipients['email']['to'][] = $attendee->getEmail();
		$recipients['web']['to'][]   = $attendee->getEmail();
		$recipients['email']['cc']   = $replyToEmail;

		if (empty($attendee->getPhoneNumber()))
		{
			$recipients[] = Factory::getUser($attendee->owner_id);
		}
		else
		{
			$recipients['sms'][] = $attendee->getPhoneNumber();
		}

		if (!empty($data['ics']))
		{
			$options->set('stringAttachment', $data['ics']);
		}
		
		$options->set('from', $attendee->owner_id);
		$options->set('to', $attendee->owner_id);

		$result = Tjnotifications::send($client, $buyerKey, $recipients, $replacements, $options);

		// Update mailsent flag if email sent
		if ($result['success'])
		{
			$order->ticket_email_sent = 1;

			return $order->save();
		}

		return false;
	}

	/**
	 * Send checkin email after checkin
	 *
	 * @param   ARRAY  $data  checkin array
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkInMail($data)
	{
		PluginHelper::importPlugin('jticketing');

		// Call the plugin and get the feedback form link
		$feedbackFormLink = Factory::getApplication()->triggerEvent('onJtGetFeedbackFormUrl', array($data));
		$replacements     = new stdClass;
		$app              = Factory::getApplication();
		$utilities        = JT::utilities();
		$sitename         = $app->get('sitename');
		$client           = "com_jticketing";
		$attendee         = JT::attendee($data['attendee_id']);
		$attendeeName     = $attendee->getFirstName() . ' ' . $attendee->getLastName();
		$toSmsNumberObj   = '';

		$replacements->checkin          = (object) $data;
		$replacements->event            = JT::event()->loadByIntegration($data['eventid']);
		$replacements->event->startdate = $utilities->getFormatedDate($replacements->event->getStartDate());
		$replacements->event->enddate   = $utilities->getFormatedDate($replacements->event->getEndDate());
		$replacements->event->site      = $sitename;
		$replacements->event->feedbackformlink = "";
		$replacements->event->title = $replacements->event->getTitle();
		$replacements->checkin->attendee_name  = $attendeeName;

		$replacements->checkin->checkouttime = ($replacements->checkin->checkouttime) ?
		$replacements->checkin->checkouttime : '';

		$opt                = new stdClass;
		$opt->attendee_name = $attendeeName;
		$opt->title         = $replacements->event->title;

		// Replacement tag for subject
		$options = new Registry;
		$options->set('subject', $opt);

		if (!empty($feedbackFormLink))
		{
			$replacements->event->feedbackformlink = Text::sprintf('COM_JTICKETING_EVENT_FEEDBACK_FORM_LINK', $feedbackFormLink[0]);
		}

		if (empty($replacements->checkin->certificateUrl))
		{
			$replacements->checkin->certificateUrl    = "";
			$replacements->checkin->certificateRawUrl = "";
		}

		// Mail to Site administrator
		$key = "checkin_site_admin";
		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;
			$recipients[] = $user;
		}
		
		$options->set('to', $user->id);
		$options->set('from', $user->id);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $opt);
		$recipients                = array();
		$key                       = "checkin_event_creator";
		$eventCreator              = Factory::getUser($replacements->event->getCreator());
		$recipients['email']['to'][]  = trim($eventCreator->email);
		$recipients['web']['to'][]  = trim($eventCreator->email);
		$recipients[]                 = $eventCreator;
		$replacements->event->creator = $eventCreator->name;
		
		$options->set('to', $replacements->event->getCreator());
		$options->set('from', $replacements->event->getCreator());

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $opt);
		$recipients                  = array();
		$key                         = "checkin";
		$recipients['email']['to'][] = empty ($attendee->getEmail()) ? $data['attendee_email']
		: $attendee->getEmail();
		$recipients['web']['to'][] = empty ($attendee->getEmail()) ? $data['attendee_email']
		: $attendee->getEmail();

		if (empty($attendee->getPhoneNumber()))
		{
			$recipients[] = Factory::getUser($data['owner_id']);
		}
		else
		{
			$recipients['sms'][] = $attendee->getPhoneNumber();
		}

		$options->set('to', empty($attendee->owner_id) ? $data['owner_id'] : $attendee->owner_id);
		$options->set('from', empty($attendee->owner_id) ? $data['owner_id'] : $attendee->owner_id);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send waiting list email after added in waitlist
	 *
	 * @param   INT  $waitlistId            waitlist id
	 *
	 * @param   INT  $seatsAvailableStatus  seats available status
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function waitinglistMail($waitlistId, $seatsAvailableStatus)
	{
		$app          = Factory::getApplication();
		$mailfrom     = $app->get('mailfrom');
		$fromname     = $app->get('fromname');
		$sitename     = $app->get('sitename');
		$model        = JT::model('WaitlistForm');
		$waitlistData = $model->getItem($waitlistId);
		
		$options = new Registry;
		$client       = "com_jticketing";
		$key          = "waitinglistAddedUser";

		if (!empty($seatsAvailableStatus))
		{
			$key = "waitinglistTicketsAvailable";
		}

		if ($waitlistData->behaviour == 'classroom_training' && empty($seatsAvailableStatus))
		{
			$key = "waitinglistClassroomTrainingAutoAdvance";
		}

		$eventDetails       = JT::event()->loadByIntegration($waitlistData->event_id);
		$eventDetails->site = $sitename;
		$eventDetails->title = $eventDetails->getTitle();

		$userDetails = new stdClass;
		$userDetails = Factory::getUser($waitlistData->user_id);

		$replacements        = new stdClass;
		$replacements->event = $eventDetails;
		$replacements->user  = $userDetails;

		$options->set('subject', $eventDetails);

		$recipients = array (

			Factory::getUser($waitlistData->user_id),

			// Add specific to, cc (optional), bcc (optional)
			'email' => array (
				'to' => array(Factory::getUser($waitlistData->user_id)->email)
			),
			'web' => array (
				'to' => array (Factory::getUser($waitlistData->user_id)->email)
			)
		);

		$notificationsParams = ComponentHelper::getParams('com_tjnotifications');
		$integrationOption = $notificationsParams->get('web_notification_provider', 'easysocial');

		// Check easysocial is installed correctly on site before sending web easysocial mail
		if ($integrationOption == 'easysocial' && !File::exists(JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php'))
		{
			$recipients = array (
	
				Factory::getUser($waitlistData->user_id),
	
				// Add specific to, cc (optional), bcc (optional)
				'email' => array (
					'to' => array(Factory::getUser($waitlistData->user_id)->email)
				)
			);
		}
		
		$options->set('to', $waitlistData->user_id);
		$options->set('from', $waitlistData->user_id);

		$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		return $result;
	}

	/**
	 * Send enrollment ticket email
	 *
	 * @param   object  $eventDetails     event details
	 * @param   object  $attendeeDetails  attendeeDetails details
	 *
	 * @return  Boolean  True on mail sent and false for failed case.
	 *
	 * @since   1.0
	 */
	public static function enrollmentTicketMail($eventDetails, $attendeeDetails)
	{
		$com_params           = JT::config();
		$fieldsIntegration    = $com_params->get('fields_integrate_with', 'com_tjfields', 'STRING');
		$eventobj             = $eventDetails;
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = JT::getIntegration(true);
		$app                  = Factory::getApplication();
		$oldEventId           = $app->getUserState('attendee.oldEventId');
		$client               = "com_jticketing";
		$attendee             = JT::attendee($attendeeDetails->id);
		$creator_id           = $eventDetails->getCreator();

		$link         = $eventDetails->event_url;
		$replacements = new stdClass;
		$evxref_id      = JT::event($eventDetails->getId(), $integration)->integrationId;

		$replacements->ticket       = $jticketingmainhelper->getticketDetails($eventDetails->getId(), $attendeeDetails->id);
		$data                       = $jticketingmainhelper->afterordermail(
			$replacements->ticket,
			$attendeeDetails->id,
			$link,
			$evxref_id
		);
		$replacements->ticket->site = $app->get('sitename', '');

		$integration = $jticketingmainhelper->getIntegration();
		$source      = $jticketingmainhelper->getSourceName($integration);
		$eventid     = $jticketingmainhelper->getEventID_FROM_INTEGRATIONID(
			$attendeeDetails->event_id,
			$source
		);
		$date        = $jticketingmainhelper->getTimezoneString($eventid);

		$replacements->ticket->startdate = $date['startdate'];
		$replacements->ticket->enddate   = $date['enddate'];

		$replacements->ticket->cdate = isset($replacements->ticket->cdate) ?
			$replacements->ticket->cdate : '';

		if ($integration == 2)
		{
			$eventDetails               = JT::event($replacements->ticket->eid);
			$eventUrlIcal               = Uri::root() . "index.php?option=com_jticketing&view=event&format=ical&id=" . $replacements->ticket->eid;
			$eventDetails->link         = $link;
			$eventDetails->calendar_url = $eventUrlIcal;
		}

		$replacements->attendee      = self::getExtrafieldsData($eventDetails, $attendeeDetails);
		$customFields      = array();
		$replacements->customFields = new stdClass;

		if ($fieldsIntegration == 'com_fields')
		{
			// Get Event fields
			$customFieldValues = FieldsHelper::getFields('com_jticketing.event', $eventDetails, true);

			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}
			else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '5.0.0')
			{
				
				JLoader::register('FieldModel', JPATH_ADMINISTRATOR . '/components/com_fields/src/Model/FieldModel.php');
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}
			else
			{
				BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/src/model', 'FieldModel');
				$customFieldmodel  = BaseDatabaseModel::getInstance('Field', 'FieldsModel', array('ignore_request' => true));
			}

			foreach ($customFieldValues as $field)
			{
					$fieldValues                = $customFieldmodel->getFieldValue($field->id, $replacements->ticket->eid);
					$field->fieldValue          = $fieldValues;
					$customFields[$field->name] = $field;
			}

			$replacements->customFields = self::getJoomlaFieldsData($customFields);
		}
		else
		{
			$jticketingModelEvent = JT::model('Event', array('ignore_request' => true));
			$extraFields          = $jticketingModelEvent->getDataExtra($replacements->ticket->eid);

			// Formatting event tjfields key value array.
			if (!empty($extraFields))
			{
				foreach ($extraFields as $field)
				{
					if (!is_array($field->value) && !empty($field->value))
					{
						$name                     = $field->name;
						$replacements->customFields->$name = $field->value;
					}
					else
					{
						$name                     = $field->name;
						$value                    = $field->value;
						$replacements->customFields->$name = $value[0]->value;
					}
				}
			}
		}

		$replacements->event               = $eventDetails;
		$replacements->event->site         = $app->get('sitename', '');
		$replacements->event->attendeelist = Route::_(
			Uri::root() . 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $eventobj->integrationId, false);
		$replacements->buyer             = new stdClass;
		$replacements->ticket->first_name = $replacements->buyer->first_name = $attendee->getFirstName();
		$replacements->ticket->last_name = $replacements->buyer->last_name  = $attendee->getLastName();

		$replacements->user           = new stdClass;
		$replacements->user->name     = $attendee->getFirstName() . " " . $attendee->getLastName();
		$replacements->user->username = Factory::getUser($attendee->owner_id)->username;
		$replacements->user->email    = Factory::getUser($attendee->owner_id)->email;
		$replacements->attendee->phone = ($attendee->getPhoneNumber()) ?
		$attendee->getPhoneNumber() : '';
		$replacements->attendee->email = $attendee->getEmail();

		$link   = 'index.php?option=com_jticketing&view=mytickets';
		$itemId = JT::utilities()->getItemId($link, 0, 0);
		$link   = 'index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&attendee_id=' . $attendee->id;

		$replacements->ticket->ticketLink = Uri::root() . substr(
			Route::_($link . '&Itemid=' . $itemId, true),
			strlen(Uri::base(true)) + 1
		);

		$link                      = $eventobj->getUrl(false);
		$itemId                    = JT::utilities()->getItemId($link, 0, 0);
		$replacements->event->link = Route::link('site', $link . '&Itemid=' . $itemId, 0, 0, true);

		$options = new Registry;
		$options->set('subject', $replacements->ticket);

		$adminKey   = "e-ticketsAdmin";
		$creatorKey = "e-ticketsCreator";
		$buyerKey   = "e-tickets";

		if (!empty($oldEventId))
		{
			$adminKey   = "m-ticketsSiteAdministrator";
			$creatorKey = "m-ticketsEventCreator";
			$buyerKey   = "m-tickets";
			$replacements->oldevent = JT::event($oldEventId);

			if (JT::getIntegration() != 'com_jticketing')
			{
				$replacements->oldevent->title = $replacements->oldevent->event->title;
			}

			$app->setUserState('attendee.oldEventId', 0);
		}
		// Mail to Site administrator
		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;
			$recipients[] = $user;
		}
		
		$options->set('to', $user->id);
		$options->set('from', $user->id);

		Tjnotifications::send($client, $adminKey, $recipients, $replacements, $options);
		
		$options = new Registry;
		$options->set('subject', $replacements->ticket);

		$recipients                = array();
		$eventCreator              = Factory::getUser($creator_id);
		$recipients['email']['to'][]  = trim($eventCreator->email);
		$recipients['web']['to'][]  = trim($eventCreator->email);
		$recipients[]                 = $eventCreator;
		$replacements->event->creator = $eventCreator->name;
		
		$options->set('to', $creator_id);
		$options->set('from', $creator_id);

		Tjnotifications::send($client, $creatorKey, $recipients, $replacements, $options);
		
		$options = new Registry;
		$options->set('subject', $replacements->ticket);

		$recipients                  = array();
		$recipients['email']['to'][] = $attendee->getEmail();
		$recipients['web']['to'][] = $attendee->getEmail();
		
		$options->set('to', $attendeeDetails->owner_id);
		$options->set('from', $attendeeDetails->owner_id);

		if (empty($attendee->getPhoneNumber()))
		{
			$recipients[] = Factory::getUser($attendeeDetails->owner_id);
		}
		else
		{
			$recipients['sms'][] = $attendee->getPhoneNumber();
		}

		if (!empty($data['ics']))
		{
			$options->set('stringAttachment', $data['ics']);
		}

		if (!empty($data['pdf']))
		{
			$options->set('attachment', $data['pdf']);
			$result = Tjnotifications::send($client, $buyerKey, $recipients, $replacements, $options);

			// Delete unwanted pdf and ics files stored in tmp folder
			if ($result)
			{
				JT::utilities()->deleteunwantedfiles($data);
			}
		}
		else
		{
			$result = Tjnotifications::send($client, $buyerKey, $recipients, $replacements, $options);
		}

		return $result;
	}

	/**
	 * Method to send reject enrollment email
	 *
	 * @param   object  $eventDetails     event details
	 * @param   object  $attendeeDetails  attendeeDetails details
	 *
	 * @return  Boolean  True on mail sent and false for failed case.
	 *
	 * @since  2.8.0
	 */
	public static function rejectEnrollmentMail($eventDetails, $attendeeDetails)
	{
		$app                  = Factory::getApplication();
		$eventCreator         = Factory::getUser($eventDetails->getCreator());
		$eventOwnerEmail      = $eventCreator->email;
		$attendee             = JT::attendee($attendeeDetails->id);

		$attendeeData = self::getExtrafieldsData($eventDetails, $attendeeDetails);
		$client       = "com_jticketing";

		$eventDetails->creator           = $eventCreator->name;
		$replacements                    = new stdClass;
		$replacements->buyer             = new stdClass;
		$replacements->buyer->first_name = empty(Factory::getUser($attendeeDetails->owner_id)->name) ?
		$attendee->getFirstName() : Factory::getUser($attendeeDetails->owner_id)->name;
		$replacements->buyer->name       = $attendee->getFirstName() . ' ' . $attendee->getLastName();
		$replacements->buyer->site       = $app->get('sitename', '');
		$replacements->event             = $eventDetails;
		$replacements->attendee          = $attendeeData;
		$replacements->buyer->enrollmentId = $eventDetails->enrollmentId = $attendee->enrollment_id;

		$replacements->user           = new stdClass;
		$replacements->user->name     = $attendee->getFirstName() . " " . $attendee->getLastName();
		$replacements->user->username = Factory::getUser($attendee->owner_id)->username;
		$replacements->user->email    = Factory::getUser($attendee->owner_id)->email;
		$replacements->event->site    = $app->get('sitename', '');
		$replacements->attendee->phone = ($attendee->getPhoneNumber()) ?
		$attendee->getPhoneNumber() : '';
		$replacements->attendee->email = $attendee->getEmail();

		$options = new Registry;
		$options->set('subject', $eventDetails);

		// Mail to Site administrator
		$key = "r-mailAdmin";
		$adminUsers = JT::utilities()->getAdminUsers();

		foreach ($adminUsers as $user)
		{
			$recipients['email']['to'][] = $user->email;
			$recipients['web']['to'][] = $user->email;
			$recipients[] = $user;
		}
		
		$options->set('to', $user->id);
		$options->set('from', $user->id);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $eventDetails);

		// Mail to Event Creator
		$recipients                = array();
		$key                       = "r-mailcreator";
		$recipients['email']['to'][]  = trim($eventOwnerEmail);
		$recipients['web']['to'][]  = trim($eventOwnerEmail);
		$recipients[]                 = $eventCreator;
		
		$options->set('to', $eventDetails->getCreator());
		$options->set('from', $eventDetails->getCreator());

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);

		$options = new Registry;
		$options->set('subject', $eventDetails);

		// Mail to Event buyer
		$recipients                  = array();
		$key                         = "r-email";
		$recipients['email']['to'][] = $attendee->getEmail();
		$recipients['web']['to'][] = $attendee->getEmail();
		
		$options->set('to', $attendee->owner_id);
		$options->set('from', $attendee->owner_id);

		if (empty($attendee->getPhoneNumber()))
		{
			$recipients[] = Factory::getUser($attendee->owner_id);
		}
		else
		{
			$recipients['sms'][] = $attendee->getPhoneNumber();
		}

		return Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Method to get the event fields data and tjfield data
	 *
	 * @param   object  &$eventDetails    event details
	 * @param   object  $attendeeDetails  attendeeDetails details
	 *
	 * @return  stdClass  attendee fields object.
	 *
	 * @since   2.8.0
	 */
	public static function getExtrafieldsData(&$eventDetails, $attendeeDetails)
	{
		/* @var $this JticketingMailHelper */
		$mailHelper  = new JticketingMailHelper;
		$extraFields = $mailHelper->getDataExtra(array('client' => 'com_jticketing.event'), $eventDetails->getId());

		// Formatting event tjfields key value array.
		if (!empty($extraFields))
		{
			foreach ($extraFields as $field)
			{
				if (!is_array($field->value) && !empty($field->value))
				{
					$name                = $field->name;
					$eventDetails->$name = $field->value;
				}
				else
				{
					$name                = $field->name;
					$value               = $field->value;
					$eventDetails->$name = $value[0]->value;
				}
			}
		}

		// Add attendee related information in format of std object
		/* @var $attendeeFieldVal JTicketingAttendeeFieldValues */
		$attendeeFieldVal = JT::attendeefieldvalues();
		$allFields = [];

		if ($attendeeDetails && $attendeeDetails->id)
		{
			$attendeeTjfields = $attendeeFieldVal->loadByAttendeeId($attendeeDetails->id, '', 'com_tjfields.com_jticketing.ticket');
			$coreFields       = $attendeeFieldVal->loadByAttendeeId($attendeeDetails->id);
			$allFields        = array_merge($attendeeTjfields, $coreFields);
		}
		$attendeeData     = new stdClass;

		if (!empty($allFields))
		{
			foreach ($allFields as $field)
			{
				$fieldKey                = $field->name;
				$attendeeData->$fieldKey = $field->field_value;
			}
		}

		return $attendeeData;
	}

	/**
	 * Method to get the event Joomla fields  data.
	 *
	 * @param   Array  $customFields  Event  Fields
	 *
	 * @return  stdClass  Event  fields  and  values  object.
	 *
	 * @since   2.9.0
	 */
	public static function getJoomlaFieldsData($customFields)
	{
		$fieldValuesObject = new stdClass;

			foreach ($customFields as $field)
			{
				$name = $field->name;

				// Convert JSON string to Array
				$fieldArray = json_decode($field->fieldValue, true);

				if ($field->type === "unifiedrepeatable")
				{
					foreach ($fieldArray as $fieldKey => $fieldValue)
					{
						if ($field->type === "unifiedrepeatable")
						{
							$fieldNewName = $name . $fieldKey;
							$fieldValuesObject->$fieldNewName = !empty($fieldValue) ? $fieldValue : "";
						}
					}
				}
				elseif ($field->type === "repeatable")
				{
					$string    = array();
					$subFields = array();

					foreach ($fieldArray as $fieldKey => $fieldValue)
					{
						$subFields = array_keys($fieldValue);
					}

					foreach ($subFields as $subField)
					{
						$fieldNewName = $name . '-' . strtolower(str_replace(' ', '-', $subField));
						$subFieldValues = array_column($fieldArray, $subField);
						$string[$fieldNewName] = implode("\n", $subFieldValues);
						$fieldValuesObject->$fieldNewName = !empty($string[$fieldNewName]) ? $string[$fieldNewName] : "";
					}
				}
				else
				{
					$fieldValuesObject->$name = !empty($field->fieldValue) ? $field->fieldValue : "";
				}
			}

			return $fieldValuesObject;
	}

	/**
	 * Send enrollment email to creator and manager of event
	 *
	 * @param   int  $eventId     event id
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return  Boolean  True on mail sent and false for failed case.
	 *
	 * @since   2.8.0
	 */
	public static function enrollmentNotificationMail($eventId, $attendeeId)
	{
		$integration  = JT::getIntegration();

		// Get event details as per integration.
		$eventDetails = JT::event()->loadByIntegration($eventId);

		if ($integration != 'com_jticketing')
		{
			$eventDetails->title = $eventDetails->event->title;
		}

		// Get attendee details.
		$attendeeDetails = JT::attendee($attendeeId);

		// Get event creator id.
		$creatorId  = $eventDetails->getCreator();
		$app        = Factory::getApplication();
		$mailfrom   = $app->get('mailfrom');
		$fromname   = $app->get('fromname');
		$toemail    = array();
		$toemail_cc = array();

		$attendeeDetails->attendee_name = Factory::getUser($attendeeDetails->owner_id)->name;

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_hierarchy/models/hierarchy.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_hierarchy/models/hierarchy.php'; }
		$hierarchyModel = BaseDatabaseModel::getInstance('hierarchy', 'HierarchyModel', array('ignore_request' => true));

		// Get manager of attendee
		$managerList = $hierarchyModel->getManagers($attendeeDetails->owner_id);

		if (is_array($managerList))
		{
			foreach ($managerList as $key => $manager)
			{
				$toemail[] = Factory::getUser($manager->reports_to)->email;
			}
		}

		// Replacement tags used notification template
		$replacementTags           = new stdClass;
		$replacementTags->event    = $eventDetails;
		$replacementTags->attendee = $attendeeDetails;

		$replacementTags->event->admin_attendeelist = Uri::root() . 'administrator/index.php?option=com_jticketing&view=attendees&filter[events]=' .
		$attendeeDetails->event_id;

		$replacementTags->event->site_attendeelist = Route::_(
			Uri::root() . 'index.php?option=com_jticketing&view=attendees&filter[events]=' . $attendeeDetails->event_id, false
		);

		$replacementTags->event->title = $eventDetails->title = $eventDetails->getTitle();
		$eventDetails->attendee_name   = Factory::getUser($attendeeDetails->owner_id)->name;

		$options = new Registry;
		$options->set('subject', $eventDetails);

		$creator = Factory::getUser($creatorId);

		// If manager is assigned for attendee then email creator will be in CC
		if (!empty($toemail))
		{
			$toemail_cc = trim($creator->email);
		}
		else
		{
			$toemail[] = $creator->email;
		}

		$recipients = array (

			Factory::getUser($creator->id),

			// Add specific to, cc (optional), bcc (optional)
			'email' => array (
				'to' => $toemail,
				'cc' => array ($toemail_cc)
			),
			'web' => array (
				'to' => $toemail
			)
		);

		$options->set('to', $manager->reports_to);
		$options->set('from', $manager->reports_to);

		// Email send method of TJNotifications
		return Tjnotifications::send('com_jticketing', 'enrolmentDone', $recipients, $replacementTags, $options);
	}

	/**
	 * Method to Send email to event creators after ticket purchased
	 *
	 * @param   string  $id  id in jticketing_order
	 *
	 * @return  void
	 *
	 * @since   2.9.0
	 */
	public static function sendEventPurchaseEmail($id)
	{
		/** @var $order JTicketingOrder */
		$orderinfo = JT::order($id);
		$eventinfo = JT::event()->loadByIntegration($orderinfo->event_details_id);
		$userInfo  = $orderinfo->getbillingdata();
		$utilities = JT::utilities();

		$creatorInfo = Factory::getUser($eventinfo->getCreator());
		$ticketTypes = $orderinfo->getItemTypes();

		foreach ($ticketTypes as $item)
		{
			// Get the total prize, count and free related to order item and type id.
			$orderItemDetails = $orderinfo->getItemsByType($item->id);
			$ticketCount      = $orderItemDetails->count;
		}

		$config    = Factory::getConfig();
		$mailFrom1 = $config->get('mailfrom');
		$fromName1 = $config->get('fromname');

		$options      = new Registry;
		$replacements = new stdClass;
		$subject      = new stdClass;

		$buyerDetails 			  = new stdClass;
		$buyerDetails->quantity   = $ticketCount;
		$buyerDetails->firstname  = isset($userInfo->firstname) ? $userInfo->firstname : '';
		$buyerDetails->lastname   = isset($userInfo->lastname) ? $userInfo->lastname : '';

		$eventinfo->creator       = isset($creatorInfo->name) ? $creatorInfo->name : '';
		$eventinfo->eventTitle    = $eventinfo->getTitle();
		$eventinfo->startDate     = $utilities->getFormatedDate($eventinfo->getStartDate());
		$eventinfo->endDate       = $utilities->getFormatedDate($eventinfo->getEndDate());

		$replacements->event = $eventinfo;
		$replacements->buyer = $buyerDetails;
		$replacements->buyer->orderid_with_prefix = $orderinfo->order_id;

		$replacements->event->eventTitle = $eventinfo->getTitle();

		$subject->firstname  = $buyerDetails->firstname;
		$subject->lastname   = $buyerDetails->lastname;
		$subject->title      = $replacements->event->eventTitle;
		$subject->orderid_with_prefix = $orderinfo->order_id;

		$options->set('subject', $subject);

		$client = "com_jticketing";
		$key    = "eventPurchaseNotification";

		$recipients = array (

			Factory::getUser($creatorInfo->id),

			// Add specific to, cc (optional), bcc (optional)
			'email' => array (
				'to' => array ($creatorInfo->email)
			),
			'web' => array (
				'to' => array ($creatorInfo->email)
			)
		);
		
		$options->set('to', $creatorInfo->id);
		$options->set('from', $creatorInfo->id);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Method to Send email to site admin for event approval
	 *
	 * @param   integer  $eventId  event id
	 *
	 * @return  void
	 *
	 * @since   3.3.2
	 */
	public static function sendAdminApproval($eventId)
	{
		$com_params   = JT::config();
		$recipients   = array();
		$replyToEmail = $com_params->get('send_approval_to');
		$eventinfo    = JT::event($eventId);
		$creatorInfo  = Factory::getUser($eventinfo->getCreator());

		$options      = new Registry;
		$replacements = new stdClass;
		$subject      = new stdClass;
		$replacements->event = new stdClass;
		$adminUsers   = array();

		$replacements->event->creator    = isset($creatorInfo->name) ? $creatorInfo->name : '';
		$replacements->event->eventTitle = $eventinfo->getTitle();
		$replacements->event->link       = Uri::root() . 'administrator/index.php?option=com_jticketing&view=events';

		$subject->eventTitle   = $replacements->event->eventTitle;
		$subject->eventCreator = $replacements->event->creator;

		$options->set('subject', $subject);

		$client = "com_jticketing";
		$key    = "adminApprovalNotification";

		if ($com_params->get('notify_admin_user'))
		{
			// Mail to site administrator
			$adminUsers = JT::utilities()->getAdminUsers();
		}

		if (isset($replyToEmail))
		{
			$replyToEmail = explode(",", $replyToEmail);
		}

		if (!empty($adminUsers))
		{
			foreach ($adminUsers as $user)
			{
				$recipients['email']['to'][] = $user->email;
				$recipients['web']['to'][] = $user->email;

				// Send SMS to site administrator
				$recipients[] = $user;
			}

			$recipients['email']['cc']   = $replyToEmail;
			
			$options->set('to', $user->id);
			$options->set('from', $user->id);
		}
		else
		{
			$recipients['email']['to'] = $replyToEmail;
			$recipients['web']['to'] = $replyToEmail;
		}

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Method to Send notification to event creator for event is approved
	 *
	 * @param   integer  $eventId  event id
	 *
	 * @return  void
	 *
	 * @since   3.3.3
	 */
	public static function eventApproved($eventId)
	{
		$eventinfo   = JT::event($eventId);
		$creatorInfo = Factory::getUser($eventinfo->getCreator());

		$options      = new Registry;
		$replacements = new stdClass;
		$subject      = new stdClass;
		$replacements->event = new stdClass;
		$replacements->event->creator    = isset($creatorInfo->name) ? $creatorInfo->name : '';
		$replacements->event->eventTitle = $eventinfo->getTitle();

		$link                      = $eventinfo->getUrl(false);
		$itemId                    = JT::utilities()->getItemId($link, 0, 0);
		$replacements->event->link = Route::link('site', $link . '&Itemid=' . $itemId, 0, 0, true);
		$subject->eventTitle   = $replacements->event->eventTitle;

		$options->set('subject', $subject);

		$client = "com_jticketing";
		$key    = "eventApprovedNotification";

		$recipients['email']['to'][] = $creatorInfo->email;
		$recipients['web']['to'][] = $creatorInfo->email;

		// Send SMS to creator
		$recipients[] = $creatorInfo;
		
		$options->set('to', $eventinfo->getCreator());
		$options->set('from', $eventinfo->getCreator());

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Method to send notification emails to event attendees
	 *
	 * @param   integer  $eventId  ID of the event
	 * @param   array    $emails   List of attendee email addresses
	 *
	 * @return  void
	 *
	 * @since   5.1.0
	 */
	public static function sendEmailNotificationToAttendees($eventId, $emails)
	{
		$client = "com_jticketing";
		$key    = "eventAttendeeNotification";

		$eventInfo   = JT::event($eventId);
		$eventTitle  = $eventInfo->getTitle();
		$creatorInfo = Factory::getUser($eventInfo->getCreator()); // Event creator ka info le raha hu

		if (!empty($emails))
		{
			$recipients = [];
			foreach ($emails as $email)
			{
				$recipients['email']['to'][] = $email;
				$recipients['web']['to'][] = $email;
			}

			$options      = new Registry;
			$replacements = new stdClass;
			$subject      = new stdClass;

			// Replacements ke andar event ka title aur message add kiya
			$replacements->event = new stdClass;
			$replacements->event->eventTitle = $eventTitle;

			$subject->eventTitle = $replacements->event->eventTitle;
			$options->set('subject', $subject);
			$options->set('from', $creatorInfo->email);
			$options->set('body', $replacements->event->message);

			Tjnotifications::send($client, $key, $recipients, $replacements, $options);
		}
	}

	/**
	 * Method Sends post-event feedback emails to attendees for a given event.
	 * Triggers the feedback form plugin to get the feedback URL and uses the 
	 * Tjnotifications system to send the email with dynamic content.
	 *
	 * @param   int    $eventId  ID of the event for which to send feedback email.
	 * @param   array  $emails   List of attendee email addresses.
	 *
	 * @return  void
	 * @since   5.1.0
	*/
	public static function sendPostEventFeedbackEmail($eventId, $emails)
	{
		PluginHelper::importPlugin('jticketing');

		// Create data array with required fields
		$data = [
			'eventid' => $eventId
		];

		// Now trigger the plugin with correct data
		$feedbackFormLinkArr = Factory::getApplication()->triggerEvent('onJtGetFeedbackFormUrl', array($data));
		$feedbackFormLink = !empty($feedbackFormLinkArr) ? $feedbackFormLinkArr[0] : '';

		$client = "com_jticketing";
		$key    = "postEventFeedbackNotification";

		$eventInfo = JT::event($eventId);

		if (!$eventInfo || empty($emails))
		{
			return;
		}

		$replacements = new stdClass;
		$replacements->event = new stdClass;
		$replacements->event->eventTitle = $eventInfo->getTitle();
		$replacements->event->feedbackLink = $feedbackFormLink;

		$recipients = [
			'email' => ['to' => $emails],
			'web'   => ['to' => $emails]
		];

		$options = new Registry;
		$options->set('from', Factory::getUser($eventInfo->getCreator())->email);
		$options->set('body', '');

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}
}
