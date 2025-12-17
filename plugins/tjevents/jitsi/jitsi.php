<?php
/**
 * @package     Jticketing.Plugin
 * @subpackage  Tjevents.jitsi
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     http:/www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Firebase\JWT\JWT;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

JLoader::discover("JTicketingEvent", JPATH_PLUGINS . '/tjevents/jitsi/jitsi');
JLoader::registerNamespace('Firebase\JWT', JPATH_PLUGINS . '/tjevents/jitsi/jitsi/Firebase', false, false, 'psr4');

/**
 * Class for Jitsi Tjevents Plugin
 *
 * @since  3.0.0
 */
class PlgTjeventsJitsi extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  3.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Return the type of the plugin
	 *
	 * @return  array
	 *
	 * @since  3.0.0
	 */
	public function onJtGetContentInfo()
	{
		$obj = array();
		$obj['name'] = 'Jitsi';
		$obj['id']   = $this->_name;

		return $obj;
	}

	/**
	 * Create a JWT token for logged in user
	 *
	 * @param   JTicketingAttendee         $attendee  Attendee class object
	 * @param   JTicketingEventJticketing  $event     Event class object
	 *
	 * @return  string
	 *
	 * @since  3.0.0
	 */
	public function onJtGenerateJwtToken(JTicketingAttendee $attendee, JTicketingEventJticketing $event)
	{
		$user = Factory::getUser();

		$name = $attendee->getFirstName() . " " . $attendee->getLastName();

		if (empty($name))
		{
			$name = $user->name;
		}

		$params = new Registry($event->getParams());
		$venue = JT::venue($event->venue);
		$jitsiConfig = new Registry($venue->getParams());
		$domain = new Uri($jitsiConfig->get('domain'));

		/**
		 *
		 * Jitsi JWT payload
		 * https://github.com/jitsi/lib-jitsi-meet/blob/master/doc/tokens.md
		 */
		$token = [
				"context" => array("user" => array(
						"name" => $name,
						"email" => $attendee->getEmail()
				)),
				"aud" => "JTicketing",
				"iss" => $jitsiConfig->get('apikey'),
				"sub" => $domain->getHost(),
				"room" => $params->get('jitsi.roomId')
		];

		return JWT::encode($token, $jitsiConfig->get('apisecret'));
	}

	/**
	 * Method to store the attendee details in the database
	 *
	 * @return  boolean
	 *
	 * @since  3.0.0
	 */
	public function onAjaxStoreAttendee()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			return new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
		}

		$eventId = $app->input->getInt('eventid');
		$event = JT::event($eventId);
		$attendee = $app->input->getInt('attendee');
		$attendee = JT::attendee($attendee);

		if (!$event->isOnline() || !$attendee->id || $attendee->status != COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_APPROVED
			|| $event->integrationId != $attendee->event_id)
		{
			return new JsonResponse(null, null, true);
		}

		$action   = $app->input->getString('action');
		$email    = $attendee->getEmail();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('intime'));
		$query->from($db->quoteName('#__jitsi_attendee'));
		$query->where($db->quoteName('email') . ' = ' . $db->q($email));
		$query->where($db->quoteName('eventid') . ' = ' . $db->q($event->getId()));
		$db->setQuery($query);
		$existing = $db->loadResult();

		if ($action == 'joined')
		{
			if ($existing)
			{
				$query = $db->getQuery(true);
				$fields = array(
						$db->quoteName('intime') . ' = ' . $db->q(Factory::getDate()->toSql()),
						$db->quoteName('outtime') . ' = ' . $db->q($db->getNullDate())
				);

				$conditions = array(
						$db->quoteName('email') . ' = ' . $db->q($email),
						$db->quoteName('eventid') . ' = ' . $event->getId()
				);

				$query->update($db->quoteName('#__jitsi_attendee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$db->execute();

				return true;
			}

			$query = $db->getQuery(true);
			$columns = array('email', 'intime', 'outtime', 'eventid');
			$values = array($db->q($email), $db->q(Factory::getDate()->toSql()), $db->q($db->getNullDate()), $event->getId());
			$query->insert('#__jitsi_attendee');
			$query->columns($db->quoteName($columns));
			$query->values(implode(',', $values));
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		/**
		 * Dump data to the table
		 * We need to calculate the total time if the end time is available for the current user
		 * This will avoid misleading calculations
		 */
		if ($existing == $db->getNullDate())
		{
			return true;
		}

		$inTime = new DateTime($existing);
		$sinceInTime = $inTime->diff(new DateTime(Factory::getDate()->toSql()));
		$minutes = $sinceInTime->days * 24 * 60;
		$minutes += $sinceInTime->h * 60;
		$minutes += $sinceInTime->i;

		$query = $db->getQuery(true);
		$fields = array(
				$db->quoteName('timespent') . ' =  timespent + ' . (int) $minutes,
				$db->quoteName('outtime') . ' = ' . $db->q(Factory::getDate()->toSql())
		);

		$conditions = array(
				$db->quoteName('email') . ' = ' . $db->q($email),
				$db->quoteName('eventid') . ' = ' . $event->getId()
		);

		$query->update($db->quoteName('#__jitsi_attendee'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Generate a layout html
	 *
	 * @param   JTicketingEventJticketing  $event     Event class object
	 * @param   JTicketingAttendee         $attendee  Attendee class object
	 *
	 * @return  string
	 *
	 * @since  3.0.0
	 */
	public function onJtRenderLayoutJitsi($event, $attendee)
	{
		$path = PluginHelper::getLayoutPath('tjevents', 'jitsi');

		ob_start();
		include $path;
		$text = ob_get_clean();

		return $text;
	}
}
