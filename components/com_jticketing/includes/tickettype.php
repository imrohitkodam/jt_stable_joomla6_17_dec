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
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Object\CMSObject;
use Joomla\String\StringHelper;
use Joomla\CMS\Language\Text;

/**
 * JTicketing ticket type class.
 *
 * @since  2.5.0
 */
class JTicketingTickettype extends CMSObject
{
	/**
	 * The auto incremental primary key of the ticket type
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $id = 0;

	/**
	 * Ticket type title
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $title = '';

	/**
	 * Parent order ID - Not used anywhere yet.
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $desc = '';

	/**
	 * Ticket end date
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	public $ticket_enddate = '';

	/**
	 * Ticket type price
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $price = 0;

	/**
	 * Fee apply against the ticket, not used.
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $deposit_fee = 0;

	/**
	 * The ticket count stored while creating the ticket type
	 * Do not use it as a available ticket count
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $available = 0;

	/**
	 * Total available tickets to buy
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $count = 0;

	/**
	 * Is ticket types have unlimited seats or no
	 * 1=unlimited 0=limited
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $unlimited_seats = 0;

	/**
	 * Integration xref table primary key - ID
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $eventid = 0;

	/**
	 * Maximum ticket one can purchase against this ticket type
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $max_limit_ticket = 0;

	/**
	 * Joomla user group ID, who can book tickets for this type
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $access = 0;

	/**
	 * Ticket type state, published or unpublished
	 *
	 * @var    integer
	 * @since  2.5.0
	 */
	public $state = 0;

	/**
	 * holds the already loaded instances of the Ticket Type
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $ticketTypeObj = array();

	/**
	 * Constructor activating the default information of the order
	 *
	 * @param   int  $id  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}

		if (! $this->id)
		{
			$nulldate = Factory::getDbo()->getNullDate();

			// Initialize the default variables
			$this->ticket_enddate = $nulldate;
		}
	}

	/**
	 * Returns the global ticket type object
	 *
	 * @param   integer  $id  The primary key of the ticket type to load (optional).
	 *
	 * @return  JTicketingTickettype  The ticket type object.
	 *
	 * @since   2.5.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingTickettype;
		}

		if (empty(self::$ticketTypeObj[$id]))
		{
			self::$ticketTypeObj[$id] = new JTicketingTickettype($id);
		}

		return self::$ticketTypeObj[$id];
	}

	/**
	 * Method to load a ticket type properties
	 *
	 * @param   int  $id  The order id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 */
	public function load($id)
	{
		$table = JT::table("tickettypes");

		if ($table->load($id))
		{
			$this->setProperties($table->getProperties());

			$this->id = (int) $table->get('id');
			$this->price = (float) $table->get('price', 0, 'FLOAT');
			$this->deposit_fee = (float) $table->get('deposit_fee');
			$this->available = (int) $table->get('available');
			$this->count = (int) $table->get('count');
			$this->unlimited_seats = (int) $table->get('unlimited_seats');
			$this->eventid = (int) $table->get('eventid');
			$this->max_limit_ticket = (int) $table->get('max_limit_ticket');
			$this->access = (int) $table->get('access');
			$this->state = (int) $table->get('state');

			return true;
		}

		return false;
	}

