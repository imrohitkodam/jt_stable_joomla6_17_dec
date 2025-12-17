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

class plgContentJLike_communitypolls extends CMSPlugin {

	/* For joomla 1.6 and above */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	// This is for category view
	function onBeforeDisplaylike($show_comments = -1, $show_like_buttons = 0)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		$html = '';
		$input = $app->input;
		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$layout=$input->get('layout','','STRING');
		$task=$input->get('layout','','STRING');
		$app = Factory::getApplication ();
		if ($app->scope != 'com_communitypolls' AND $task!='viewpoll') {
			return;
		}

		//com_communitypolls&view=polls&task=viewpoll&id=2
		$cont_id	=$input->get('id','','INT');

		$item_url='index.php?option=com_communitypolls&view=polls&id='.$cont_id;
		$element_id	=$cont_id;
		$element	='com_communitypolls.polls';
		$title	='';

		if($show_comments != -1)
		{
			$show_comments=1;
		}

		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $element_id, 'element' => $element, 'title' => $title, 'url' => $item_url,'plg_name'=>'jlike_communitypolls','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons  ) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		echo  $html = $jlikehelperObj->showlike();
   }
	function onAfterGetjlike_communitypollsOwnerDetails($cont_id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__jcp_polls'))
			->where($db->quoteName('id') . ' = ' . (int) $cont_id);
		$db->setQuery($query);
		return $db->loadResult();
	}
}
