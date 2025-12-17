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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
Use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * jticketing time helper
 *
 * @since       0.0.1
 * 
 * @deprecated  2.5.0 Will be removed without replacement in the next major release
 */
class JticketingTimeHelper
{
	/**
	 * Function used to get the formaated time
	 *
	 * @param   ARRAY  $post  Post data
	 *
	 * @deprecated  2.5.0
	 *
	 * @return  string  $formattedTime  Final formatted time
	 *
	 * @since  1.0.0
	 */
	public static function getFormattedDateTime($post)
	{
		// Get all non-jform data for event start time fields.
		$event_start_time_ampm = strtolower($post['event_start_time_ampm']);
		$event_start_time_hour = $post['event_start_time_hour'];
		$event_start_time_min  = $post['event_start_time_min'];
		$event_start_time      = $event_start_time_hour;

		// Convert hours into 24 hour format.
		if (($event_start_time_ampm == 'pm') && ($event_start_time_hour != '12'))
		{
			$event_start_time = $event_start_time_hour + 12;
		}
		elseif (($event_start_time_ampm == 'am') && ($event_start_time_hour == '12'))
		{
			$event_start_time = $event_start_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$event_start_time .= ":" . $event_start_time_min;
		$event_start_time .= ":" . '00';

		// Get all non-jform data for event start time fields.
		$event_end_time_ampm = strtolower($post['event_end_time_ampm']);
		$event_end_time_hour = $post['event_end_time_hour'];
		$event_end_time_min  = $post['event_end_time_min'];
		$event_end_time      = $event_end_time_hour;

		// Convert hours into 24 hour format.
		if (($event_end_time_ampm == 'pm') && ($event_end_time_hour != '12'))
		{
			$event_end_time = $event_end_time_hour + 12;
		}

		// Checking time 12am is it then convert to 24 hour time
		elseif (($event_end_time_ampm == 'am') && ($event_end_time_hour == '12'))
		{
			$event_end_time = $event_end_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$event_end_time .= ":" . $event_end_time_min;
		$event_end_time .= ":" . '00';

		// Get all non-jform data for event start time fields.
		$booking_start_time_ampm = strtolower($post['booking_start_time_ampm']);
		$booking_start_time_hour = $post['booking_start_time_hour'];
		$booking_start_time_min  = $post['booking_start_time_min'];
		$booking_start_time      = $booking_start_time_hour;

		// Convert hours into 24 hour format.
		if (($booking_start_time_ampm == 'pm') && ($booking_start_time_hour != '12'))
		{
			$booking_start_time = $booking_start_time_hour + 12;
		}
		elseif (($booking_start_time_ampm == 'am') && ($booking_start_time_hour == '12'))
		{
			$booking_start_time = $booking_start_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$booking_start_time .= ":" . $booking_start_time_min;
		$booking_start_time .= ":" . '00';

		// Get all non-jform data for event start time fields.
		$booking_end_time_ampm = strtolower($post['booking_end_time_ampm']);
		$booking_end_time_hour = $post['booking_end_time_hour'];
		$booking_end_time_min  = $post['booking_end_time_min'];
		$booking_end_time      = $booking_end_time_hour;

		// Convert hours into 24 hour format.
		if (($booking_end_time_ampm == 'pm') && ($booking_end_time_hour != '12'))
		{
			$booking_end_time = $booking_end_time_hour + 12;
		}

		// Checking time 12am is it then convert to 24 hour time
		elseif (($booking_end_time_ampm == 'am') && ($booking_end_time_hour == '12'))
		{
			$booking_end_time = $booking_end_time_hour - 12;
		}

		// Get minutes and attach seconds.
		$booking_end_time .= ":" . $booking_end_time_min;
		$booking_end_time .= ":" . '00';

		$formattedTime = array();
		$formattedTime['event_start_time'] = $event_start_time;
		$formattedTime['event_end_time']   = $event_end_time;
		$formattedTime['booking_start_time'] = $booking_start_time;
		$formattedTime['booking_end_time']   = $booking_end_time;

		return $formattedTime;
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $date  date
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function timeLapse($date)
	{
		$now      = new Date;
		$dateDiff = self::timeDifference($date->toUnix(), $now->toUnix());

		if ($dateDiff['days'] > 0)
		{
			$lapse = Text::sprintf(
			(CStringHelper::isPlural($dateDiff['days']))
				? 'COM_COMMUNITY_LAPSED_DAY_MANY' : 'COM_COMMUNITY_LAPSED_DAY', $dateDiff['days']
				);
		}
		elseif ($dateDiff['hours'] > 0)
		{
			$lapse = Text::sprintf(
			(CStringHelper::isPlural($dateDiff['hours']))
				? 'COM_COMMUNITY_LAPSED_HOUR_MANY' : 'COM_COMMUNITY_LAPSED_HOUR', $dateDiff['hours']
				);
		}
		elseif ($dateDiff['minutes'] > 0)
		{
			$lapse = Text::sprintf(
			(CStringHelper::isPlural($dateDiff['minutes']))
				? 'COM_COMMUNITY_LAPSED_MINUTE_MANY' : 'COM_COMMUNITY_LAPSED_MINUTE', $dateDiff['minutes']
				);
		}
		else
		{
			if ($dateDiff['seconds'] == 0)
			{
				$lapse = Text::_("COM_COMMUNITY_ACTIVITIES_MOMENT_AGO");
			}
			else
			{
				$lapse = Text::sprintf(
				(CStringHelper::isPlural($dateDiff['seconds']))
					? 'COM_COMMUNITY_LAPSED_SECOND_MANY' : 'COM_COMMUNITY_LAPSED_SECOND', $dateDiff['seconds']
					);
			}
		}

		return $lapse;
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $start  start
	 * @param   string  $end    end
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function timeDifference($start, $end)
	{

		if (is_string($start) && ($start != intval($start)))
		{
			$start = new Date($start);
			$start = $start->toUnix();
		}

		if (is_string($end) && ($end != intval($end)))
		{
			$end = new Date($end);
			$end = $end->toUnix();
		}

		$uts          = array();
		$uts['start'] = $start;
		$uts['end']   = $end;

		if ($uts['start'] !== -1 && $uts['end'] !== -1)
		{
			if ($uts['end'] >= $uts['start'])
			{
				$diff = $uts['end'] - $uts['start'];

				if ($days = intval((floor($diff / 86400))))
				{
					$diff = $diff % 86400;
				}

				if ($hours = intval((floor($diff / 3600))))
				{
					$diff = $diff % 3600;
				}

				if ($minutes = intval((floor($diff / 60))))
				{
					$diff = $diff % 60;
				}

				$diff = intval($diff);

				return (array(
					'days' => $days,
					'hours' => $hours,
					'minutes' => $minutes,
					'seconds' => $diff
				));
			}
			else
			{
				trigger_error(Text::_("COM_COMMUNITY_TIME_IS_EARLIER_THAN_START_WARNING"), E_USER_WARNING);
			}
		}
		else
		{
			trigger_error(Text::_("COM_COMMUNITY_INVALID_DATETIME"), E_USER_WARNING);
		}

		return (false);
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $start  start
	 * @param   string  $end    end
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function timeIntervalDifference($start, $end)
	{
		$start = new Date($start);
		$start = $start->toUnix();

		$end = new Date($end);
		$end = $end->toUnix();

		if ($start !== -1 && $end !== -1)
		{
			return ($start - $end);
		}
		else
		{
			trigger_error(Text::_("COM_COMMUNITY_INVALID_DATETIME"), E_USER_WARNING);
		}

		return (false);
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $jdate  str
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function formatTime($jdate)
	{

		return StringHelper::strtolower($jdate->toFormat('%I:%M %p'));
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $str  str
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function getInputDate($str = '')
	{
		$mainframe =& Factory::getApplication();
		$config = CFactory::getConfig();

		$timeZoneOffset = $mainframe->get('offset');
		$dstOffset      = $config->get('daylightsavingoffset');

		$date = new CDate($str);
		$my =& Factory::getUser();
		$cMy = CFactory::getUser();

		if ($my->id)
		{
			if (!empty($my->params))
			{
				$timeZoneOffset = $my->getParam('timezone', $timeZoneOffset);

				$myParams  = $cMy->getParams();
				$dstOffset = $myParams->get('daylightsavingoffset', $dstOffset);
			}
		}

		$timeZoneOffset = (-1) * $timeZoneOffset;
		$dstOffset      = (-1) * $dstOffset;
		$date->setOffset($timeZoneOffset + $dstOffset);

		return $date;
	}

	/**
	 * Retrieve date.
	 *
	 * @param   string  $str  str
	 * @param   string  $off  offset
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   boolean  $off  offset
	 *
	 * @since  1.5
	 **/
	static public function getDate($str = '', $off = 0)
	{
		require_once JPATH_ROOT . '/components/com_community/libraries/core.php';

		$mainframe =& Factory::getApplication();
		$config = CFactory::getConfig();
		$extraOffset = $config->get('daylightsavingoffset');

		// Convert to utc time first.
		$utc_date = new CDate($str);
		$date     = new CDate($utc_date->toUnix() + $off * 3600);

		$my =& Factory::getUser();
		$cMy = CFactory::getUser();

		// J1.6 returns timezone as string, not integer offset.
		if (method_exists('Date', 'getOffsetFromGMT'))
		{
			$systemOffset = new CDate('now', $mainframe->get('offset'));
			$systemOffset = $systemOffset->getOffsetFromGMT(true);
		}
		else
		{
			$systemOffset = $mainframe->get('offset');
		}

		if (!$my->id)
		{
			$date->setOffset($systemOffset + $extraOffset);
		}
		else
		{
			if (!empty($my->params))
			{
				$pos = StringHelper::strpos($my->params, 'timezone');

				$offset = $systemOffset + $extraOffset;

				if ($pos === false)
				{
					$offset = $systemOffset + $extraOffset;
				}
				else
				{
					$offset = $my->getParam('timezone', -100);

					$myParams = $cMy->getParams();
					$myDTS    = $myParams->get('daylightsavingoffset');
					$cOffset  = (!empty($myDTS)) ? $myDTS : $config->get('daylightsavingoffset');

					if ($offset == -100)
					{
						$offset = $systemOffset + $extraOffset;
					}
					else
					{
						$offset = $offset + $cOffset;
					}
				}

				$date->setOffset($offset);
			}
			else
			{
				$date->setOffset($systemOffset + $extraOffset);
			}
		}

		return $date;
	}

	/**
	 * Return locale date
	 *
	 * @param   string  $date  current date
	 *
	 * @deprecated  2.5.0
	 *
	 * @return  date object
	 *
	 * @since   2.4.2
	 **/
	public function getLocaleDate($date = 'now')
	{
		$mainframe =& Factory::getApplication();
		$systemOffset = $mainframe->get('offset');

		$now = new Date($date, $systemOffset);
		$now->setOffset($systemOffset);

		return $now;
	}

	/**
	 * Retrieve timezone.
	 *
	 * @param   int  $offset  The current offset
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   string  The current time zone for the given offset.
	 *
	 * @since  1.5
	 **/
	static public function getTimezone($offset = 0)
	{
		$timezone = self::getTimezoneList();

		if (isset($timezone[$offset]))
		{
			return $timezone[$offset];
		}
	}

	/**
	 * Retrieve the list of timezones.
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   array  The list of timezones available.
	 *
	 * @since  1.5
	 **/
	static public function getTimezoneList()
	{
		$timezone = array();

		$timezone['-11']   = Text::_('(UTC -11:00) Midway Island, Samoa');
		$timezone['-10']   = Text::_('(UTC -10:00) Hawaii');
		$timezone['-9.5']  = Text::_('(UTC -09:30) Taiohae, Marquesas Islands');
		$timezone['-9']    = Text::_('(UTC -09:00) Alaska');
		$timezone['-8']    = Text::_('(UTC -08:00) Pacific Time (US &amp; Canada)');
		$timezone['-7']    = Text::_('(UTC -07:00) Mountain Time (US &amp; Canada)');
		$timezone['-6']    = Text::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City');
		$timezone['-5']    = Text::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima');
		$timezone['-4']    = Text::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz');
		$timezone['-4.5']  = Text::_('(UTC -04:30) Venezuela');
		$timezone['-3.5']  = Text::_('(UTC -03:30) St. John\'s, Newfoundland, Labrador');
		$timezone['-3']    = Text::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown');
		$timezone['-2']    = Text::_('(UTC -02:00) Mid-Atlantic');
		$timezone['-1']    = Text::_('(UTC -01:00) Azores, Cape Verde Islands');
		$timezone['0']     = Text::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca');
		$timezone['1']     = Text::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris');
		$timezone['2']     = Text::_('(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa');
		$timezone['3']     = Text::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg');
		$timezone['3.5']   = Text::_('(UTC +03:30) Tehran');
		$timezone['4']     = Text::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi');
		$timezone['4.5']   = Text::_('(UTC +04:30) Kabul');
		$timezone['5']     = Text::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent');
		$timezone['5.5']   = Text::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi, Colombo');
		$timezone['5.75']  = Text::_('(UTC +05:45) Kathmandu');
		$timezone['6']     = Text::_('(UTC +06:00) Almaty, Dhaka');
		$timezone['6.30']  = Text::_('(UTC +06:30) Yagoon');
		$timezone['7']     = Text::_('(UTC +07:00) Bangkok, Hanoi, Jakarta');
		$timezone['8']     = Text::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong');
		$timezone['8.75']  = Text::_('(UTC +08:00) Ulaanbaatar, Western Australia');
		$timezone['9']     = Text::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk');
		$timezone['9.5']   = Text::_('(UTC +09:30) Adelaide, Darwin, Yakutsk');
		$timezone['10']    = Text::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok');
		$timezone['10.5']  = Text::_('(UTC +10:30) Lord Howe Island (Australia)');
		$timezone['11']    = Text::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia');
		$timezone['11.30'] = Text::_('(UTC +11:30) Norfolk Island');
		$timezone['12']    = Text::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka');
		$timezone['12.75'] = Text::_('(UTC +12:45) Chatham Island');
		$timezone['13']    = Text::_('(UTC +13:00) Tonga');
		$timezone['14']    = Text::_('(UTC +14:00) Kiribati');

		return $timezone;
	}

	/**
	 * Get formatted date time.
	 *
	 * @param   integer  $time    Time value
	 * @param   string   $format  format
	 * @param   boolean  $offset  offset
	 *
	 * @deprecated  2.5.0
	 *
	 * @return  string   formatted date and time
	 *
	 * @since  1.5
	 */
	public function getFormattedTime($time, $format, $offset = 0)
	{
		$time = strtotime($time);

		// Manually modify the month and day strings in the format.
		if (strpos($format, '%a') !== false)
		{
			$format = str_replace('%a', self::dayToString(date('w', $time), true), $format);
		}

		if (strpos($format, '%A') !== false)
		{
			$format = str_replace('%A', self::dayToString(date('w', $time)), $format);
		}

		if (strpos($format, '%b') !== false)
		{
			$format = str_replace('%b', self::monthToString(date('n', $time), true), $format);
		}

		if (strpos($format, '%B') !== false)
		{
			$format = str_replace('%B', self::monthToString(date('n', $time)), $format);
		}

		return strftime($format, $time);
	}

	/**
	 * Translates day of week number to a string.
	 *
	 * @param   integer  $day   The numeric day of the week.
	 * @param   boolean  $abbr  Return the abreviated day string?
	 *
	 * @deprecated  2.5.0
	 *
	 * @return   string  The day of the week.
	 *
	 * @since  1.5
	 */
	protected function dayToString($day, $abbr = false)
	{
		switch ($day)
		{
			case 0:

				return $abbr ? Text::_('SUN') : Text::_('SUNDAY');
			case 1:

				return $abbr ? Text::_('MON') : Text::_('MONDAY');
			case 2:

				return $abbr ? Text::_('TUE') : Text::_('TUESDAY');
			case 3:

				return $abbr ? Text::_('WED') : Text::_('WEDNESDAY');
			case 4:

				return $abbr ? Text::_('THU') : Text::_('THURSDAY');
			case 5:

				return $abbr ? Text::_('FRI') : Text::_('FRIDAY');
			case 6:

				return $abbr ? Text::_('SAT') : Text::_('SATURDAY');
		}
	}

	/**
	 * Translates month number to a string.
	 *
	 * @param   integer  $month  The numeric month of the year.
	 * @param   boolean  $abbr   Return the abreviated month string?
	 *
	 * @deprecated  2.5.0
	 *
	 * @return  string   The month of the year.
	 *
	 * @since  1.5
	 */
	protected function monthToString($month, $abbr = false)
	{
		switch ($month)
		{
			case 1:

				return $abbr ? Text::_('JANUARY_SHORT') : Text::_('JANUARY');
			case 2:

				return $abbr ? Text::_('FEBRUARY_SHORT') : Text::_('FEBRUARY');
			case 3:

				return $abbr ? Text::_('MARCH_SHORT') : Text::_('MARCH');
			case 4:

				return $abbr ? Text::_('APRIL_SHORT') : Text::_('APRIL');
			case 5:

				return $abbr ? Text::_('MAY_SHORT') : Text::_('MAY');
			case 6:

				return $abbr ? Text::_('JUNE_SHORT') : Text::_('JUNE');
			case 7:

				return $abbr ? Text::_('JULY_SHORT') : Text::_('JULY');
			case 8:

				return $abbr ? Text::_('AUGUST_SHORT') : Text::_('AUGUST');
			case 9:

				return $abbr ? Text::_('SEPTEMBER_SHORT') : Text::_('SEPTEMBER');
			case 10:

				return $abbr ? Text::_('OCTOBER_SHORT') : Text::_('OCTOBER');
			case 11:

				return $abbr ? Text::_('NOVEMBER_SHORT') : Text::_('NOVEMBER');
			case 12:

				return $abbr ? Text::_('DECEMBER_SHORT') : Text::_('DECEMBER');
		}
	}

	/**
	 * Get the exact time from the UTC00:00 time & the offset/timezone given
	 *
	 * @param   array    $datetime  datetime is UTC00:00
	 * @param   boolean  $offset    offset/timezone
	 *
	 * @deprecated  2.5.0
	 *
	 * @return  string  The month of the year
	 *
	 * @since  1.5
	 */
	public function getFormattedUTC($datetime, $offset)
	{
		$date      = new DateTime($datetime);
		$splitTime = explode(".", $offset);
		$begin     = new DateTime($datetime);

		// Modify the hour
		$begin->modify($splitTime[0] . ' hour');

		// Modify the minutes
		if (isset($splitTime[1]))
		{
			// The offset is actually a in 0.x hours. Convert to minute // = percentage x 60 minues x 0.1
			$splitTime[1] = $splitTime[1] * 6;
			$isMinus      = ($splitTime[0][0] == '-') ? '-' : '+';
			$begin->modify($isMinus . $splitTime[1] . ' minute');
		}

		return $begin->format('Y-m-d H:i:s');
	}

	/**
	 * Get date format.
	 *
	 * @param   date  $datetime  The current offset
	 *
	 * @deprecated  2.5.0  Use utilities class getFormattedDate
	 *
	 * @return  string  formatted datetime
	 *
	 * @since  1.5
	 **/
	public function getFormattedDate($datetime)
	{
		$comParams = ComponentHelper::getParams('com_jticketing');
		$dateFormat = $comParams->get('date_format_show');

		if ($dateFormat == "custom")
		{
			$customFormt = $comParams->get('custom_format');
			$fromattedDate = HTMLHelper::date($datetime, $customFormt, true);
		}
		else
		{
			$fromattedDate = HTMLHelper::date($datetime, $dateFormat, true);
		}

		return $fromattedDate;
	}
}
