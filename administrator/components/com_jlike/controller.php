<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * JlikeController
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeController extends BaseController
{
	/**
	 * Migrate like
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  JController This object to support chaining..
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';

		$view = Factory::getApplication()->getInput()->getCmd('view', 'dashboard');
		Factory::getApplication()->getInput()->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * Apply
	 *
	 * @return  null
	 */
	public function apply()
	{
		$model = $this->getModel('buttonset');

		if ($model->store())
		{
			$msg = Text::_('COM_JLIKE_SAVED');
		}
		else
		{
			$msg = Text::_('COM_JLIKE_ERROR_IN_SAVING');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_jlike&view=buttonset';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Add
	 *
	 * @return  return null
	 */
	public function add()
	{
		$db       = Factory::getDbo();
		$file     = Factory::getApplication()->getInput()->files->get('file');

		$filename = $file['name'];
		$arr      = explode('.', $file['name']);
		$ext      = array_pop($arr);

		$imgPath     = JPATH_COMPONENT_SITE . '/assets/images/buttonset/';
		$destination = $imgPath . $filename;

		$allowedImageExtension = array("png", "jpg", "jpeg");

		// Validate file input to check if is with valid extension
		if (!in_array($ext, $allowedImageExtension))
		{
			$msg = Text::_('COM_JLIKE_BUTTONSET_IMAGE_FILE_VALIDATION');
			$this->setRedirect('index.php?option=com_jlike&view=buttonset', $msg, 'error');

			return false;
		}

		if (!move_uploaded_file($file['tmp_name'], $destination))
		{
			$msg = Text::_('Error: Cannot move uploaded file');
			$this->setRedirect('index.php?option=com_jlike', $msg);
		}

		$db = Factory::getDbo();
		$db->setQuery("INSERT INTO `#__jlike` set `published`=0,`title`='{$filename}';");
		$db->execute();
		$id = $db->insertid();

		if (!rename($destination, $imgPath . $filename))
		{
			$msg = Text::_('Error: Cannot rename uploaded file');
			$this->setRedirect('index.php?option=com_jlike', $msg);
		}

		$this->setRedirect('index.php?option=com_jlike&view=buttonset', Text::_('COM_JLIKE_FILE_UPLOAD_SUCCESS'));
	}

	/**
	 * Cancel
	 *
	 * @return  return null
	 */
	public function cancel()
	{
		$msg = Text::_('Operation Cancelled');
		$this->setRedirect('index.php?option=com_jlike', $msg);
	}

	/**
	 * Getversion AJax call
	 *
	 * @return  return null
	 */
	public function getVersion()
	{
		echo $recdata = file_get_contents('http://techjoomla.com/vc/index.php?key=abcd1234&product=jlike');
		Factory::getApplication()->close();
	}

	/**
	 * Migrate like
	 *
	 * @return  return null
	 */
	public function migrateLikes()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		include_once JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'helpers' . DS . 'migration.php';
		$jlikemigrateobj = new comjlikeMigrateHelper;
		$result          = $jlikemigrateobj->migrateLikes();

		if ($result)
		{
			echo json_encode(1);
		}
		else
		{
			echo json_encode(-1);
		}

		Factory::getApplication()->close();
	}

	/**
	 * Save
	 *
	 * @return  return null
	 */
	public function save()
	{
		$model = $this->getModel('element_config');

		if ($model->store())
		{
			$msg = Text::_('COM_JLIKE_DATA_SAVED');
		}
		else
		{
			$msg = Text::_('COM_JLIKE_DATA_SAVED_ERROR');
		}

		$this->setRedirect('index.php?option=com_jlike&view=element_config', $msg);
	}

	/**
	 * Get Item and status related data for csv emport
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function csvExportStatusdetails()
	{
		$comjlikeHelper = new comjlikeHelper;

		$model         = $this->getModel("contents");
		$CSVData       = $model->csvExportStatusdetails();

		if (!empty($CSVData))
		{
			$status_code = $CSVData[0]->statusCount;

			$filename      = Text::_('COM_JLIKE_STATUS_REPORTS') . date("Y-m-d_h:i:s") . '_' . $status_code;
			$params        = ComponentHelper::getParams('com_jlike');
			$currency      = $params->get('currency_symbol');
			$csvData       = null;

			// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
			$headColumn    = array();
			$headColumn[0] = Text::_('COM_JLIKE_STATUS_EXPORT_ID');
			$headColumn[1] = Text::_('COM_JLIKE_STATUS_EXPORT_TITLE');
			$headColumn[2] = Text::_('COM_JLIKE_STATUS_EXPORT_URL');
			$headColumn[3] = Text::_('COM_JLIKE_STATUS_EXPORT_CAT_ID');
			$headColumn[4] = Text::_('COM_JLIKE_STATUS_EXPORT_CATE_NAME');
			$headColumn[5] = Text::_('COM_JLIKE_STATUS_EXPORT_STATUSCOUNT');

			/*$headColumn[6] = Text::_('COM_JLIKE_STATUS_EXPORT_ELEMENT_ID');
			$headColumn[7] = Text::_('COM_JLIKE_STATUS_EXPORT_ELEMENT');
			*/

			$csvData .= implode(";", $headColumn);
			$csvData .= "\n";
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=" . $filename . ".csv");

			foreach ($CSVData as $data)
			{
				$csvrow    = array();
				$csvrow[0] = '"' . $data->id . '"';
				$csvrow[1] = '"' . $data->title . '"';
				$csvrow[2] = '"' . $data->url . '"';
				$csvrow[3] = '"' . $data->category_id . '"';

				// Get category name
				$csvrow[4] = '';

				if (!empty($data->category_id))
				{
					$csvrow[4] = '"' . $comjlikeHelper->getZooCatName($data->category_id) . '"';
				}

				$csvrow[5] = '"' . $data->statusCount . '"';

				/*
				$csvrow[6] = '"' . $data->element_id . '"';
				$csvrow[7] = '"' . $data->element . '"';
				*/

				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}

			ob_clean();
			echo $csvData . "\n";
			jexit();
		}

		$link = Uri::base() . substr(Route::_('index.php?option=com_jlike&view=contents', false), strlen(Uri::base(true)) + 1);
		$this->setRedirect($link);
	}

	/**
	 * Get Item and status related data for csv emport
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function csvExportAllRatings()
	{
		$comjlikeHelper = new comjlikeHelper;

		$model         = $this->getModel("ratings");
		$CSVData       = $model->csvExportAllRatings();

		if (!empty($CSVData))
		{
			$allStatusList = (array) $comjlikeHelper->getAllStatus();
			$status_code   = $CSVData[0]->statusCount;

			$filename      = Text::_('COM_JLIKE_STATUS_RATES_PLANS_REPORTS') . date("Y-m-d_h:i:s") . '_' . $status_code;
			$params        = ComponentHelper::getParams('com_jlike');
			$currency      = $params->get('currency_symbol');
			$csvData       = null;

			// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
			$headColumn    = array();
			$headColumn[0] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_ID');
			$headColumn[1] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_TITLE');
			$headColumn[2] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_URL');
			$headColumn[3] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_CAT_ID');
			$headColumn[4] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_CATE_NAME');
			$headColumn[5] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_STATUS_TEXT');
			$headColumn[6] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_STATUS_ID');
			$headColumn[7] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_USER_ID');

			/*$headColumn[6] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_ELEMENT_ID');
			$headColumn[7] = Text::_('COM_JLIKE_STATUS_RATES_EXPORT_ELEMENT');
			*/

			$csvData .= implode(";", $headColumn);
			$csvData .= "\n";
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=" . $filename . ".csv");

			foreach ($CSVData as $data)
			{
				$csvrow    = array();
				$csvrow[0] = '"' . $data->id . '"';
				$csvrow[1] = '"' . $data->title . '"';
				$csvrow[2] = '"' . $data->url . '"';
				$csvrow[3] = '"' . $data->category_id . '"';

				// Get category name
				$csvrow[4] = '';

				if (!empty($data->category_id))
				{
					$csvrow[4] = '"' . $comjlikeHelper->getZooCatName($data->category_id) . '"';
				}

				$csvrow[5] = '"' . $data->status_id . '"';

				if (!empty($allStatusList[$data->status_id]))
				{
					$currentStatusCode = $allStatusList[$data->status_id]->status_code;
					$csvrow[5]         = '"' . Text::_($currentStatusCode) . '"';
				}

				$csvrow[6] = '"' . $data->status_id . '"';
				$csvrow[7] = '"' . $data->user_id . '"';
				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}

			ob_clean();
			echo $csvData . "\n";
			jexit();
		}

		$link = Uri::base() . substr(Route::_('index.php?option=com_jlike&view=contents', false), strlen(Uri::base(true)) + 1);
		$this->setRedirect($link);
	}
}
