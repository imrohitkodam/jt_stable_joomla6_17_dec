<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';
TJCERT::init('site');

// Register namespace for autoloading
JLoader::registerNamespace('TjCertificate', JPATH_COMPONENT);
require_once JPATH_COMPONENT . '/controller.php';

// Execute the task.
$controller = BaseController::getInstance('TjCertificate');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
