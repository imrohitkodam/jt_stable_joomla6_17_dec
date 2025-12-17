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
$lang->load('plg_jlike_redshop', JPATH_ADMINISTRATOR);

class plgContentJLike_redshop extends CMSPlugin {

	/* For Red Shop */
	public function onAfterDisplayProduct(&$template_desc, $params, $data)
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}
		
		$html = '';
		if ($app->scope != 'com_redshop')
		{
			return;
		}

		$input = $app->input;

		$cont_id = $data->product_id;
		$view = $input->get('view', '', 'STRING');
		if ($view != 'category')
			$route = Uri::getInstance()->toString();
		else
		$route=$params['jlike_link'];

		$element	=	'';
		$element	.=	'com_redshop.product';

		//Not to show anything related to commenting
		$show_comments=-1;
		$jlike_comments = $this->params->get('jlike_comments');

		if($jlike_comments)
		{
			//show comments
			$show_comments=1;
		}

		$url	=	$route;
		$show_like_buttons = 1;
		Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'element' => $element, 'title' => $data->product_name, 'url' => $route,'plg_name'=>'jlike_redshop','show_comments'=>$show_comments,'show_like_buttons'=>$show_like_buttons ) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();
		if($view=='category')
		return $html;
		else
		echo $html;

	}

}
