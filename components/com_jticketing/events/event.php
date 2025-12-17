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
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/jlike.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/jlike.php'; }

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;

/**
 * JTicketing triggers class for event.
 *
 * @since  2.1
 */
class JticketingTriggerEvent
{
	/**
	 * Client for which certificate is issued
	 *
	 * @var    string
	 * @since  2.7.0
	 */
	protected static $certificateClient = 'com_jticketing.event';

	public $eventFormModel;

	/**
	 * Method acts as a consturctor
	 *
	 * @since   2.1
	 */
	public function __construct()
	{
		$this->jtJlikeHelper    = new JticketingJlikeHelper;
		$this->contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');
		$this->eventFormModel = JT::model('EventForm');
	}

	/**
	 * Trigger for after event save
	 *
	 * @param   Array    $eventDetails  Campaign Details
	 * @param   Boolean  $isNew         Is new
	 *
	 * @return  void
	 */
	public function onAfterEventSave($eventDetails, $isNew)
	{
		if ($isNew)
		{
			$link = "index.php?option=com_jticketing&view=event&id=" . $eventDetails['eventId'];

			// Store content for respective event
			$contentData = array(
				'url' => $link,
				'type' => "",
				'element' => "com_jticketing.event",
				'element_id' => $eventDetails['eventId'],
				'title' => $eventDetails['title']
			);

			$event = JT::event($eventDetails['eventId']);

			if ($event->isOnline())
			{
				$event->updateParamsAfterEventSave();
			}

			return $this->eventFormModel->createContent($contentData);
		}
		else
		{
			// Store content for respective event
			$contentData = array(
				'url' => $link,
				'type' => "",
				'element' => "com_jticketing.event",
				'element_id' => $eventDetails['eventId'],
				'title' => $eventDetails['title']
			);

			return $this->eventFormModel->updateContent($contentData);
		}
	}

	/**
	 * Trigger for after event delete
	 *
	 * @param   Array  $eventDetails  Event Details
	 *
	 * @return  void
	 */
	public function onAfterEventDelete($eventDetails)
	{
		$event = JT::event()->loadByIntegration($eventDetails[0]->id);
		$link  = $event->getUrl();

		$contentData = array(
			'url' => $link,
			'type' => "",
			'element' => "com_jticketing.event",
			'element_id' => $eventDetails[0]->id,
			'title' => $eventDetails[0]->title
		);

		// Get content Id
		$contentId = $this->contentFormModel->getContentID($contentData);

		$data = array('id' => $contentId);
		$this->contentFormModel->delete($data);
	}

	/**
	 * Trigger for event checkin
	 *
	 * @param   Array  $checkinDetails  Checkin Details
	 * @param   Array  $params          other required data
	 *
	 * @return  Array
	 */
	public function onAfterJtEventCheckin($checkinDetails, $params)
	{
		$returnArray = array ();
		$integration = JT::getIntegration();
		$config      = JT::config();

		// Insert or update todo depends on the data passed
		$this->jtJlikeHelper->saveTodo($params);

		// Issue Certificate
		$event = JT::event()->loadByIntegration($params['eventId'], $integration);

		// Check certification enabled
		if ($event->isCertificationEnabled() && !empty($event->params) && $checkinDetails['checkin'] == 1)
		{
			$certificateTemplate = $event->getCertificateTemplate();

			// Check if event is saved with Template params
			if (!empty($certificateTemplate))
			{
				$downloadCertificateUrlOpts             = array ();
				$downloadCertificateUrlOpts['absolute'] = true;

				if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php'; }

				$attendeeObj = JT::attendee($checkinDetails['attendeeId']);

				$checkIfIssued = $attendeeObj->checkCertificateIssued();

				// Check if certificate already issued
				if (!$checkIfIssued[0]->id)
				{
					$tjCert = TJCERT::Certificate();

					$certificateExpiryDate   = '';
					$certificateExpiryInDays = $event->getCertificateExpiry();

					if (!empty($certificateExpiryInDays) && $certificateExpiryInDays > 0)
					{
						$certificateExpiryDate = Factory::getDate($event->getStartDate(), 'UTC');
						$certificateExpiryDate->modify("+" . $certificateExpiryInDays . " days");
						$certificateExpiryDate = $certificateExpiryDate->toSql();
					}

					$tjCert->setCertificateTemplate($certificateTemplate);
					$tjCert->setClient(self::$certificateClient);
					$tjCert->setClientId($event->id);
					$tjCert->setClientIssuedTo($attendeeObj->id);
					$tjCert->setUserId($attendeeObj->owner_id);

					if ($config->get('collect_attendee_info_checkout') == 1)
					{
						$attendeeFieldVal   = JT::attendeefieldvalues();
						$fieldsValues       = $attendeeFieldVal->loadByAttendeeId($attendeeObj->id);

						$setClientIssuedToName = '';

						if (!empty($fieldsValues[3]->field_value))
						{
							$setClientIssuedToName = $fieldsValues[3]->field_value . ' ' . $fieldsValues[2]->field_value;
						}

						$tjCert->setClientIssuedToName($setClientIssuedToName);
					}

					if (!empty($certificateExpiryDate))
					{
						$tjCert->setExpiry($certificateExpiryDate);
					}

					// Get Certificate Replacements
					// Get event replacement tags
					$eventReplacements = $event->getReplacementTags();

					// Get checkin replacement tags
					$checkinModel           = JT::model('checkin', array("ignore_request" => true));
					$checkinReplacementTags = $checkinModel->getReplacementTags($attendeeObj);

					// Get ticket replacement tags
					$ticketReplacements = JT::tickettype($attendeeObj->ticket_type_id)->getReplacementTags();

					$replacements = (object) array_merge((array) $eventReplacements, (array) $checkinReplacementTags, (array) $ticketReplacements);

					$options = new Registry;
					$certificateObj = $tjCert->issueCertificate($replacements, $options);

					$returnArray['certificateUrl'] = Text::sprintf('COM_JTICKETING_EVENT_CERTIFICATE_LINK',
						$certificateObj->getDownloadUrl($downloadCertificateUrlOpts)
					);

					$returnArray['certificateRawUrl'] = $certificateObj->getDownloadUrl($downloadCertificateUrlOpts);
				}
				else
				{
					$returnArray['certificateUrl'] = Text::sprintf('COM_JTICKETING_EVENT_CERTIFICATE_LINK',
						TJCERT::Certificate($checkIfIssued[0]->id)->getDownloadUrl($downloadCertificateUrlOpts)
					);

					$returnArray['certificateRawUrl'] = TJCERT::Certificate($checkIfIssued[0]->id)->getDownloadUrl(
						$downloadCertificateUrlOpts
					);
				}
			}
		}

		return $returnArray;
	}
}
