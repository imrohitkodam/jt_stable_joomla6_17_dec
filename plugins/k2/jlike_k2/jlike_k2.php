<?php
/**
 * @version		2.1
 * @package		Example K2 Plugin (K2 plugin)
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;

$jlikeHelperPath = JPATH_SITE . '/components/com_jlike/helper.php';
if (file_exists($jlikeHelperPath)) {
	require_once $jlikeHelperPath;
}

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Load the K2 Plugin API
if (file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php')) {
	require_once JPATH_ADMINISTRATOR.'/components/com_k2/lib/k2plugin.php';
}

//Load language file
$lang =  Factory::getLanguage();
$lang->load('plg_jlike_k2', JPATH_ADMINISTRATOR);

// Initiate class to hold plugin events
class plgK2Jlike_k2 extends K2Plugin
{

	// Some params
	var $pluginName = 'jlike_k2';
	var $pluginNameHumanReadable = 'Example jLike Plugin';

	function __construct(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**
	 * Below we list all available FRONTEND events, to trigger K2 plugins.
	 * Watch the different prefix "onK2" instead of just "on" as used in Joomla! already.
	 * Most functions are empty to showcase what is available to trigger and output. A few are used to actually output some code for example reasons.
	 */

	function onK2AfterDisplayContent( &$item, &$params, $limitstart) {


		$app = Factory::getApplication();
		$input = $app->input;

		if (!$app->isClient('site')) {
			return;
		}
		if ($app->scope != 'com_k2') {
			return;
		}

		$item_route = $item->link;

		$k2id=$item->id;
		$element='';
		$element	.='com_k2.item';

		//Not to show anything related to commenting
		$show_comments=-1;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			//show comments count on category view
			$show_comments=0;

			$view=$input->get('view','','STRING');
			if($view=='item')
			{
				//show comments
				$show_comments=1;
			}
		}

		$show_like_buttons = 1;
		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id'=>$item->id,'element' => $element, 'title' => $item->title, 'url' => $item_route,'plg_name'=>'jlike_k2','show_comments'=>$show_comments, 'show_like_buttons'=>$show_like_buttons ) ) );


		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
		return $html;
	}


	function onAfterGetjlike_k2OwnerDetails($cont_id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__k2_items'))
			->where($db->quoteName('id') . ' = ' . (int) $cont_id);
		$db->setQuery($query);
		return $db->loadResult();
	}

} // END CLASS

