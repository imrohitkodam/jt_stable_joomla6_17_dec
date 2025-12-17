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

require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';

/**
 * JTicketing event class.
 *
 * @since  2.5.0
 */
class JTicketingEventEasysocial extends JTicketingEvent
{
	/**
	 * holds the property of the easysocial event object
	 *
	 * @var    SocialEvent
	 * @since  2.5.0
	 */
	public $event;

	/**
	 * holds the already loaded instances of the event
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $loadedESEvent = array();

	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   int  $eventId  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($eventId)
	{
		parent::__construct($eventId, 'com_easysocial');

		if (empty(self::$loadedESEvent[$eventId]))
		{
			self::$loadedESEvent[$eventId] = ES::event($eventId);
		}

		$this->event = self::$loadedESEvent[$eventId];
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
		return $this->event->category_id;
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
	 * This method will return the event Id
	 *
	 * @return  string  Event Id
	 *
	 * @since   2.5.0
	 */
	public function getId()
	{
		return $this->event->id;
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
		return $this->event->getEventStart()->toSql();
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
		return $this->event->getEventEnd()->toSql();
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
		return $this->event->creator_uid;
	}

	/**
	 * This method will return the event avtar
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getAvatar()
	{
		return $this->event->getAvatar(SOCIAL_AVATAR_LARGE);
	}

	/**
	 * This method will return the event avtar
	 *
	 * @return  string|void
	 *
	 * @since   2.5.0
	 */
	public function getVenueDetails()
	{
		if ($this->event->latitude)
		{
			return $this->event->address;
		}

		return;
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
		return $this->event->getPermalink($xhtml = true, $external = false, $layout = 'item', $sef);
	}

	/**
	 * Determine if the event is end
	 *
	 * @return  boolean return true if the event is over
	 *
	 * @since   2.5.0
	 */
	public function isOver()
	{
		return $this->event->isOver();
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
	 * Get state of the event
	 *
	 * @return  Integer
	 *
	 * @since   3.2.0
	 */
	public function getState()
	{
		return $this->event->state;
	}

	/**
	 * Get EasySocial event specific total seats
	 *
	 * @return  int
	 *
	 * @since   3.2.0
	 */
	public function getEventTotalSeats()
	{
		$event = ES::event($this->event->id);

		return $event->getTotalSeats();
	}
}
