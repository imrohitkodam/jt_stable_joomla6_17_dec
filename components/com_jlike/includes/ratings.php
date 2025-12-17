<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\File;

JLoader::import('components.com_jlike.models.rating', JPATH_ADMINISTRATOR);
HTMLHelper::script('media/com_jlike/vendors/handlebarsjs/handlebars.js');
HTMLHelper::stylesheet('media/com_jlike/vendors/starrating/css/star.min.css');
HTMLHelper::script('components/com_jlike/assets/scripts/ratings/jlike_rating_ui.min.js');
HTMLHelper::script('components/com_jlike/assets/scripts/ratings/jlike_rating_service.min.js');
require_once  JPATH_SITE . '/components/com_jlike/defines.php';

/**
 * Jlike Component entry file
 *
 * @since  3.0.0
 */
class JlikeRatings
{
	/**
	 * display rating and reviews
	 *
	 * @param   integer  $element_id      elementid
	 * @param   string   $element         element
	 * @param   string   $url             url
	 * @param   string   $title           title
	 * @param   string   $ratingTypeCode  Unique rating type code
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function display($element_id, $element, $url, $title = '', $ratingTypeCode = '')
	{
		$loggedInUser = Factory::getUser();

		// Get the content id
		JLoader::import('components.com_jlike.models.contentform', JPATH_SITE);
		$contentFormModel = BaseDatabaseModel::getInstance('contentform', 'JLikeModel', array('ignore_request' => true));

		$data = array('element_id' => $element_id, 'element' => $element, 'url' => $url, 'title' => $title);
		$contentId = $contentFormModel->getContentID($data);

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');
		$ratingTypeTable = Table::getInstance('Ratingtype', 'JlikeTable', array('ignore_request' => true));
		$ratingTypeTable->load(array('code' => $ratingTypeCode, 'state' => 1));

		// If rating type not found, then get default rating type
		if (!$ratingTypeTable->id)
		{
			$ratingTypeTable->load(array('is_default' => 1, 'state' => 1));

			if (!$ratingTypeTable->id)
			{
				return;
			}
		}

		if ($ratingTypeTable->id)
		{
			// Show rating form to only logged-in user
			if ($loggedInUser->id)
			{
				$this->showRating($contentId, $ratingTypeTable->code);
			}
		}

		if ($ratingTypeTable->show_all_rating)
		{
			$this->showRatings($contentId);
		}
	}

	/**
	 * load rating layout
	 *
	 * @param   integer  $contentId       content id.
	 * @param   string   $ratingTypeCode  ratingTypeCode.
	 *
	 * @since   1.0.0
	 *
	 * @return void
	 */
	public function showRating($contentId, $ratingTypeCode)
	{
		$app     = Factory::getApplication();
		$componentPath = JPATH_SITE . '/components/com_jlike';
		$adminComponentPath = JPATH_ADMINISTRATOR . '/components/com_jlike';

		// @require_once $adminComponentPath . '/models/rating.php';
		require_once $componentPath . '/controller.php';

		$config = array ();

		$component = new JlikeController(array('name' => 'Jlike', $config['base_path'] => $componentPath));

		$component->addViewPath($componentPath . '/views');
		$component->addModelPath($adminComponentPath . '/models');

		// $model = $component->getModel('rating');
		$model = BaseDatabaseModel::getInstance('rating', 'JLikeModel', array('ignore_request' => true));

		$view  = $component->getView('rating', 'html');

		Form::addFormPath($componentPath . '/models/forms');
		$form = $model->getForm();
		$jinput = $app->input;
		$jinput->set('ratingTypeCode', $ratingTypeCode);
		$jinput->set('contentId', $contentId);

		$view->setModel($model);

		$templatePath = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/com_jlike/rating';

		if (File::exists($templatePath . '/default.php'))
		{
			$view->addTemplatePath($templatePath);
		}
		else
		{
			$view->addTemplatePath($componentPath . '/views/rating/tmpl');
		}

		$view->display();
	}

	/**
	 * load ratings layout
	 *
	 * @param   integer  $contentId  contentId
	 *
	 * @since   1.0.0
	 * @return void
	 */
	public function showRatings($contentId)
	{
		$app     = Factory::getApplication();
		$componentPath = JPATH_SITE . '/components/com_jlike';
		$adminComponentPath = JPATH_ADMINISTRATOR . '/components/com_jlike';

		// @require_once $adminComponentPath . '/models/ratings.php';
		require_once $componentPath . '/controller.php';

		$config = array ();

		$component = new JlikeController(array('name' => 'Jlike', $config['base_path'] => $componentPath));

		$component->addViewPath($componentPath . '/views');
		$component->addModelPath($adminComponentPath . '/models');

		// $model = $component->getModel('ratings');
		$model = BaseDatabaseModel::getInstance('ratings', 'JLikeModel', array('ignore_request' => true));

		$view  = $component->getView('ratings', 'html');

		Form::addFormPath($componentPath . '/models/forms');

		$jinput = $app->input;
		$jinput->set('contentId', $contentId);

		$view->setModel($model);

		$templatePath = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/com_jlike/ratings';

		if (File::exists($templatePath . '/default.php'))
		{
			$view->addTemplatePath($templatePath);
		}
		else
		{
			$view->addTemplatePath($componentPath . '/views/ratings/tmpl');
		}

		$view->display();
	}
}
