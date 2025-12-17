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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Makepayment controller class.
 *
 * @since  3.2
 */
class JticketingControllermakepayment extends BaseController
{
	/**
	 *Function to save
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function setOrder()
	{
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/frontendhelper.php'; }
		$mainHelper = new jticketingfrontendhelper;
		$id = Factory::getApplication()->getInput()->get('id');
		$target_data = $mainHelper->getbookingDetails($id);
		$postdata = Factory::getApplication()->getInput()->get('post');
		$total = '';

		foreach ($postdata as $key => $value)
		{
			foreach ($target_data['order_item'] as $data)
			{
				if ($data->id == $key )
				{
					$total += $value;
					$data->price = $value;
				}
			}
		}

		$target_data['order'][0]->totalprice = $total;
		$target_data['order'][0]->parent_id = $id;
		/*
		 * Array
		(
			[order] => Array
				(
					[0] => stdClass Object
						(
							[id] => 12
							[name] => Super User
							[order_id] => JT-00012
							[order_amount] => 500.00
							[original_amount] => 1100.00
							[status] => P
						)

				)

			[order_item] => Array
				(
					[0] => stdClass Object
						(
							[id] => 8
							[order_id] => 12
							[type_id] => 5
							[ticket_price] => 600.00
							[total] => 300.00
							[name] => Sachin
							[payment_status] => P
							[price] => 12
						)

					[1] => stdClass Object
						(
							[id] => 9
							[order_id] => 12
							[type_id] => 5
							[ticket_price] => 500.00
							[total] => 200.00
							[name] => Sachin
							[payment_status] => P
							[price] => 13
						)

				)

		)
		 *
		 */

		$model = $this->getModel('makepayment');
		$val   = $model->saveBalance($target_data);

		$redirect = Uri::base() . 'index.php?option=com_jticketing&view=allticketsales&layout=pay&id=' . $val;
		$this->setRedirect($redirect);
	}

	/**
	 *Function to save
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function cancel()
	{
		$redirect = Uri::base() . 'index.php/component/jticketing/?view=allticketsales&layout=myevent&Itemid=232';
		$this->setRedirect($redirect);
	}
}
