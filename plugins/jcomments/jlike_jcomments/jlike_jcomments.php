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

$jlikeHelperPath = JPATH_SITE . '/components/com_jlike/helper.php';
if (file_exists($jlikeHelperPath)) {
	require_once $jlikeHelperPath;
}

class plgJcommentsjlike_jcomments extends CMSPlugin {

	function onAfterdisplayJcomment($context, $addata)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		$html = '';

		$show_comments=-1;
		$show_like_buttons=1;

		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $addata['id'], 'element' => $context, 'title' => $addata['title'], 'url' => $addata['url'], 'plg_name'=>'jlike_jcomments', 'show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons ) ) );
		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		return $html = $jlikehelperObj->showlike();
   }

}
