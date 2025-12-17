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
use Joomla\Filesystem\Folder;
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

// Joomla 6: JLoader removed - use require_once
$houseKeepingPath = JPATH_SITE . "/libraries/techjoomla/controller/houseKeeping.php";
if (file_exists($houseKeepingPath))
{
	require_once $houseKeepingPath;
}

/**
 * Dashboard form controller class.
 *
 * @package  JTicketing
 * @since    1.8
 */
class JticketingControllercp extends jticketingController
{
	use TjControllerHouseKeeping;

	/**
	 * Method for getVersion
	 *
	 * @return void
	 *
	 * @since   1.8
	 */
	public function getVersion()
	{
		$url = "https://techjoomla.com/vc/index.php?key=abcd1234&product=jticketing";
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		echo $data;
		jexit();
	}

	/**
	 * Method for Save
	 *
	 * @return void
	 *
	 * @since   1.8
	 */
	public function save()
	{
		$input = Factory::getApplication()->getInput();

		switch ($input->get('task'))
		{
			case 'cancel':
				$this->setRedirect('index.php?option=com_broadcast');
			break;
			case 'save':
				if ($this->getModel('cp')->store(Factory::getApplication()->getInput()->get('post')))
				{
					$msg = Text::_('QUEUE_SAVED');
				}
				else
				{
					$msg = Text::_('QUEUE_SAVE_PROBLEM');
				}

				$this->setRedirect("index.php?option=com_broadcast&view=cp&layout=queue", $msg);
			break;
		}
	}

	/**
	 * Method for Cancel
	 *
	 * @return void
	 *
	 * @since   1.8
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_broadcast');
	}

	/**
	 * Method for Set session for graph
	 *
	 * @return  Json Statsforpie chart data
	 *
	 * @since   1.8
	 */
	public function SetsessionForGraph()
	{
		$input = Factory::getApplication()->getInput();
		$periodicorderscount = '';
		$fromDate = $input->get('fromDate');
		$toDate = $input->get('toDate');
		$periodicorderscount = 0;

		$session = Factory::getSession();
		$session->set('jticketing_from_date', $fromDate);
		$session->set('jticketing_end_date', $toDate);

		$model = $this->getModel('cp');
		$statsforpie = $model->statsForPie();
		$periodicorderscount = $model->getperiodicorderscount();
		$session->set('statsforpie', $statsforpie);
		$session->set('periodicorderscount', $periodicorderscount);

		header('Content-type: application/json');
		echo json_encode(array("statsforpie" => $statsforpie));

		jexit();
	}

	/**
	 * Method makechart
	 *
	 * @return  Json data
	 *
	 * @since   1.8
	 */
	public function makechart()
	{
		$month_array_name = array(
			Text::_('SA_JAN'),
			Text::_('SA_FEB'),
			Text::_('SA_MAR'),
			Text::_('SA_APR'),
			Text::_('SA_MAY'),
			Text::_('SA_JUN'),
			Text::_('SA_JUL'),
			Text::_('SA_AUG'),
			Text::_('SA_SEP'),
			Text::_('SA_OCT'),
			Text::_('SA_NOV'),
			Text::_('SA_DEC')
		);
		$session = Factory::getSession();
		$jticketing_from_date = '';
		$jticketing_end_date = '';
		$statsforbar = '';
		$jticketing_from_date = $session->get('jticketing_from_date', '');
		$jticketing_end_date = $session->get('jticketing_end_date', '');
		$total_days = (strtotime($jticketing_end_date) - strtotime($jticketing_from_date)) / (60 * 60 * 24);
		$total_days = $total_days ++;
		$statsforbar = $session->get('statsforbar', '');
		$statsforpie = $session->get('statsforpie', '');
		$periodicorderscount = $session->get('periodicorderscount');
		$imprs = 0;
		$clicks = 0;

		$emptylinechart = 0;
		$barchart = '';
		$fromDate = $session->get('jticketing_from_date', '');
		$toDate = $session->get('jticketing_end_date', '');

		$dateMonthYearArr = array();
		$fromDateSTR = strtotime($fromDate);
		$toDateSTR = strtotime($toDate);
		$pending_orders = $confirmed_orders = $refund_orders = 0;

		if (empty($statsforpie))
		{
			$barchart = Text::_('NO_STATS');
			$emptylinechart = 1;
		}
		else
		{
			if (!empty($statsforpie[0]))
			{
				$pending_orders = $statsforpie[0][0]->orders;
			}

			if (!empty($statsforpie[1]))
			{
				$confirmed_orders = $statsforpie[1][0]->orders;
			}

			if (!empty($statsforpie[2]))
			{
				$denied_orders = $statsforpie[2][0]->orders;
			}

			if (!empty($statsforpie[3]))
			{
				$failed_orders = $statsforpie[3][0]->orders;
			}

			if (!empty($statsforpie[4]))
			{
				$underReview = $statsforpie[4][0]->orders;
			}

			if (!empty($statsforpie[5]))
			{
				$refunded_orders = $statsforpie[5][0]->orders;
			}

			if (!empty($statsforpie[6]))
			{
				$canceled_orders = $statsforpie[6][0]->orders;
			}

			if (!empty($statsforpie[7]))
			{
				$reversed_orders = $statsforpie[7][0]->orders;
			}
		}

		header('Content-type: application/json');
		echo json_encode(
		array(
		"pending_orders" => $pending_orders,
		"confirmed_orders" => $confirmed_orders,
		"denied_orders" => $denied_orders,
		"failed_orders" => $failed_orders,
		"underReview" => $underReview,
		"refunded_orders" => $refunded_orders,
		"canceled_orders" => $canceled_orders,
		"reversed_orders" => $reversed_orders,

		"periodicorderscount" => $periodicorderscount,
		"emptylinechart" => $emptylinechart
							)
							);

		jexit();
	}

