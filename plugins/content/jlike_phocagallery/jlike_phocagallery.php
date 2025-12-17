<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

$jlikeHelperPath = JPATH_SITE . '/components/com_jlike/helper.php';
if (file_exists($jlikeHelperPath)) {
	require_once $jlikeHelperPath;
}

$lang = Factory::getLanguage();
$lang->load('plg_jlike_phocagallery', JPATH_ADMINISTRATOR);

class plgContentJLike_phocagallery extends CMSPlugin {

	function onBeforeDisplaylike($context, $article, $params, $limitstart)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}

		$html = '';

		if ($app->scope != 'com_phocagallery') {
			return;
		}

		$route = Uri::getInstance()->toString();
		$input=Factory::getApplication()->getInput();
		$catid=$input->get('cat_id','','INT');
		$cont_id	=	$input->get('id','','INT');

		$element	=	'';
		$input=Factory::getApplication()->getInput();
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$layout=$input->get('layout','','STRING');

		$show_like_buttons = 1;
		$show_comments=-1;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			if($view=='category')
			{
				//show comments
				$show_comments=1;
			}
		}

		if($option)
			$element	.=	$option;
		if($view)
			$element	.=	'.'.$view;
		if($layout)
			$element	.=	'.'.$layout;
			//print_r(array ('cont_id' => $cont_id, 'element' => $element, 'title' => $article->slug, 'url' => $route ));
		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $article->slug, 'url' => $route,'plg_name'=>'jlike_phocagallery','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
		return $html;
   }

}
