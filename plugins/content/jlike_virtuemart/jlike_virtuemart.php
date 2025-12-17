<?php
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

$jlikeHelperPath = JPATH_SITE . '/components/com_jlike/helper.php';
if (file_exists($jlikeHelperPath)) {
	require_once $jlikeHelperPath;
}

class plgContentJLike_virtuemart extends CMSPlugin {

	function onContentAfterTitle( $context, &$article, &$params, $limitstart )
	{
		$app = Factory::getApplication();
		if (!$app->isClient('site')) {
			return;
		}

		$html='';
		$app = Factory::getApplication ();

		if ($app->scope != 'com_virtuemart') {
			return;
		}

		$route= URI::getInstance()->toString();
		$input=Factory::getApplication()->getInput();

		$view=$input->get('view','','STRING');

		$element	=	'';
		$element='com_virtuemart.productdetails';
			if($view!='category')
			{
				$virtuemart_product_id=$input->get('virtuemart_product_id','','INT');
				$virtuemart_category_id=$input->get('virtuemart_category_id','','INT');
			}
			else if($view=='category')
			{
				if(empty($article->virtuemart_product_id))
				return;
				$route=$article->link;
				$virtuemart_product_id=$article->virtuemart_product_id;

			}
			else
			return;
			$cont_id	=	$virtuemart_product_id;
			$show_like_buttons = 1;
			Factory::getApplication()->getInput()->set ( 'data', json_encode ( array ('cont_id' => $cont_id, 'plg_name' => 'jlike_virtuemart', 'element' => $element, 'plg_type' => 'content', 'title' => $article->slug, 'url' => $route,'show_like_buttons'=>$show_like_buttons ) ) );

		require_once(JPATH_SITE.'/'.'components/com_jlike/helper.php');
		$jlikehelperObj=new comjlikeHelper();
		$html = $jlikehelperObj->showlike();

		return $html;

   }


}
