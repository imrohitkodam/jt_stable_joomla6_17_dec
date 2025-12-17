<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

// Import library dependencies

// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_easyblog', JPATH_ADMINISTRATOR);

/**
 * Class supporting a list of Tjlms records.
 *
 * @since  1.0.0
 */
class PlgContentJLike_Easyblog extends CMSPlugin
{
	/**
	 * For joomla 1.6 and above
	 *
	 * @param   Object  $element  Subject
	 * @param   Object  $ebcat    Configuration of article
	 *
	 * @return  Content to display
	 */
	public function onContentBeforeDisplay($element, $ebcat)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site'))
		{
			return;
		}

		if ($app->scope != 'com_easyblog')
		{
			return;
		}

		$show_like_buttons = 1;
		$cat_url           = 'index.php?option=com_easyblog&view=categories&layout=listings&id=' . $ebcat->id;

		Factory::getApplication()->getInput()->set('data', json_encode(
		array(
			'cont_id' => $ebcat->id,
			'element' => $element,
			'title' => $ebcat->title,
			'url' => $cat_url,
			'plg_name' => 'jlike_easyblog',
			'plg_type' => 'content',
			'show_comments' => -1,
			'show_like_buttons' => $show_like_buttons
		)
		)
		);

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';

		$jlikehelperObj = new comjlikeHelper;
		$html           = $jlikehelperObj->showlike();

		return $html;
	}

	/**
	 * For joomla 1.6 and above
	 *
	 * @param   Object  $context     Subject
	 * @param   Object  &$item       Configuration of article
	 * @param   Object  &$params     Configuration of article
	 * @param   Object  $limitstart  Configuration of article
	 *
	 * @return  Content display
	 *
	 * @since 1.7
	 */
	public function onContentAfterDisplay($context, &$item, &$params, $limitstart = 0)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('site'))
		{
			return;
		}

		$html = '';

		if ($app->scope != 'com_easyblog')
		{
			return;
		}

		$item_url   = 'index.php?option=com_easyblog&view=entry&id=' . $item->id;
		$element_id = $item->id;
		$element    = $context;
		$title      = $item->title;
		$input = Factory::getApplication()->getInput();
		$view  = $input->get('view', '', 'STRING');

		// Not to show anything related to commenting
		$show_comments  = -1;
		$jlike_comments = $this->params->get('jlike_comments');

		if ($jlike_comments)
		{
			// It show comment count
			$show_comments = 0;

			if ($view == 'entry')
			{
				//  It show comments
				$show_comments = 1;
			}
		}

		$show_like_buttons = 0;

		Factory::getApplication()->getInput()->set('data', json_encode(
		array(
			'cont_id' => $element_id,
			'element' => $element,
			'title' => $title,
			'url' => $item_url,
			'plg_name' => 'jlike_easyblog',
			'plg_type' => 'content',
			'show_comments' => $show_comments,
			'show_like_buttons' => $show_like_buttons
		)
		)
		);

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';

		$jlikehelperObj = new comjlikeHelper;
		$html           = $jlikehelperObj->showlike();

		return $html;
	}

	/**
	 * For joomla 1.6 and above
	 *
	 * @param   int  $cont_id  Content id
	 *
	 * @return  owner id
	 *
	 * @since 1.7
	 */
	public function onAfterGetjlike_easyblogOwnerDetails($cont_id)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__easyblog_post'))
			->where($db->quoteName('id') . ' = ' . (int) $cont_id);
		$db->setQuery($query);

		return $db->loadResult();
	}
}
