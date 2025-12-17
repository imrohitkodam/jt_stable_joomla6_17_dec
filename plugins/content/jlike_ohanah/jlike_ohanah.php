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

// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_ohanah', JPATH_ADMINISTRATOR);

class plgContentJLike_Ohanah extends CMSPlugin {

	/* For joomla 1.6 and above */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	// This is for category view
	function onBeforeDisplaylike($show_comments = -1, $show_like_buttons = 0)
	{
		$app = Factory::getApplication();
		$input = $app->input;

		if (!$app->isClient('site')) {
			return;
		}

		if ($app->scope != 'com_ohanah') {
			return;
		}

		$html='';

		$option=$input->get('option','','STRING');
		$view=$input->get('view','','STRING');
		$layout=$input->get('layout','','STRING');


		$cont_id	=$input->get('id','','INT');

		$item_url='index.php?option=com_ohanah&view=event&id='.$cont_id;
		$element_id	=$cont_id;
		$element	='com_ohanah.event';
		$title	='';

		//$show_like_buttons = 1;
		//Not to show anything related to commenting

		if($show_comments != -1)
		{
			$show_comments=-1;
			$jlike_comments = $this->params->get('jlike_comments');

			if($jlike_comments)
			{
				//show comments
				$show_comments=1;
			}
		}

		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $element_id, 'element' => $element, 'title' => $title, 'url' => $item_url,'plg_name'=>'jlike_ohanah','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons) ) );
		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		echo  $html = $jlikehelperObj->showlike();
   }
	function onAfterGetjlike_ohanahOwnerDetails($cont_id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__ohanah_events'))
			->where($db->quoteName('ohanah_event_id') . ' = ' . (int) $cont_id);
		$db->setQuery($query);
		return $db->loadResult();
	}
}
