<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
require_once JPATH_ROOT . '/components/com_community/libraries/core.php';

/**
 * JTicketing community event class.
 *
 * @since  2.5.0
 */
class JTicketingEventCommunity extends JTicketingEvent
{
	/**
	 * holds the property of the easysocial event object
	 *
	 * @var    CTableEvent
	 * @since  2.5.0
	 */
	public $event, $eventid;

	/**
	 * holds the already loaded instances of the event
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $loadedJSEvent = array();

	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   int  $eventId  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($eventId)
	{
		parent::__construct($eventId, 'com_community');

		if (empty(self::$loadedJSEvent[$eventId]))
		{
			$event = Table::getInstance('Event', 'CTable');
			$event->load($this->eventid);
			self::$loadedJSEvent[$eventId] = $event;
		}

		$this->event = self::$loadedJSEvent[$eventId];
	}

	/**
	 * This method will return the event title
	 *
	 * @return  string  Event title
	 *
	 * @since   2.5.0
	 */
	public function getId()
	{
		return $this->event->id;
	}

	/**
	 * This method will return the event avatar
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getAvatar()
	{
		return $this->event->getCover();
	}

	/**
	 * This method will return the category Id
	 *
	 * @return  integer  Category Id
	 *
	 * @since   2.5.0
	 */
	public function getCategory()
	{
		return $this->event->catid;
	}

	/**
	 * This method will return the event title
	 *
	 * @return  string  Event title
	 *
	 * @since   2.5.0
	 */
	public function getTitle()
	{
		return $this->event->title;
	}

	/**
	 * This method will return the event start date
	 *
	 * @return  string  Event startDate
	 *
	 * @since   2.5.0
	 */
	public function getStartDate()
	{
		// As JomSocial not save date in UTC, convert date in UTC and then return
		$offset = Factory::getUser()->getTimezone();

		return Factory::getDate($this->event->startdate, $offset)->toSql();
	}

	/**
	 * This method will return the event end date
	 *
	 * @return  string  Event enddate
	 *
	 * @since   2.5.0
	 */
	public function getEndDate()
	{
		// As JomSocial not save date in UTC, convert date in UTC and then return
		$offset = Factory::getUser()->getTimezone();

		return Factory::getDate($this->event->enddate, $offset)->toSql();
	}

	/**
	 * This method will return the event creator Joomla user id
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getCreator()
	{
		return $this->event->creator;
	}

	/**
	 * This method will return the event venue details
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getVenueDetails()
	{
		return $this->event->location;
	}

	/**
	 * This method will return the event URL
	 *
	 * @param   boolean  $sef  flag to get sef or non sef URL
	 *
	 * @return  string
	 *
	 * @since   2.5.0
	 */
	public function getUrl($sef = true)
	{
		$handler = CEventHelper::getHandler($this->event);

		return $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewevent&eventid=' . $this->event->id,
			$xhtml = true, $external = false, $sef
		);
	}

	/**
	 * Get state of the event
	 *
	 * @return  Integer
	 *
	 * @since   3.2.0
	 */
	public function getState()
	{
		return $this->event->published;
	}

	/**
	 * Method to check if event is multiple day
	 *
	 * @return  integer
	 *
	 * @since   3.2.0
	 */
	public function isMultiDay()
	{
		$startdate 	= new DateTime($this->getStartDate());
		$enddate 	= new DateTime($this->getEndDate());

		if ($startdate->format('Y-m-d') < $enddate->format('Y-m-d'))
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Method to save the Event object to the database
	 *
	 * @param   array  $data  The event data to be bind with the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2.0
	 */
	public function save($data)
	{
		try
		{
			$result = $this->event->load($data['id']);

			if (!$result)
			{
				$this->setError($this->event->getError());

				return false;
			}

			return ($this->event->bind($data) && $this->event->store());
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Get jomsocial event specific total seats
	 *
	 * @return  int
	 *
	 * @since   3.2.0
	 */
	public function getEventTotalSeats()
	{
		return $this->event->ticket;
	}
}
