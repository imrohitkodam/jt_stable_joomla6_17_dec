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

// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_kunena', JPATH_ADMINISTRATOR);

class plgKunenajlike_kunena extends CMSPlugin {

	function onKunenaPrepare($context, $topic, $params, $vv)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		$html='';

		if ($app->scope != 'com_kunena' and $context!='kunena.topic') {
			return;
		}

		$route = Uri::getInstance()->toString();
		$input=Factory::getApplication()->getInput();

		$cont_id	=	$input->get('id','','INT');
		if($context=='kunena.topics')
		{
				$cont_id	=	$input->get('cat_id','','INT');
				return;
		}

		$view=$input->get('view','','STRING');
		//Not to show anything related to commenting
		$show_comments=-1;
		$show_like_buttons = 1;

		$jlike_comments = $this->params->get('jlike_comments');

		//~ if($jlike_comments)
		//~ {
			//~ //show comment count
			//~ $show_comments = 0;
//~
			//~ if($view=='topic')
			//~ {
				//~ //show comments
				//~ $show_comments=1;
			//~ }
		//~ }

		$data=$input->get('data','','STRING');
		$element	=$context;
		if(!empty($data)) //this is used since there are three triggers which will print like button three times onKunenaPrepare com_kunena on line no 94
		return;
		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $topic->subject, 'url' => $route,'plg_name'=>'jlike_kunena','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons ) ) );
		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
		echo $html;
   }

}
