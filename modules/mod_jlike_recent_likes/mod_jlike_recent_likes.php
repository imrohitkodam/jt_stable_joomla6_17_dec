<?php
/**
 * @package     JLike
 * @subpackage  mod_lms_categorylist
 * @copyright   Copyright (C) 2009-2014 Techjoomla, Tekdi Technologies Pvt. Ltd. All rights reserved.
 * @license     GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link        http://www.techjoomla.com
 */
// No direct access.
	defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
	require_once JPATH_SITE . '/components/com_jlike/helper.php';

	$input = Factory::getApplication()->getInput();
	$post	= $input->post;

	$session = Factory::getSession();
	$Itemid = Factory::getApplication()->getInput()->get('Itemid');

	if ($Itemid)
	{
		$session->set('JT_Itemid', $Itemid);
	}

	$doc = Factory::getDocument();
	$doc->addStyleSheet(Uri::base() . 'modules/mod_recent_likes/css/likes.css');
	$limit = $params->get('no_of_likes');

	$jlikehelperobj = new comjlikeHelper;
	$recentlikes = $jlikehelperobj->GetRecentLikes($limit);
	$lang = Factory::getLanguage();
	$lang->load('mod_jlike_recent_likes', JPATH_SITE);
	$layout = ModuleHelper::getLayoutPath('mod_jlike_recent_likes');
	require_once $layout;
