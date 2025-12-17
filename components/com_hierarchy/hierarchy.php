<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Include dependancies
HTMLHelper::_('bootstrap.loadcss');
HTMLHelper::_('bootstrap.framework');

// Execute the task.
$app = Factory::getApplication();
$controller = BaseController::getInstance('Hierarchy');
$controller->execute($app->getInput()->get('task'));
$controller->redirect();

// Initialize hierarchy js
$document = Factory::getDocument();
HTMLHelper::_('script', '/media/com_hierarchy/js/hierarchy.js');