	/**
	 * Method to save the Ticket type object to the database
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5.0
	 * @throws  \RuntimeException
	 */
	public function save()
	{
		$table = JT::table('tickettypes');

		// Allow an exception to be thrown.
		try
		{
			$table->bind($this->getProperties());

			// Check and store the object.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Load the ticket type object of current ticket type id.
			if ($table->store())
			{
				$this->load($this->id);

				return true;
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return false;
	}

	/**
	 * Format the price of ticket type object
	 *
	 * @param   boolean  $formated  Default true
	 *
	 * @return  integer|string    formated like 3$ or 3
	 *
	 * @since   2.5.0
	 */
	public function getPrice($formated = true)
	{
		if ($formated)
		{
			$utilities = JT::utilities();

			return $utilities->getFormattedPrice($this->price);
		}

		return $this->price;
	}

	/**
	 * Method to return the available no of ticket count
	 *
	 * @return  integer|Boolean  for unlimited seats false and for limited the count will be returned.
	 *
	 * @since   2.5.0
	 */
	public function getAvailable()
	{
		if ($this->unlimited_seats)
		{
			return COM_JTICKETING_CONSTANT_TICKET_TYPE_UNLIMITED;
		}

		return $this->count;
	}

	/**
	 * Check whether the ticket types count are available for purchase or not
	 *
	 * @return  integer|Boolean  for unlimited seats false and for limited the count will be returned.
	 *
	 * @since   2.5.0
	 */
	public function isAvailable()
	{
		$availableToBuy = $this->getAvailable();

		if ($availableToBuy == COM_JTICKETING_CONSTANT_TICKET_TYPE_UNLIMITED)
		{
			return true;
		}

		return  $availableToBuy > 0;
	}

	/**
	 * Check whether the ticket types is expired or not
	 *
	 * @return  boolean  return true if not expired
	 *
	 * @since   2.5.0
	 */
	public function isExpired()
	{
		$currentDate = Factory::getDate()->toUnix();

		if (empty($this->ticket_enddate) || Factory::getDate($this->ticket_enddate, 'UTC')->toUnix() > $currentDate)
		{
				return false;
		}

		return true;
	}

	/**
	 * Check whether the ticket types count are available for purchase or not
	 *
	 * @param   integer  $eventId  The event integration id
	 *
	 * @return  integer|Boolean  for unlimited seats false and for limited the count will be returned.
	 *
	 * @since   2.5.0
	 */
	public function isValidToBuy($eventId = null)
	{
		// Check that user is having correct access.
		$userAccessLevel    = array_unique(Access::getAuthorisedViewLevels(Factory::getUser()->id));

		if ((!$this->id) || ($this->eventid != $eventId)
			|| ($this->state != 1) || !$this->isAvailable() || $this->isExpired() || !in_array($this->access, $userAccessLevel))
		{
			if (JT::config()->get('ticket_access') == 'exclude' && !in_array($this->access, $userAccessLevel))
			{
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Generate and return replacement tag's object.
	 *
	 * @return  object|boolean
	 *
	 * @since  2.7.0
	 */
	public function getReplacementTags()
	{
		if (!$this->id)
		{
			return false;
		}

		$replacement         = new stdClass;
		$replacement->ticket = $this;

		return $replacement;
	}

	/**
	 * Function to Decrement Ticket Count
	 *
	 * @return  Boolean
	 *
	 * @since   3.2.0
	 */
	public function decreaseAvilableSeats()
	{
		if ($this->unlimited_seats == 1)
		{
			return true;
		}

		if ($this->count == 0 )
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_NOT_ENOUGH_TICKETS'));

			return false;
		}

		// Decrement ticket count when user get enrollment approval
		$ticketCountData = array();
		$ticketCountData['id'] = $this->id;
		$ticketCountData['count'] = --$this->count;

		if (!$this->save($ticketCountData))
		{
			$event = JT::event()->loadByIntegration($this->eventid);
			$this->setError(Text::sprintf('COM_JTICKETING_ERROR_CONNOT_UPDATE_TICKET', $event->getTitle()));

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Increment the count of available ticket
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function increaseAvailableSeats()
	{
		if ($this->unlimited_seats == 1)
		{
			return true;
		}

		if ((int) $this->available === (int) $this->count)
		{
			$this->setError(Text::_('COM_JTICKETING_ERROR_CANNOT_INCREMENT_TICKET'));

			return false;
		}

		// Increment ticket count when user get enrollment approval
		$ticketCountData          = array();
		$ticketCountData['id']    = $this->id;
		$ticketCountData['count'] = ++$this->count;

		// Save incremented ticket count

		if (!$this->save($ticketCountData))
		{
			$event = JT::event()->loadByIntegration($this->eventid);
			$this->setError(Text::sprintf('COM_JTICKETING_ERROR_CONNOT_UPDATE_TICKET', $event->title));

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to get count of ticket types that are checkin
	 *
	 * @return  integer
	 *
	 * @since   3.2.0
	 */
	public function getSoldCount()
	{
		$attendees = JT::model('attendees', array('ignore_request' => true));
		$attendees->setState('filter_ticketId', $this->id);
		$attendees->setState('filter.status', 'A');
		$result = $attendees->getItems();

		return count($result);
	}

	/**
	 * Method to get count of ticket types that are checkin
	 *
	 * @return  integer
	 *
	 * @since   3.2.0
	 */
	public function getCheckinCount()
	{
		$attendees = JT::model('attendees', array('ignore_request' => true));
		$attendees->setState('filter_ticketId', $this->id);
		$attendees->setState('filter.attended_status', 1);
		$result = $attendees->getItems();

		return count($result);
	}
}
