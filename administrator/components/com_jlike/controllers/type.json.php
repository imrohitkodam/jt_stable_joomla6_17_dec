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
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Load category list model from admin
if (file_exists(JPATH_ROOT . '/administrator/components/com_categories/models/categories.php')) {
	require_once JPATH_ROOT . '/administrator/components/com_categories/models/categories.php';
}

/**
 * path type controller class.
 *
 * @since  1.6
 */
class JlikeControllerType extends FormController
{
	protected $pathTypeCategoryExtension = 'com_jlike.path.';

	/**
	 * Function to get path type params
	 *
	 * @return  object  object
	 */
	public function getPathTypeParams()
	{
		if (!Session::checkToken('get'))
		{
			echo new JResponseJson(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app = Factory::getApplication();
			$input     = $app->input;

			$getDefaultPathType = $input->getInt('defaultPathType', 0);

			if ($getDefaultPathType)
			{
				$defaultPathParams = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_jlike/path_params.json');

				echo new JsonResponse(json_decode($defaultPathParams));

				return;
			}

			$pathTypeId = $input->getInt('pathTypeId', 0);

			if ($pathTypeId == 0)
			{
				echo new JResponseJson(null, Text::_('COM_JLIKE_PATH_TYPE_EMPTY'), true);

				return;
			}

			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');
			$pathTypeTable = Table::getInstance('Type', 'JlikeTable');

			$pathTypeTable->load(array('path_type_id' => $pathTypeId));

			echo new JsonResponse(json_decode($pathTypeTable->params));
		}
	}

	/**
	 * Function to get path type categories
	 *
	 * @return  object  object
	 */
	public function getPathTypeCategories()
	{
		if (!Session::checkToken('get'))
		{
			echo new JResponseJson(null, Text::_('JINVALID_TOKEN'), true);
		}
		else
		{
			$app      = Factory::getApplication();
			$input    = $app->input;
			$pathType = $input->getInt('pathType', 0);

			if (empty($pathType))
			{
				echo new JResponseJson(null, Text::_('COM_JLIKE_PATH_TYPE_EMPTY'), true);

				return;
			}

			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');
			$pathTypeTable = Table::getInstance('Type', 'JlikeTable');

			$pathTypeTable->load(array('path_type_id' => $pathType));

			if (empty($pathTypeTable->identifier))
			{
				echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);

				return;
			}

			$extension = $this->pathTypeCategoryExtension . $pathTypeTable->identifier;

			$model = BaseDatabaseModel::getInstance('Categories', 'CategoriesModel', array('ignore_request' => true));
			$model->setState('filter.extension', $extension);
			$model->setState('filter.published', 1);
			$categoryList = $model->getItems();

			echo new JsonResponse(json_encode($categoryList));
		}
	}
}
