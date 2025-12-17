<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_easydiscuss', JPATH_ADMINISTRATOR);

class plgContentJLike_easydiscuss extends CMSPlugin {

	function AfterEasydiscussTitle($element, $item_url, $item)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		$input=Factory::getApplication()->getInput();
		$view=$input->get('view','','STRING');

		//Not to show anything related to commenting
		$show_comments=-1;
		$show_like_buttons=1;

		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' =>$item->id, 'element' => $element, 'title' => $item->title, 'url' => $item_url,'plg_name'=>'jlike_easydiscuss','show_comments'=>$show_comments,'show_like_buttons'=>$show_like_buttons ) ) );
		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
    return $html;
  }
	function onContentAfterTitle($context, &$item, &$params, $limitstart)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site')) {
			return;
		}
		$html = '';

		if ($app->scope != 'com_easydiscuss') {
			return;
		}

		$item_url = 'index.php?option=com_easydiscuss&view=post&id='.$item->id;
		$element_id	=	$item->id;
		$element	=	$context;
		$title	=	$item->title;
		$show_like_buttons = 1;
		$show_comments = -1;


		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $element_id, 'element' => $element, 'title' => $title, 'url' => $item_url,'plg_name'=>'jlike_easydiscuss','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons ) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		echo $html = $jlikehelperObj->showlike();

   }


}
