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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

	/**
	 * Pendingpaid controller class.
	 *
	 * @since  3.2
	 */
class JticketingControllerpendingpaid extends BaseController
{
	/**
	 *Function to save
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function save()
	{
		$input = Factory::getApplication()->getInput();
		$task = $input->get('task');

		switch ($task)
		{
			case 'cancel':
			$this->setRedirect('index.php?option=com_jticketing');
		}
	}

	/**
	 *Function to cancel
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function cancel()
	{
		$input = Factory::getApplication()->getInput();
		$task = $input->get('task');

		switch ($task)
		{
			case 'cancel':
			$this->setRedirect('index.php?option=com_jticketing');
		}
	}

	/**
	 *Function to csv export
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function csvexport()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$model = $this->getModel('pendingpaid');

		$com_params = ComponentHelper::getParams('com_jticketing');
		$currency = $com_params->get('currency');
		$Data = $model->getData();

		foreach ($Data as &$data)
		{
			$data->pendingcount = $model->pendingcount($data->eventid);
			$data->confirmcount = $model->confirmcount($data->eventid);
		}

		$csvData = null;
		$csvData_arr[] = Text::_('COM_JTICKETING_EVENT_NAME');
		$csvData_arr[] = Text::_('COM_JTICKETING_NUMBER_OF_SEATS');
		$csvData_arr[] = Text::_('COM_JTICKETING_FULLY_PAID_SEATS');
		$csvData_arr[] = Text::_('COM_JTICKETING_PENDING_SEATS');

		$filename = "Jt_attendees_" . date("Y-m-d_H-i", time());

		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");

		$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

		$csvData .= implode(';', $csvData_arr);
		$csvData .= "\n";
		echo $csvData;

		/*'P'=>Text::_('JT_PSTATUS_PENDING'),
		'C'=>Text::_('JT_PSTATUS_COMPLETED'),
		'D'=>Text::_('JT_PSTATUS_DECLINED'),
		'E'=>Text::_('JT_PSTATUS_FAILED'),
		'UR'=>Text::_('JT_PSTATUS_UNDERREVIW'),
		'RF'=>Text::_('JT_PSTATUS_REFUNDED'),
		'CRV'=>Text::_('JT_PSTATUS_CANCEL_REVERSED'),
		'RV'=>Text::_('JT_PSTATUS_REVERSED'),
		);*/

		$csvData = '';

		foreach ($Data as $data )
		{
			$phone = $email = '';

			if (!$data->confirmcount)
			{
				$data->confirmcount = 0;
			}

			if (!$data->pendingcount)
			{
				$data->pendingcount = 0;
			}

			$csvData = $doc_submitted = $checkin = '';
			$csvData_arr1 = array();

			$csvData_arr1[] = ucfirst($data->title);
			$csvData_arr1[] = $data->pendingcount + $data->confirmcount;
			$csvData_arr1[] = $data->confirmcount;
			$csvData_arr1[] = $data->pendingcount;
			$csvData = implode(';', $csvData_arr1);

			echo $csvData . "\n";
		}

		echo $csvData . "\n";
		jexit();
	}
}
