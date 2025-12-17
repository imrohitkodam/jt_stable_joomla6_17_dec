<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Class for getting user events based on user id
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketApiResourceGetTicketSales extends ApiResource
{
	/**
	 * Get Event data
	 *
	 * @return  json user list
	 *
	 * @since   1.0
	 */
	public function get()
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');
		$currency = $com_params->get('currency_symbol');
		$input       = Factory::getApplication()->input;
		$lang      = Factory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir  = JPATH_SITE;
		$lang->load($extension, $base_dir);
		$obj_merged = array();
		$userid = $input->get('userid', '', 'INT');

		$res = new stdClass;
		$res->result = array();
		$res->empty_message = '';

		if (empty($userid))
		{
			$res->empty_message = Text::_("COM_JTICKETING_INVALID_USER");

			return $this->plugin->setResponse($res);
		}

		$jticketingmainhelper = new jticketingmainhelper;
		$plugin = PluginHelper::getPlugin('api', 'jticket');

		// Check if plugin is enabled
		if ($plugin)
		{
			// Get plugin params
			$pluginParams = new Registry($plugin->params);
			$users_allow_access_app = $pluginParams->get('users_allow_access_app');
		}

		// If user is in allowed user to access APP show all events to that user
		if (is_array($users_allow_access_app) and in_array($userid, $users_allow_access_app))
		{
			$eventdatapaid = $jticketingmainhelper->getSalesDataAdmin($userid);
		}
		else
		{
			$eventdatapaid = $jticketingmainhelper->getSalesDataAdmin($userid);
		}

		$db = Factory::getDbo();
		$db->setQuery($eventdatapaid);
		$results = $db->loadObjectlist();
		$obj_merged = $results;

		if ($obj_merged)
		{
			if ($obj_merged[0])
			{
				$obj_merged[0]->eoriginal_amount = $obj_merged[0]->eamount ? number_format((float)$obj_merged[0]->eamount, 2, '.', '') : 0;
				$obj_merged[0]->ecommission = $obj_merged[0]->ecommission ? number_format((float)$obj_merged[0]->ecommission, 2, '.', '') : 0;
				$obj_merged[0]->eamount = $obj_merged[0]->eoriginal_amount - $obj_merged[0]->ecommission;
			}

			$res->result = $obj_merged;
			$res->result[0]->currency = $currency;
		}
		else
		{
			$res->empty_message = Text::_("NODATA");
		}

		$this->plugin->setResponse($res);
	}

	/**
	 * Post Method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function post()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}

	/**
	 * Put method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function put()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}

	/**
	 * Delete method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete()
	{
		$this->plugin->err_code = 405;
		$this->plugin->err_message = Text::_("COM_JTICKETING_SELECT_GET_METHOD");
		$this->plugin->setResponse(null);
	}
}
