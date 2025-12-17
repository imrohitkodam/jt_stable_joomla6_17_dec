<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * The Categories List Controller
 *
 * @since  1.6
 */
class JticketingControllerCatimpexp extends AdminController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'catimpexp', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to categories csv Import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvImport()
	{
		$mainframe = Factory::getApplication();
		$fileArray = $mainframe->getInput()->files->get('csvfile', '', 'NONE');
		$uploads_dir = $mainframe->get('tmp_path') . '/';

		// Start file heandling functionality *
		$extension    = File::getExt($fileArray['name']);
		$fileName     = File::stripExt($fileArray['name']);
		$fileName     = File::makeSafe($fileName);
		$fileName     = preg_replace('/\s/', '_', $fileName);

		// Generate random string
		$randomString = Factory::getUser()->id . '_' . time();

		// New name
		$newFileName = $fileName . '_' . $randomString;

		// New full path
		$newFilePath = $uploads_dir . $newFileName . '.' . $extension;

		if (!isset($fileArray['tmp_name']) || empty($fileArray) || !$fileArray['tmp_name']) 
		{
			$mainframe->enqueueMessage(Text::_('COM_JTICKETING_ERROR_IN_MOVING'), 'warning');

			$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

			return;
		}

		if (!File::upload($fileArray['tmp_name'], $newFilePath, false, true))
		{
			$mainframe->enqueueMessage(Text::_('COM_JTICKETING_ERROR_IN_MOVING'), 'warning');

			$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

			return;
		}

		// $file = fopen($uploads_dir, "r");

		// $info = pathinfo($uploads_dir);

		$least_data = 0;

		$rowNum = 0;

		if ($file = fopen($newFilePath, "r"))
		{
			if (File::getExt($newFilePath) != 'csv')
			{
				$msg = Text::_('NOT_CSV_MSG');
				$mainframe->enqueueMessage("<b>" . $msg . "</b>", 'error');
				$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

				return;
			}

			// Parsing the CSV header
			$headers = array();

			while (($data = fgetcsv($file)) !== false)
			{
				if ($rowNum == 0)
				{
					foreach ($data as $d)
					{
						$headers[] = $d;
					}
				}
				else
				{
					// Parsing the data rows
					$rowData = array();

					foreach ($data as $d)
					{
						$rowData[] = $d;
					}

					if (count($headers) == count($rowData))
					{
						$categoryData[] = array_combine($headers, $rowData);
					}
				}

				$rowNum++;
			}

			fclose($file);
		}
		else
		{
			// $msg = Text::_('File not open');
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_('COM_JTICKETING_SOME_ERROR_OCCURRED'), 'error');
			$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

			return;
		}

		// End file heandling functionality
		if (!$categoryData)
		{
			$msg = Text::_('ZERO_CONTACTS_CSV_ERROR');
			$mainframe->enqueueMessage("<b>" . $msg . "</b>", 'error');
			$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

			return;
		}
		else
		{
			$model = $this->getModel();
			$return = $model->importCategoryCSVdata($categoryData);
			$msg = $return['msg'];

			if (!empty($return['msg1']))
			{
				$msg .= $return['msg1'];
				$mainframe->enqueueMessage("<b>" . $msg . "</b>", 'error');
			}
			else
			{
				$mainframe->enqueueMessage("<b>" . $msg . "</b>", 'success');
			}

			$mainframe->redirect(Route::_('index.php?option=com_jticketing&view=catimpexp', false));

			return;
		}
	}

	/**
	 * Method to categories csv export
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvexport()
	{
		// #load categories model file as it is
		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_categories/models/categories.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_categories/models/categories.php'; }
		}
		else
		{
			// Joomla 6: Use JoomlaComponentCategoriesAdministratorModelCategoriesModel
		}

		/*$table = Table::getInstance('Category', 'CategoriesTable', $config = array());

		$categoriesModel = new CategoriesModelCategories;
		$extension       = $categoriesModel->setState('filter.extension', 'com_jticketing');
		$DATA            = $categoriesModel->getItems();*/
		$db              = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title, parent_id');
		$query->from('#__categories');
		$query->where('extension = ' . "'com_jticketing'");
		$db->setQuery($query);
		$DATA = $db->loadObjectList();

		// Create CSV headers
		$csvData       = null;
		$csvData_arr[] = Text::_('Id');
		$csvData_arr[] = Text::_('COM_JTICKETING_CATEGORN_NAME');
		$csvData_arr[] = Text::_('COM_JTICKETING_PARENT_ID');

		$filename = "EventCategoryExport_" . date("Y-m-d_H-i", time());

		// Set CSV headers
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . $filename . ".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$csvData .= implode(',', $csvData_arr);
		$csvData .= "\n";
		echo $csvData;

		$csvData      = '';

		foreach ($DATA as $data)
		{
			$csvData_arr1 = array();

			if ($data->id)
			{
				$csvData_arr1[] = $data->id;
				$csvData_arr1[] = $data->title;
				$csvData_arr1[] = $data->parent_id;

				$csvData = implode(',', $csvData_arr1);
				echo $csvData . "\n";
			}
		}

		jexit();
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->getInput();
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
}
