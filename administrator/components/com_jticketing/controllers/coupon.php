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
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Coupon controller class.
 *
 * @since  3.2
 */
class JticketingControllerCoupon extends FormController
{
	/**
	 *Function to construct a coupon controller
	 *
	 * @since  3.2
	 */
	public function __construct()
	{
		$this->view_list = 'coupons';
		parent::__construct();
	}
}