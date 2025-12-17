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
$lang->load('plg_jlike_flexicontent', JPATH_ADMINISTRATOR);

class plgContentJLike_flexicontent extends CMSPlugin {

	/*for joomla 1.6 and above*/
	//This is for to show only like button
	function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
	{

		$show_comments=-1;
		$show_like_buttons=1;

		$html = $this->SetValues($context, $article, $params, $page, $show_like_buttons, $show_comments);
		return $html;

	}

   //This is for showing comment
	function onContentAfterDisplay($context, &$article, &$params, $page = 0)
	{
		$input=Factory::getApplication()->getInput();

		//Not to show anything related to commenting
		$show_comments=-1;

		//Not to show like button
		$show_like_buttons=0;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			//show comment count
			$show_comments=0;
			$view = $input->get('view','','STRING');

			if($view=='article')
			{
				//show comments
				$show_comments=1;
			}
		}


		$html = $this->SetValues($context, $article, $params, $page, $show_like_buttons, $show_comments);
		return $html;

	}


	function SetValues($context, $article, $params, $page = 0, $show_like_buttons, $show_comments)
	{

		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		$html = '';
		if ($app->scope != 'com_flexicontent') {
			return;
		}
		$sef = $app->get('sef');

		if ($sef == '1' )
		{
			$url = ContentHelperRoute::getArticleRoute($article->slug);
		}
		else
		{
			$url = ContentHelperRoute::getArticleRoute( $article->id, $article->catid);
		}

		$cont_id	=	$article->id;

		$element	=	'';
		$input=Factory::getApplication()->getInput();
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$layout=$input->get('layout','','STRING');
		if($option)
			$element	.=	$option;
		if($view)
			$element	.=	'.'.$view;
		if($layout)
			$element	.=	'.'.$layout;

		$title	=	$article->title;

		Factory::getApplication()->getInput()->set('data', json_encode ( array ('cont_id' => $cont_id, 'element' => $context, 'title' => $article->title, 'url' => $url,'plg_name'=>'jlike_flexicontent','show_comments'=>$show_comments,'show_like_buttons'=>$show_like_buttons)));

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		return $html = $jlikehelperObj->showlike();

	}


	function onAfterGetjlike_flexicontentOwnerDetails($cont_id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__content'))
			->where($db->quoteName('id') . ' = ' . (int) $cont_id);
		$db->setQuery($query);
		return $db->loadResult();
	}

}
