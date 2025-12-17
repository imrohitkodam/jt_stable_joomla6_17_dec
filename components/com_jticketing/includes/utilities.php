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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * JTicketing utility class for common methods
 *
 * @since  2.5.0
 */
class JTicketingUtilities
{
	/**
	 * Get formated date
	 *
	 * @param   string  $datetime  The current offset
	 *
	 * @return  string  formatted datetime
	 *
	 * @since  2.5.0
	 **/
	public function getFormatedDate($datetime)
	{
		$comParams = JT::config();
		$dateFormat = $comParams->get('date_format_show');

		if ($dateFormat == "custom")
		{
			$dateFormat = $comParams->get('custom_format');
		}

		return HTMLHelper::_('date', $datetime, $dateFormat, true);
	}

	/**
	 * Format the price  based on the JT config with the help of the money library
	 *
	 * @param   float  $price  price
	 *
	 * @return  string    formated like 3$ or $3
	 *
	 * @since  2.5.0
	 */
	public function getFormattedPrice($price)
	{
		$params = JT::config();

		$currencyCode = $params->get('currency');
		$currencyCodeOrSymbol = $params->get('currency_code_or_symbol', 'code');
		$currencyDisplayFormat = $params->get('currency_display_format', '{AMOUNT} {CURRENCY_SYMBOL} ');

		$config = new stdClass();
		$config->CurrencyDisplayFormat = $currencyDisplayFormat;
		$config->CurrencyCodeOrSymbol = $currencyCodeOrSymbol;
		$tjCurrency = new TjMoney($currencyCode);

		return $tjCurrency->displayFormattedValue($price, $config);
	}

	/**
	 * Get rounded price
	 *
	 * @param   float  $price  price
	 *
	 * @return  String   rounded price
	 *
	 * @since  3.2.0
	 */
	public function getRoundedPrice($price)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$currencyCode = $params->get('currency', '', 'STRING');

		$tjCurrency = new TjMoney($currencyCode);

		// Get rounded output to display directly
		$roundedAmount = $tjCurrency->getRoundedValue($price);

