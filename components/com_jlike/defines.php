<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$document   = Factory::getDocument();
$document->addScriptDeclaration('var jlikeRootPath = "' . Uri::root() . '"');
