<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\String\StringHelper;

defined('_JEXEC') or die();

include_once JPATH_SITE . '/components/com_jevents/jevents.defines.php';

/**
 * JEvent event class.
 *
 * @since  2.5.0
 */
class JTicketingEventJevents extends JTicketingEvent
{
	/**
	 * holds the property of the jevent event object
	 *
	 * @var    jIcalEventRepeat
	 * @since  2.5.0
	 */
	public $event;

	/**
	 * holds the already loaded instances of the event
	 *
	 * @var    array
	 * @since  2.5.0
	 */
	protected static $loadedJEvent = array();

	/**
	 * Constructor activating the default information of the event
	 *
	 * @param   int  $eventId  The unique event key to load.
	 *
	 * @since   2.5.0
	 */
	public function __construct($eventId)
	{
		parent::__construct($eventId, 'com_jevents');

		if (empty(self::$loadedJEvent[$eventId]))
		{
			$dataModel = new JEventsDataModel("JEventsAdminDBModel");
			$queryModel = new JEventsDBModel($dataModel);
			self::$loadedJEvent[$eventId] = $queryModel->listEventsById($eventId, 1);
		}

		$this->event = self::$loadedJEvent[$eventId];
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
	 * This method will return the event Id
	 *
	 * @return  string  Event Id
	 *
	 * @since   2.5.0
	 */
	public function getId()
	{
		return (int) $this->event->id();
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
		return $this->event->getUnixStartTime();
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
		return $this->event->getUnixEndTime();
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
		return $this->event->created_by();
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
		return '/media/com_jticketing/images/default-event-image.png';
	}

	/**
	 * This method will return the event location
	 *
	 * @return  integer
	 *
	 * @since   2.5.0
	 */
	public function getVenueDetails()
	{
		$location = '';

		if (!is_numeric($this->event->location()))
		{
			$location = self::wraplines(self::replacetags($this->event->location()));
			$position = strpos($location, '\n');
			if ($position !== false) {
				$location = substr($location, 0, $position);
			}
		}
		else if (isset($this->event->_loc_title))
		{
			$location = self::wraplines(self::replacetags($this->event->_loc_title));
		}
		else
		{
			if (is_numeric($this->event->location()) && file_exists(JPATH_ADMINISTRATOR.'/components/com_jevlocations/tables/location.php'))
			{
				Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jevlocations/tables');

				$db = Factory::getDbo();
				$locationTable = Table::getInstance('Location', 'Table', array('dbo', $db));
				$locationTable->load(array('loc_id' => $this->event->location()));
				$location   = $locationTable ? $locationTable->title : '';
			}
			else 
			{
			    $location = self::wraplines(self::replacetags($this->event->location()));
			}
		}

		return $location;
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
		$link = $this->event->viewDetailLink($this->event->yup(), $this->event->mup(), $this->event->dup(), $sef);

		$link = new Uri($link);
		$link->delVar('tmpl');

		if (!$sef)
		{
			return $link->toString();
		}

		return Route::_($link->toString(), false);
	}

	/**
	 * Determine if the event is repetitive
	 *
	 * @return  boolean return true if the event is repetitive
	 *
	 * @since   2.5.0
	 */
	public function isrepeat()
	{
		return $this->event->isrepeat();
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
	 * Get noendtime of the event
	 *
	 * @return Integer
	 *
	 * @since   4.1.3
	 */
	public function getNoEndTime()
	{
		$allInformation = $this->event->getOriginalFirstRepeat();
		return $allInformation->noendtime();
	}

	/**
	 * Special methods ONLY user for iCal location
	 *
	 * @return  string
	 *
	 * @since   4.1.3
	 */
	private static function wraplines($input, $line_max = 76, $quotedprintable = false)
	{

		$eol = "\r\n";

		$input = str_replace($eol, "", $input);

		// new version
		$output = '';
		while (StringHelper::strlen($input) >= $line_max)
		{
			$output .= StringHelper::substr($input, 0, $line_max - 1);
			$input  = StringHelper::substr($input, $line_max - 1);
			if (StringHelper::strlen($input) > 0)
			{
				$output .= $eol . " ";
			}
		}
		if (StringHelper::strlen($input) > 0)
		{
			$output .= $input;
		}

		return $output;
	}

	/**
	 * Special methods ONLY user for iCal location
	 *
	 * @return  string
	 *
	 * @since   4.1.3
	 */
	private static function replacetags($description)
	{

		$description = str_replace('<p>', '', $description);
		$description = str_replace('<P>', '', $description);
		$description = str_replace('</p>', '\n', $description);
		$description = str_replace('</P>', '\n', $description);
		$description = str_replace('<p/>', '\n\n', $description);
		$description = str_replace('<P/>', '\n\n', $description);
		$description = str_replace('<br />', '\n', $description);
		$description = str_replace('<br/>', '\n', $description);
		$description = str_replace('<br>', '\n', $description);
		$description = str_replace('<BR />', '\n', $description);
		$description = str_replace('<BR/>', '\n', $description);
		$description = str_replace('<BR>', '\n', $description);
		$description = str_replace('<li>', '\n - ', $description);
		$description = str_replace('<LI>', '\n - ', $description);

		try
		{
			$dom = new DOMDocument();
			// see http://php.net/manual/en/domdocument.savehtml.php cathexis dot de ¶
			@$dom->loadHTML('<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head><body>' . $description . '</body>');

			$links = $dom->getElementsByTagName('a');
			foreach ($links as $link)
			{
				$fragment = $dom->createDocumentFragment();
				$href = $link->getAttribute('href');
				$text = $link->textContent;
				if ($text == $href || empty($href))
				{
					$fragment->appendXML($link->textContent);
				}
				else
				{
					$fragment->appendXML($link->textContent . " (" . $href . ")");
				}

				$link->parentNode->replaceChild($fragment, $link);
			}
			//$description = $dom->saveHTML($dom->getElementsByTagName('body')[0]);
			$body = $dom->getElementsByTagName('body')[0];
			$newdescription= '';
			$children = $body->childNodes;
			foreach ($children as $child) {
				$newdescription .= $child->ownerDocument->saveHTML( $child );
			}
			if (!empty($newdescription))
			{
				$description = $newdescription;
			}

		}
		catch (Exception $exception)
		{
			$x = 1;
		}
		$description = strip_tags($description, '<a>');
		//$description 	= strtr( $description,	array_flip(get_html_translation_table( HTML_ENTITIES ) ) );
		//$description 	= preg_replace( "/&#([0-9]+);/me","chr('\\1')", $description );
		return $description;

	}

	/**
	 * Determine if the event booking is started
	 *
	 * @return  boolean return true if the booking is already started
	 *
	 * @since   2.5.0
	 */
	public function isBookingStarted()
	{
		$currentDate = Factory::getDate()->toUnix();
		$user = Factory::getUser();

		// // For integrated events we need to check the booking end time based on the ticket end date
		$nullDate = Factory::getDbo()->getNullDate();
		$ticketTypes = $this->getTicketTypes();

		// If tickets are not available for booking return false
		foreach ($ticketTypes as $ticketType)
		{
			// If ticket is available for booking return false
			if ($ticketType->ticket_startdate &&
				(Factory::getDate($ticketType->ticket_startdate, 'UTC')->toUnix() > $currentDate)
					|| (StringHelper::strcmp($nullDate, $ticketType->ticket_startdate) == 0))
			{
				return false;
			}
		}

		return true;
	}
}