		return $roundedAmount;
	}

	/**
	 * Generate random no
	 *
	 * @param   integer  $length  length for field
	 *
	 * @return  String
	 *
	 * @since  2.5.0
	 */
	public function generateRandomString($length = 32)
	{
		$string = '';

		while (($len = strlen($string)) < $length)
		{
			$size = $length - $len;

			$bytes = random_bytes($size);

			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}

		// Return the string
		return $string;
	}

	/**
	 * Get item id of url
	 *
	 * @param   string  $link          link
	 * @param   string  $skipIfNoMenu  skipIfNoMenu
	 * @param   int     $catid         category if aapplicable to provided link
	 *
	 * @return  int  Itemid of the given link
	 *
	 * @since   2.5.0
	 */
	public function getItemId($link, $skipIfNoMenu = 0, $catid = 0)
	{
		$itemid    		= 0;
		$parsedLinked 	= array();
		$config			= JT::config();
		$integration	= $config->get('integration', COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE);

		parse_str($link, $parsedLinked);

		$app = Factory::getApplication();
		$menu = $app->getMenu();

		$jinput = Factory::getApplication();

		if ($jinput->isClient('site'))
		{
			$items = $menu->getItems('link', $link);

			if (isset($items[0]))
			{
				$itemid = $items[0]->id;
			}
		}

		if (isset($parsedLinked['view'])
			&& ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_NATIVE)
			&& (($parsedLinked['view'] == 'order')
			|| ($parsedLinked['view'] == 'orders') || ($parsedLinked['view'] == 'events')))
		{
			if (isset($parsedLinked['layout']) && !($parsedLinked['layout'] == 'my' && isset($itemid)))
			{
				// Get the itemid of the menu which is pointed to events URL
				$menuLink = 'index.php?option=com_jticketing&view=events&layout=default';

				$newMenuItem = $menu->getItems('link', $menuLink , true);

				$itemid = (!empty($newMenuItem)) ? $newMenuItem->id : 0;

				if (!$itemid)
				{
					$newMenuItem = $menu->getItems('link', $menuLink . '&catid=0', true);

					$itemid = (!empty($newMenuItem)) ? $newMenuItem->id : 0;
				}
			}

			if (!$itemid)
			{
				if (($catid == 0) && ($parsedLinked['view'] == 'order' || $parsedLinked['view'] == 'orders') && isset($parsedLinked['orderId']))
				{
					$order 	= JT::order($parsedLinked['orderId']);
					$event	= JT::event()->loadByIntegration($order->event_details_id);
					$catid	= $event->getCategory();
				}

				$menuLink = 'index.php?option=com_jticketing&view=events&layout=default';
				$menuLink .= '&catid=' . $catid;

				$layout = !empty($parsedLinked['layout']) ? $parsedLinked['layout'] : '';

				if ($parsedLinked['view'] == 'orders' || $layout === 'order')
				{
					// Get the itemid of the menu which is pointed to orders URL
					$menuLink = 'index.php?option=com_jticketing&view=orders&layout=default';
				}

				$menuItem = $menu->getItems('link', $menuLink, true);

				$itemid = (!empty($menuItem)) ? $menuItem->id : 0;
			}
		}
		
		else if (isset($parsedLinked['view'])
			&& ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_JOMSOCIAL)
			&& (($parsedLinked['view'] == 'order')
			|| ($parsedLinked['view'] == 'orders')))
		{
			// Get the itemid of the menu which is pointed to events URL
			$menuLink = 'index.php?option=com_community&view=events';

			$newMenuItem = $menu->getItems('link', $menuLink, true);

			$itemid = (!empty($newMenuItem)) ? $newMenuItem->id : 0;

			if (!$itemid) 
			{
				$link = 'index.php?option=com_jticketing&view=events';

				$db		= Factory::getDbo();
				$query 	= $db->getQuery(true);
				$query->select($db->qn('id'));
				$query->from($db->qn('#__menu'));
				$query->where($db->qn('link') . ' LIKE ' . $db->Quote('%' . $link . '%'));
				$query->where($db->qn('published') . '=' . $db->Quote(1));
				$query->setLimit(1);
				$db->setQuery($query);
				$itemid = $db->loadResult();
			}
		}
		else if (isset($parsedLinked['view'])
			&& ($integration == COM_JTICKETING_CONSTANT_INTEGRATION_EASYSOCIAL)
			&& (($parsedLinked['view'] == 'order')
			|| ($parsedLinked['view'] == 'orders')))
		{
			// Get the itemid of the menu which is pointed to events URL
			$menuLink = 'index.php?option=com_easysocial&view=events';

			$newMenuItem = $menu->getItems('link', $menuLink, true);

			$itemid = (!empty($newMenuItem)) ? $newMenuItem->id : 0;

			if (!$itemid) 
			{
				$menuLink = 'index.php?option=com_easysocial&view=events&filter=all';

				$newMenuItem = $menu->getItems('link', $menuLink, true);

				$itemid = (!empty($newMenuItem)) ? $newMenuItem->id : 0;
			}

			if (!$itemid) 
			{
				$link = 'index.php?option=com_jticketing&view=events';

				$db		= Factory::getDbo();
				$query 	= $db->getQuery(true);
				$query->select($db->qn('id'));
				$query->from($db->qn('#__menu'));
				$query->where($db->qn('link') . ' LIKE ' . $db->Quote('%' . $link . '%'));
				$query->where($db->qn('published') . '=' . $db->Quote(1));
				$query->setLimit(1);
				$db->setQuery($query);
				$itemid = $db->loadResult();
			}
		}
		else if (isset($itemid) && isset($parsedLinked['view']))
		{
			if ($parsedLinked['view'] == 'event')
			{
				$link = 'index.php?option=com_jticketing&view=events&layout=default';

				if ($catid == 0)
				{
					$id = !empty($parsedLinked['id']) ? $parsedLinked['id'] : 0;
					$event	= JT::event($id);
					$link .= '&catid=' . $event->getCategory();
				}
			}

			$db		= Factory::getDbo();
			$query 	= $db->getQuery(true);
			$query->select($db->qn('id'));
			$query->from($db->qn('#__menu'));
			$query->where($db->qn('link') . ' LIKE ' . $db->Quote('%' . $link . '%'));
			$query->where($db->qn('published') . '=' . $db->Quote(1));
			$query->where($db->qn('client_id') . '=' . $db->Quote(0));
			$query->setLimit(1);
			$db->setQuery($query);
			$itemid = $db->loadResult();
		}

		if (!$itemid)
		{
			if ($skipIfNoMenu)
			{
				$itemid = 0;
			}
			else
			{
				$input = $jinput->input;
				$itemid = $input->get('Itemid', '0', 'INT');
			}
		}

		return $itemid;
	}

	/**
	 * Check if given string in JSON
	 *
	 * @param   string  $string  string
	 *
	 * @return boolean
	 *
	 * @since  2.7.0
	 */
	public function isJSON($string)
	{
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

	/**
	 * This method returns the user of given group Id
	 *
	 * @return  array
	 *
	 * @since  3.1.0
	 */
	public function getAdminUsers()
	{
		$users = array();

		// Get all admin users
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('sendEmail') . '= 1');
		$db->setQuery($query);
		$adminUsers = $db->loadObjectList();

		foreach ($adminUsers as $adminUser)
		{
			$users[] = Factory::getUser($adminUser->id);
		}

		return $users;
	}

	/**
	 * Get actual payment status
	 *
	 * @param   string  $pstatus  payment status like P/S
	 *
	 * @return  array  $payment_statuses  payment status array
	 *
	 * @since   3.1.0
	 */
	public function getPaymentStatus($pstatus)
	{
		$payment_statuses = array(
			'P' => Text::_('JT_PSTATUS_PENDING'),
			'C' => Text::_('JT_PSTATUS_COMPLETED'),
			'D' => Text::_('JT_PSTATUS_DECLINED'),
			'DP' => Text::_('JT_PSTATUS_DEPOSIT_PAID'),
			'E' => Text::_('JT_PSTATUS_FAILED'),
			'UR' => Text::_('JT_PSTATUS_UNDERREVIW'),
			'RF' => Text::_('JT_PSTATUS_REFUNDED'),
			'CRV' => Text::_('JT_PSTATUS_CANCEL_REVERSED'),
			'RV' => Text::_('JT_PSTATUS_REVERSED')
		);

		return $payment_statuses[$pstatus];
	}

	/**
	 * Get layout html
	 *
	 * @param   string  $viewName       name of view
	 * @param   string  $layout         layout of view
	 * @param   string  $searchTmpPath  site/admin template
	 * @param   string  $useViewpath    site/admin view
	 *
	 * @return  [type]                  description
	 *
	 * @since  3.2.0
	 */
	public function getViewPath($viewName, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;

		$layoutName = (!empty($layout)) ? $layout . '.php' : "default.php";

		$defTemplate = $this->getSiteDefaultTemplate();

		$override = $searchTmpPath . '/templates/' . $defTemplate . '/html/com_jticketing/' . $viewName . '/' . $layoutName;

		$view = (File::exists($override)) ? $override : $useViewpath . '/components/com_jticketing/views/' . $viewName . '/tmpl/' . $layoutName;

		return $view;
	}

	/**
	 * Get sites/administrator default template
	 *
	 * @param   mixed  $client  0 for site and 1 for admin template
	 *
	 * @return  String
	 *
	 * @since   3.2.0
	 */
	public function getSiteDefaultTemplate($client = 0)
	{
		try
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('template')
				->from($db->quoteName('#__template_styles'))
				->where($db->quoteName('client_id') . '=' . $client)
				->where($db->quoteName('home') . '=1');
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return '';
		}
	}

	/**
	 * Method to sort array
	 *
	 * @param   array  $array   to sort
	 * @param   int    $column  column name
	 * @param   int    $order   asc or desc
	 *
	 * @return  array
	 *
	 * @since  3.2.0
	 */
	public function multiDSort($array, $column, $order)
	{
		foreach ($array as $key => $row)
		{
			$orderby[$key] = $row->$column;
		}

		$order = ($order == 'asc') ? SORT_ASC : SORT_DESC;

		array_multisort($orderby, $order, $array);

		return $array;
	}

	/**
	 * Method to delete unwanted files
	 *
	 * @param   array  $data  Array containing filenames
	 *
	 * @return  void
	 *
	 * @since  3.2.0
	 */
	public function deleteUnwantedfiles($data)
	{
		$tmp_path = JPATH_SITE . '/components/com_jticketing/helpers/dompdf/tmp/';

		if (!empty($data['pdf']))
		{
			if (is_array($data['pdf']))
			{
				foreach ($data['pdf'] as $pdf)
				{
					if (file_exists($pdf))
					{
						unlink($pdf);
					}
				}
			}
			else
			{
				if (file_exists($data['pdf']))
				{
					unlink($data['pdf']);
				}
			}
		}

		if (!empty($data['qr_code']))
		{
			if (is_array($data['qr_code']))
			{
				foreach ($data['qr_code'] as $pdf)
				{
					if (file_exists($tmp_path . $pdf))
					{
						unlink($tmp_path . $pdf);
					}
				}

				foreach ($data['ics_file'] as $ics_file)
				{
					if (file_exists($tmp_path . $ics_file))
					{
						unlink($tmp_path . $ics_file);
					}
				}
			}
			else
			{
				if (file_exists($tmp_path . $data['qr_code']))
				{
					unlink($tmp_path . $data['qr_code']);
				}
			}
		}
	}

	/**
	 * Load Assets which are require for quick2cart.
	 *
	 * @return  null.
	 *
	 * @since   3.2.0
	 */
	public static function loadjticketingAssetFiles()
	{
		$config = JT::config();

		// Define wrapper class
		if (!defined('JTICKETING_WRAPPER_CLASS'))
		{
			$wrapperClass   = "jticketing-wrapper";
			$currentBSViews = $config->get('bootstrap_version', "bs3");

				$wrapperClass = " jticketing-wrapper ";

				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " jticketing-wrapper tjBs3 ";
				}
				else
				{
					$wrapperClass = " jticketing-wrapper ";
				}

			define('JTICKETING_WRAPPER_CLASS', $wrapperClass);
		}

		// Load js assets
		$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

		if (File::exists($tjStrapperPath))
		{
			require_once $tjStrapperPath;
			TjStrapper::loadTjAssets('com_jticketing');
		}
	}

	/**
	 * Method to get country.
	 *
	 * @param   int     $countryId  id of country
	 * @param   string  $country    name of country
	 *
	 * @return  object
	 *
	 * @since   3.2.0
	 */
	public function getCountry($countryId = '', $country = '')
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		$countryTable = Table::getInstance('Country', 'TjfieldsTable');

		$parameter = array();

		if ($countryId)
		{
			$parameter['id'] = $countryId;
		}

		if ($country)
		{
			$parameter['country'] = $country;
		}

		$countryTable->load($parameter);

		$countryObj                 = new stdClass;
		$countryObj->id             = $countryTable->id;
		$countryObj->country        = $countryTable->country;
		$countryObj->country_3_code = $countryTable->country_3_code;
		$countryObj->country_code   = $countryTable->country_code;
		$countryObj->country_jtext  = $countryTable->country_jtext;

		return $countryObj;
	}

	/**
	 * Method to get state.
	 *
	 * @param   int     $regionId  region id
	 * @param   string  $region    state name
	 *
	 * @return  object
	 *
	 * @since   3.2.0
	 */
	public function getRegion($regionId = '', $region = '')
	{
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjfields/tables');
		$regionTable = Table::getInstance('Region', 'TjfieldsTable');

		$parameter = array();

		if ($regionId)
		{
			$parameter['id'] = $regionId;
		}

		if ($region)
		{
			$parameter['region'] = $region;
		}

		$regionTable->load($parameter);

		$regionObj                = new stdClass;
		$regionObj->id            = $regionTable->id;
		$regionObj->country_id    = $regionTable->country_id;
		$regionObj->region        = $regionTable->region;
		$regionObj->region_3_code = $regionTable->region_3_code;
		$regionObj->region_code   = $regionTable->region_code;
		$regionObj->region_jtext  = $regionTable->region_jtext;

		return $regionObj;
	}

	/**
	 * function for generating ICS file.
	 *
	 * @param   integer  $data  data of event
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public static function generateIcs($data)
	{
		$html = '';
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
	 * @since	3.2.0
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
	 * Method to apply css to html
	 *
	 * @param   array   $prev     replacement data for tags
	 * @param   string  $cssdata  int
	 *
	 * @return  string  html with css applied
	 *
	 * @deprecated 3.2.0 Use JT::utilities()->getEmorgify($html, $cssdata); instead
	 */
	public function getEmorgify($prev, $cssdata)
	{
		require_once JPATH_LIBRARIES . '/techjoomla/emogrifier/tjemogrifier.php';
		InitEmogrifier::initTjEmogrifier();

		// Condition to check if mbstring is enabled
		if (!function_exists('mb_convert_encoding'))
		{
			echo Text::_("MB_EXT");

			return $prev;
		}

		$emogr = new TjEmogrifier($prev, $cssdata);

		return $emogr->emogrify();
	}

	/**
	 * This function is used to load javascripts in component
	 *
	 * @param   string  &$jsFilesArray  com_jticketing/com_jevents
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   3.2.0
	 */
	public function getJticketingJsFiles(&$jsFilesArray)
	{
		$db       = Factory::getDbo();
		$input    = Factory::getApplication()->getInput();
		$option   = $input->get('option', '');
		$view     = $input->get('view', '');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();

		// Load css files
		$comparams      = ComponentHelper::getParams('com_jticketing');
		$load_bootstrap = $comparams->get('load_bootstrap');

		// Load bootstrap.min.js before loading other files
		if (!$app->isClient("administrator"))
		{
			if ($load_bootstrap)
			{
				HTMLHelper::_('stylesheet', 'media/techjoomla_strapper/bs3/css/bootstrap.min.css');
			}

			// Get plugin 'relatedarticles' of type 'content'
			$plugin = PluginHelper::getPlugin('system', 'plug_sys_jticketing');

			if ($plugin)
			{
				// Get plugin params
				$pluginParams = new Registry($plugin->params);
				$load         = $pluginParams->get('loadBS3js');
			}

			if (!empty($load))
			{
				$jsFilesArray[] = 'media/techjoomla_strapper/bs3/js/bootstrap.min.js';
			}
		}

		return $jsFilesArray;
	}

	/**
	 * Get user profile url
	 *
	 * @param   integer  $userid    userid
	 * @param   integer  $relative  relative
	 *
	 * @return  string  profile url
	 *
	 * @since   3.2.0
	 */
	public function getUserProfileUrl($userid, $relative = false)
	{
		$params                   = JT::config();
		$socialIntegrationOption  = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');
		$link                     = '';
		$length                   = strlen(Uri::base(true)) + 1;
		$user                     = Factory::getUser($userid);

		if ($socialIntegrationOption != 'none')
		{
			if ($socialIntegrationOption == 'joomla')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php'; }
				$sociallibraryclass = new JSocialJoomla;
			}
			elseif ($socialIntegrationOption == 'jomsocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php'; }
				$sociallibraryclass = new JSocialJomsocial;
			}
			elseif ($socialIntegrationOption == 'EasySocial')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }
				$sociallibraryclass = new JSocialEasysocial;
			}
			elseif($socialIntegrationOption == 'cb')
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/cb.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/cb.php'; }
				$sociallibraryclass = new JSocialCB;
			}

			$link = $sociallibraryclass->getProfileUrl($user, $relative);
		}

		return $link;
	}
}
