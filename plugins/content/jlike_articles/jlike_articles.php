<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

// Import library dependencies

$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

if (!class_exists('ComjlikeHelper') && file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
{
	// Require_once $path;
	if (file_exists($helperPath)) {
		require_once $helperPath;
	}
}


// Load language file
$lang = Factory::getLanguage();
$lang->load('plg_jlike_articles', JPATH_ADMINISTRATOR);

/**
 * Class supporting a list of Tjlms records.
 *
 * @since  1.0.0
 */
class PlgContentJLike_Articles extends CMSPlugin
{
	public $params;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 *
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		$this->_params = ComponentHelper::getParams('com_jlike');

		parent::__construct($subject, $config);
	}

	/**
	 * If in the article view and the parameter is enabled shows the page navigation
	 *
	 * @param   string   $context   The context of the content being passed to the plugin
	 * @param   object   &$article  The article object
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed  void or true
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
	{
		$show_comments     = -1;
		$show_like_buttons = 1;
		$show_recommend    = 0;
		$showassignbtn     = 0;

		if ($this->_params->get('recommendation'))
		{
			$show_recommend = $this->params->get('recommendation');
		}

		if ($this->_params->get('assignment'))
		{
			$showassignbtn = $this->params->get('assignment');
		}

		$html = $this->SetValues($context, $article, $params, $page, $show_like_buttons, $show_comments, $show_recommend, $showassignbtn);

		return $html;
	}

	/**
	 * If in the article view and the parameter is enabled shows the page navigation
	 *
	 * @param   string   $context   The context of the content being passed to the plugin
	 * @param   object   &$article  The article object
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed  void or true
	 *
	 * @since   1.6
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $page = 0)
	{
		$input = Factory::getApplication()->getInput();

		// Not to show anything related to commenting
		$show_comments = -1;

		// Not to show like button
		$show_like_buttons = 0;
		$jlike_comments = $this->params->get('jlike_comments');

		if ($jlike_comments)
		{
			// Show comment count
			$show_comments = 0;
			$view = $input->get('view', '', 'STRING');

			if ($view == 'article')
			{
				// Show comments
				$show_comments = 1;
			}
		}

		$showassignbtn  = 0;
		$show_recommend = 0;

		$html = $this->SetValues($context, $article, $params, $page, $show_like_buttons, $show_comments, $show_recommend, $showassignbtn);

		return $html;
	}

	/**
	 * Function used to set Values
	 *
	 * @param   string   $context            Context
	 * @param   object   $article            Article Object
	 * @param   object   $params             Params
	 * @param   integer  $page               Page
	 * @param   integer  $show_like_buttons  show_like_buttons
	 * @param   integer  $show_comments      show_comments
	 * @param   integer  $show_recommend     show_recommend
	 * @param   integer  $showassignbtn      showassignbtn
	 *
	 * @return  string|boolean|void
	 *
	 * @since  1.0.0
	 */
	public function SetValues($context, $article, $params, $page, $show_like_buttons, $show_comments, $show_recommend, $showassignbtn)
	{
		$input = Factory::getApplication()->getInput();
		$app   = Factory::getApplication();

		$option = $input->get('option', '', 'STRING');
		$view   = $input->get('view', '', 'STRING');
		$layout = $input->get('layout', '', 'STRING');

		if ($app->getName() != 'site')
		{
			return;
		}

		/* if (($app->scope != 'com_content' AND $context != 'com_content.category')
		OR ($app->scope != 'com_content' AND $context != 'com_content.article'))
		{
			return;
		} */

		// Don't show likes button and comment box on article list view page
		if ($app->scope !== 'com_content' || $context !== 'com_content.article')
		{
			return;
		}

		$context = 'com_content.article';
		$url = ContentHelperRoute::getArticleRoute($article->id, $article->catid);
		$cont_id	=	$article->id;

		$jlike_categories = $this->params->get('jlike_categories');

		if ($jlike_categories && count($jlike_categories) >= 1)
		{
			if (!in_array($article->catid, $jlike_categories))
			{
				return false;
			}
		}

		Factory::getApplication()->getInput()->set('data',
			json_encode(
				array(
					'cont_id' => $cont_id,
					'element' => $context,
					'title' => $article->title,
					'url' => $url,
					'plg_name' => 'jlike_articles',
					'show_comments' => $show_comments,
					'show_like_buttons' => $show_like_buttons,
					'showrecommendbtn' => $show_recommend,
					'plg_type' => 'content',
					'showassignbtn' => $showassignbtn,
					'show_reviews' => 0
				)
			)
		);

		require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';
		$jlikehelperObj = new comjlikeHelper;

		return $jlikehelperObj->showlike();
	}

	/**
	 * Function used to get owner details
	 *
	 * @param   INT  $cont_id  Content id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function onAfterGetjlike_articlesOwnerDetails($cont_id)
	{
		$table = Table::getInstance("content");
		$table->load($cont_id);

		return $table->created_by;
	}

	/**
	 * getjlike_articlesLikesNotificationDetails
	 *
	 * @param   Array  $extraParams  Array of plug name, plug type, content Id etc.
	 *
	 * @param   Array  $data         Content Data.
	 *
	 * @return  array
	 */
	public function getjlike_articlesLikesNotificationDetails($extraParams, $data)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($data['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $data['title'] . '</a>';

		$owner_id = $this->onAfterGetjlike_articlesOwnerDetails($data['element_id']);

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.like.jlike_articles#' . $data['element_id'];
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * getjlike_articlesDislikesNotificationDetails
	 *
	 * @param   Array  $extraParams  Array of plug name, plug type, content Id etc.
	 *
	 * @param   Array  $data         Content Data.
	 *
	 * @return  array
	 */
	public function getjlike_articlesDislikesNotificationDetails($extraParams, $data)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($data['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $data['title'] . '</a>';

		$owner_id = $this->onAfterGetjlike_articlesOwnerDetails($data['element_id']);

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.dislike.jlike_articles#' . $data['element_id'];
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * getjlike_articlesCommentNotificationDetails
	 *
	 * @param   Array    $extraParams    Array of plug name, plug type, content Id etc.
	 *
	 * @param   integer  $annotation_id  Annotation Id.
	 *
	 * @param   Array    $commentData    Comment Data.
	 *
	 * @return  array
	 */
	public function getjlike_articlesCommentNotificationDetails($extraParams, $annotation_id, $commentData)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($extraParams['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $extraParams['title'] . '</a>';

		$owner_id = $this->onAfterGetjlike_articlesOwnerDetails($extraParams['cont_id']);

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.comment.jlike_articles#' . $extraParams['cont_id'];
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * getjlike_articlesCommentReplyNotificationDetails
	 *
	 * @param   Array    $extraParams  Array of plug name, plug type, content Id etc.
	 *
	 * @param   integer  $parent_id    Parent Comment Id.
	 *
	 * @param   integer  $owner_id     Comment owner Id.
	 *
	 * @return  array
	 */
	public function getjlike_articlesCommentReplyNotificationDetails($extraParams, $parent_id, $owner_id)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($extraParams['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $extraParams['title'] . '</a>';

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.commentreply.jlike_articles';
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * getjlike_articlesLikeOnCommentNotificationDetails
	 *
	 * @param   Array    $extraParams  Array of plug name, plug type, content Id etc.
	 *
	 * @param   integer  $parent_id    Parent Comment Id.
	 *
	 * @param   integer  $owner_id     Comment owner Id.
	 *
	 * @return  array
	 */
	public function getjlike_articlesLikeOnCommentNotificationDetails($extraParams, $parent_id, $owner_id)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($extraParams['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $extraParams['title'] . '</a>';

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.commentlike.jlike_articles';
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * getjlike_articlesDislikeOnCommentNotificationDetails
	 *
	 * @param   Array    $extraParams  Array of plug name, plug type, content Id etc.
	 *
	 * @param   integer  $parent_id    Parent Comment Id.
	 *
	 * @param   integer  $owner_id     Comment owner Id.
	 *
	 * @return  array
	 */
	public function getjlike_articlesDislikeOnCommentNotificationDetails($extraParams, $parent_id, $owner_id)
	{
		$returnData = array ();

		$link = Uri::root() . substr(Route::_($extraParams['url']), strlen(Uri::base(true)) + 1);
		$link = '<a href="' . $link . '">' . $extraParams['title'] . '</a>';

		$replacements                        = new stdClass;
		$replacements->notification->owner   = Factory::getUser($owner_id)->name;
		$replacements->notification->user    = Factory::getUser()->name;
		$replacements->notification->article = $link;

		$options = new Registry;

		$returnData['notifyClient']       = 'jlike';
		$returnData['notifyKey']          = 'jlike.commentdislike.jlike_articles';
		$returnData['replacementsObj']    = $replacements;
		$returnData['optionsRegistryObj'] = $options;

		return $returnData;
	}

	/**
	 * Function used to get social integration
	 *
	 * @return  $socialIntegration
	 *
	 * @since  1.0.0
	 */
	public function onAfterjlike_articlesGetSocialIntegration()
	{
		$integration = strtolower(ComponentHelper::getParams('com_jlike')->get('integration'));

		return $integration;
	}

	/**
	 * Function used to get article data
	 *
	 * @param   INT  $article_id  Id of article
	 *
	 * @return  $enroledUsers
	 *
	 * @since  1.0.0
	 */
	public function onAfterjlike_articlesGetElementData($article_id)
	{
		$data = array();

		$article = Table::getInstance("content");

		// Get Article
		$article->load($article_id);
		$data['title'] = $article->get("title");

		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

		$data['url'] = 'index.php?option=com_content&view=article&id=' . $article_id;

		if (!class_exists('comjlikeHelper') && file_exists(JPATH_SITE . '/components/com_jlike/helper.php'))
		{
			$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

			// Require_once $path;
			if (file_exists($helperPath)) {
				require_once $helperPath;
			}
		}

		if (class_exists('comjlikeHelper'))
		{
			$ComjlikeHelper = new ComjlikeHelper;
			$Itemid      = $ComjlikeHelper->getitemid($data['url']);
			$data['url'] = 'index.php?option=com_content&view=article&id=' . $article_id . '&Itemid=' . $Itemid;
		}

		return $data;
	}
}