	/**
	 * Manual Setup related chages: For now - 1. for overring the bs-2 view
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function setup()
	{
		$jinput = Factory::getApplication()->getInput();
		$takeBackUp = $jinput->get("takeBackUp", 1);

		$defTemplate = JT::utilities()->getSiteDefaultTemplate(0);
		$templatePath = JPATH_SITE . '/templates/' . $defTemplate . '/html/';

		$statusMsg = array();
		$statusMsg["component"] = array();

		// 1. Override component view
		$siteBs2views = JPATH_ROOT . "/components/com_jticketing/views_bs2/site";

		// Check for com_jticketing folder in template override location
		$compOverrideFolder  = $templatePath . "com_jticketing";

		if (Folder::exists($compOverrideFolder))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $compOverrideFolder . '_' . date("Ymd_H_i_s");
				$status = Folder::move($compOverrideFolder, $backupPath);
				$statusMsg["component"][] = Text::_('COM_JTICKETING_TAKEN_BACKUP_OF_OVERRIDE_FOLDER') . $backupPath;
			}
			else
			{
				$delStatus = Folder::delete($compOverrideFolder);
			}
		}

		// Copy
		$status = Folder::copy($siteBs2views, $compOverrideFolder);
		$statusMsg["component"][] = Text::_('COM_JTICKETING_OVERRIDE_DONE') . $compOverrideFolder;

		// 2. Create Override plugins folder if not exist
		$pluginsPath = JPATH_ROOT . "/components/com_jticketing/views_bs2/plugins/";

		// Check for com_jticketing folder in template override location
		$pluginsOverrideFolder  = $templatePath . "plugins";
		$createFolderStatus = Folder::create($pluginsOverrideFolder);

		$statusMsg["plugins"][] = Text::_('COM_JTICKETING_CREATE_PLUGINS_FOLDER_FAILED');

		// 3. Modules override
		$modules = Folder::folders(JPATH_ROOT . "/components/com_jticketing/views_bs2/modules/");
		$statusMsg["modules"] = array();

		foreach ($modules as $modName)
		{
			$this->overrideModule($templatePath, $modName, $statusMsg, $takeBackUp);
		}

		$this->displaySetup($statusMsg);
		exit;
	}

	/**
	 * Override the Modules
	 *
	 * @param   array  $statusMsg  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function displaySetup($statusMsg)
	{
		echo "<br/> =================================================================================";
		echo "<br/> " . Text::_("COM_JTICKETING_BS2_OVERRIDE_PROCESS_START");
		echo "<br/> =================================================================================";

		foreach ($statusMsg as $key => $extStatus)
		{
			echo "<br/> <br/><br/>*****************  " . Text::_("COM_JTICKETING_BS2_OVERRIDING_FOR") .
			" <strong>" . $key . "</strong> ****************<br/>";

			foreach ($extStatus as $k => $status)
			{
				$index = $k + 1;
				echo $index . ") " . $status . "<br/> ";
			}
		}

		echo "<br/> " . Text::_("COM_JTICKETING_BS2_OVERRIDING_DONE");
	}
}
