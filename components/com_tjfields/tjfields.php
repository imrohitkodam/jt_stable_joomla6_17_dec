<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

// Load TjfieldsHelper
if (file_exists(JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php'))
{
	require_once JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';
	TjfieldsHelper::getLanguageConstantForJs();
}

// Execute the task.
$controller	= BaseController::getInstance('Tjfields');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
