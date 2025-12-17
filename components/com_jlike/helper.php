<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

require_once JPATH_SITE . '/components/com_jlike/helpers/subhelper.php';

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/socialintegration.php';

if (!class_exists('socialintegrationHelper'))
{
	//  Require_once $path;
	JLoader::register('socialintegrationHelper', $helperPath);
	JLoader::load('socialintegrationHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/integration.php';

if (!class_exists('comjlikeIntegrationHelper'))
{
	//  Require_once $path;
	JLoader::register('comjlikeIntegrationHelper', $helperPath);
	JLoader::load('comjlikeIntegrationHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

if (!class_exists('comjlikeHelper'))
{
	//  Require_once $path;
	JLoader::register('comjlikeHelper', $helperPath);
	JLoader::load('comjlikeHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

if (!class_exists('ComjlikeMainHelper'))
{
	// Require_once $path;
	JLoader::register('ComjlikeMainHelper', $helperPath);
	JLoader::load('ComjlikeMainHelper');
}

if (!class_exists('comjlikeHelper'))
{
	/**
	 * Get button set used
	 *
	 * @since  2.2
	 */
	class ComjlikeHelper extends ComjlikeSubHelper
				{
		protected $jlikemainhelperObj;
		/**
		 * Get button set used
		 *
		 * @since   2.2
		 *
		 * @return  Array
		 */
		public function getbttonset()
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jlike'));
			$query->where($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			$res = $db->loadObject();

			return $res;
		}

		/**
		 * Called from plugin to show like / Dilike buttons
		 *
		 * @since   2.2
		 *
		 * @return  string
		 */
		public function showlike()
		{
			$mainframe     = Factory::getApplication();
			$componentPath = JPATH_SITE . '/components/com_jlike';
			require_once $componentPath . '/models/jlike_likes.php';
			require_once $componentPath . '/controller.php';
			$component = new jLikeController(array('name' => 'jlike'));
			$component->addViewPath($componentPath . '/views');
			$component->addModelPath($componentPath . '/models');
			$view  = $component->getView('jlike', 'raw');
			$model = $component->getModel('jlike_likes');
			$view->setModel($model);
			$view->setModel($model);
			$templatePath = JPATH_SITE . '/templates/' . $mainframe->getTemplate() . '/html/com_jlike/jlike';

			if (File::exists($templatePath . '/default.php'))
			{
				$view->addTemplatePath($templatePath);
			}
			else
			{
				$view->addTemplatePath($componentPath . '/views/jlike/tmpl');
			}

			ob_start();

			$view->display();

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Called from plugin to show like / Dilike buttons
		 *
		 * @since   2.2
		 *
		 * @return  string
		 */
		public function showlikebuttons()
		{
			$mainframe     = Factory::getApplication();
			$user          = Factory::getUser();
			$componentPath = JPATH_SITE . '/components/com_jlike';
			require_once $componentPath . '/models/jlike_likes.php';
			require_once $componentPath . '/controller.php';
			$component = new jLikeController(array('name' => 'jlike'));
			$component->addViewPath($componentPath . '/views');
			$component->addModelPath($componentPath . '/models');
			$view  = $component->getView('jlike', 'raw');
			$model = $component->getModel('jlike_likes');
			$view->setModel($model);

			$templatePath = JPATH_SITE . '/templates/' . $mainframe->getTemplate() . '/html/com_jlike/jlike';

			// If (JFile::exists($templatePath . '/likebuttons.php'))
			{
				$view->addTemplatePath($templatePath);
			}
			// Else
			{
				$view->addTemplatePath($componentPath . '/views/jlike/tmpl');
			}

			ob_start();

			$view->setLayout('likebuttons');

			$view->display();

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Get like or dislike count for the given content
		 *
		 * @param   INT  $content_id  id of the content
		 *
		 * @return  object list
		 */
		public function getLikeDislikeCount($content_id)
		{
			$db             = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array('SUM(jl.like) as likecnt','SUM(jl.dislike) as dislikecnt'));
			$query->from($db->quoteName('#__jlike_likes', 'jl'));
			$query->where($db->quoteName('jl.content_id') . ' = ' . $db->quote($content_id));

			$query->group(array('jl.content_id'));

			$db->setQuery($query);
			$count = $db->loadObject();

			return $count;
		}

		/**
		 * Get user details
		 *
		 * @param   string  $obj      user object
		 * @param   Array   $plgData  Plugin info array
		 *
		 * @return  Array
		 */
		public function getUserDetails($obj, $plgData)
		{
			$jlikemainhelperObj = new ComjlikeMainHelper;
			$mainHelper  = $jlikemainhelperObj;
			$socialintegrationHelper  = $mainHelper->getSocialLibraryObject('', $plgData);
			$user = array();
			$user['img_url']          = $socialintegrationHelper->getAvatar($obj);
			$user['link_url']         = $socialintegrationHelper->getProfileUrl($obj);

			return $user;
		}

		/**
		 * Get ItemId function
		 *
		 * @param   string   $link          URL to find itemid for
		 *
		 * @param   integer  $skipIfNoMenu  return 0 if no menu is found
		 *
		 * @return  Integer
		 */
		public function getitemid($link, $skipIfNoMenu = 0)
		{
			$itemid    = 0;
			$mainframe = Factory::getApplication();

			if ($mainframe->isClient("site"))
			{
				$menu  = $mainframe->getMenu();
				$items = $menu->getItems('link', $link);

				if (isset($items[0]))
				{
					$itemid = $items[0]->id;
				}
			}

			if (!$itemid)
			{
				$db = Factory::getDbo();

				$query = $db->getQuery(true)->select('id')->from('#__menu')->where("'link LIKE %" . $link . "%'")->where("published =1")->setlimit("1");

				if ($query)
				{
					$db->setQuery($query);
					$itemid = $db->loadResult();
				}
			}

			if (!$itemid)
			{
				if ($skipIfNoMenu)
				{
					$itemid = 0;
				}
				else
				{
					$jinput = Factory::getApplication()->getInput();
					$itemid = $jinput->getInt('Itemid', 0);

					// $itemid = JFactory::getApplication()->getInput()->get('Itemid', 0);
				}
			}

			return $itemid;
		}

		/**
		 * addlabels
		 *
		 * @param   String  $data  (string) name of component
		 *
		 * @return  Integer
		 *
		 * @since 1.0
		 */
		public function addlables($data)
		{
			$db                  = Factory::getDbo();
			$insert_obj          = new stdClass;
			$insert_obj->user_id = Factory::getUser()->id;
			$insert_obj->title   = $db->escape($data['label']);
			$insert_obj->privacy = '1';
			$db->insertObject('#__jlike_like_lists', $insert_obj);
			$list_id = $db->insertid();

			PluginHelper::importPlugin('system', 'jlike_api');
			Factory::getApplication()->triggerEvent('onAfteraddlable', array($list_id));

			return $list_id;
		}

		/**
		 * manageContent
		 *
		 * @param   String  $data  data provided to manage content
		 *
		 * @return  Integer|String|Array
		 *
		 * @since 1.0
		 */
		public function manageContent($data)
		{
			$db	= Factory::getDbo();

			$content_id = '';

			if (!empty($data['element_id']) && !empty($data['element']))
			{
				$content_id = $this->getContentId($data['element_id'], $data['element']);
			}

			if (!$content_id)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = $data['element_id'];
				$insert_obj->element    = $data['element'];
				$insert_obj->url        = $data['url'];
				$insert_obj->title      = $data['title'];
				$db->insertObject('#__jlike_content', $insert_obj);
				$content_id = $db->insertid();
			}

			return $content_id;
		}

		/**
		 * multi_d_sort
		 *
		 * @param   Array   $array   (string) name of component
		 * @param   String  $column  (string) name of component
		 * @param   String  $order   (string) name of component
		 *
		 * @return  Array
		 *
		 * @since 1.0
		 */
		public function multi_d_sort($array, $column, $order)
		{
			foreach ($array as $key => $row)
			{
				// $orderby[$key]=$row['campaign']->$column;
				$orderby[$key] = $row->$column;
			}

			if ($order == 'asc')
			{
				array_multisort($orderby, SORT_ASC, $array);
			}
			else
			{
				array_multisort($orderby, SORT_DESC, $array);
			}

			return $array;
		}

		/**
		 * Check if GetMostLikes
		 *
		 * @param   String  $limit  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function GetRecentLikes($limit)
		{
			$db = Factory::getDbo();

			// Create a new query object.
			$query = $db->getQuery(true);

			if (!$limit)
			{
				$limit = 5;
			}

			$query->select('likecontent.*');
			$query->select($db->qn('likes.date', 'likedate'));
			$query->from($db->qn('#__jlike_content', 'likecontent'));
			$query->join('LEFT', $db->qn('#__jlike_likes', 'likes') . ' ON (' . $db->qn('likecontent.id') . ' = ' . $db->qn('likes.content_id') . ')');
			$query->group($db->quoteName('likecontent.id'));
			$query->order($db->qn('likes.date') . ' DESC');
			$query->setLimit($limit);
			$db->setQuery($query);

			return $db->loadObjectList();
		}

		/**
		 * Check if GetMostLikes
		 *
		 * @param   String  $limit  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function GetMostLikes($limit)
		{
			$db = Factory::getDbo();

			// Create a new query object.
			$query = $db->getQuery(true);

			if (!$limit)
			{
				$limit = 5;
			}

			$query->select('likecontent.*');
			$query->from($db->quoteName('#__jlike_content', 'likecontent'));
			$query->order($db->quoteName('likecontent.like_cnt') . ' DESC ');
			$query->setLimit($limit);
			$db->setQuery($query);

			return $db->loadObjectList();
		}

		/**
		 * Check if installed
		 *
		 * @param   String  $component  (string) name of component
		 *
		 * @return  Integer
		 *
		 * @since 1.0
		 */
		public function Checkifinstalled($component)
		{
			$componentpath = JPATH_ROOT . '/components/' . $component;

			if (Folder::exists($componentpath))
			{
				return 1;
			}
		}

		/**
		 * Notification to content owner after comment added
		 *
		 * @param   String  $comment   (string) name of view
		 * @param   String  $cnt_id    (string) name of view
		 * @param   String  $element   (string) name of view
		 * @param   String  $url       (string) name of view
		 * @param   String  $title     (string) name of view
		 * @param   String  $plg_name  (string) name of view
		 * @param   String  $plg_type  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function activityStream($comment, $cnt_id, $element, $url, $title, $plg_name, $plg_type)
		{
			// Activity Stream Integration
			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

			/*$params                        = ComponentHelper::getParams('com_jlike');*/
			$allow_activity_stream_comment = $params->get('allow_activity_stream_comment');

			if ($allow_activity_stream_comment == 1)
			{
				$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;
				$res                       = new StdClass;
				$res->comment              = $comment;
				$res->userid               = Factory::getUser()->id;
				$res->element              = '';
				$res->url                  = $url;
				$res->title                = $title;
				$res->access               = 0;

				$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
				$comjlikeIntegrationHelper->pushtoactivitystream($res, 'comment', 1, $integration);
			}
		}

		/**
		 * Method identify that a current user like or dislike the comment
		 *
		 * @param   String  $annotationid  (string) name of view
		 * @param   String  $userId        (string) name of view
		 *
		 * @return  Integer
		 *
		 * @since 1.0
		 */
		public function getUserCurrentLikeDislike($annotationid, $userId)
		{
			$db    = Factory::getDbo();

			// Create a new query object.
			$query = $db->getQuery(true);
			$userLikeDislike = 0;

			// Check that the current user like or dislike this comment
			$query->select($db->qn(array('like','dislike')));
			$query->from($db->quoteName('#__jlike_likes'));
			$query->where($db->qn('annotation_id') . ' = ' . $db->q((int) $annotationid));
			$query->where($db->qn('userid') . ' = ' . $db->q((int) $userId));

			$db->setQuery($query);
			$data = $db->loadObject();

			if (!empty($data))
			{
				if ($data->like)
				{
					// User like on comment
					$userLikeDislike = 1;
				}
				elseif ($data->dislike)
				{
					// User dislike on comment
					$userLikeDislike = 2;
				}
			}
			else
			{
				// User not like dislike
				$userLikeDislike = 0;
			}

			return $userLikeDislike;
		}

		/**
		 * method to get the user name, profile url & avtar who like or dislike the comment
		 *
		 * @param   String  $annotationid     (string) name of view
		 * @param   String  $likedOrdisliked  (string) name of view
		 *
		 * @return  Array
		 *
		 * @since 1.0
		 */
		public function getUserByCommentId($annotationid, $likedOrdisliked)
		{
			$db	= Factory::getDbo();
			$query = $db->getQuery(true);

			if ($likedOrdisliked)
			{
				$query->where($db->qn('likes.like') . ' = ' . $db->q(1));
			}
			else
			{
				$query->where($db->qn('likes.dislike') . ' = ' . $db->q(1));
			}

			$query->select($db->qn(array('u.name','u.id','u.email')));
			$query->from($db->quoteName('#__jlike_likes', 'likes'));
			$query->join('INNER', $db->qn('#__users', 'u') . ' ON (' . $db->qn('u.id') . ' = ' . $db->qn('likes.userid') . ')');
			$query->where($db->qn('likes.annotation_id') . ' = ' . $db->q((int) $annotationid));

			$db->setQuery($query);
			$result = $db->LoadObjectList();

			$socialintegrationHelper = new socialintegrationHelper;

			foreach ($result as $row)
			{
				$user                  = new stdClass;
				$user->id              = $row->id;
				$user->email           = $row->email;

				// Hide user's personal info #110026
				// $row->user_profile_url = $socialintegrationHelper->getUserProfileUrl($row->id);
				$row->avtar            = $socialintegrationHelper->getUserAvatar($user);

				// Hide user's personal info #110026
				unset($row->id);
				unset($row->email);
			}

			return $result;
		}

		/**
		 * checks for view override
		 *
		 * @param   String  $message_body  (string) name of view
		 *
		 * @return  String
		 *
		 * @since 1.0
		 */
		public function getProfiletag($message_body)
		{
			if (strpos($message_body, '<a') !== false)
			{
				preg_match_all("/<a\s(.+?)>(.+?)<\/a>/is", $message_body, $matches);
				$all_a_tags = $matches[0];

				$hrefpattern = "/(?<=profiletag=(\"|'))[^\"']+(?=(\"|'))/";
				preg_match_all($hrefpattern, $message_body, $matches);
				$profile_tags = $matches[0];

				foreach ($all_a_tags as $all_a_tag)
				{
					foreach ($profile_tags as $profile_tag)
					{
						if (strpos($all_a_tag, $profile_tag))
						{
							$message_body = str_replace($all_a_tag, $profile_tag, $message_body);
						}
					}
				}
			}

			return $message_body;
		}

		/**
		 * checks for view override
		 *
		 * @param   String  $viewname       (string) name of view
		 * @param   string  $layout         layout name eg order
		 * @param   string  $searchTmpPath  it may be admin or site. it is side(admin/site) where to search override view
		 * @param   string  $useViewpath    it may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
		 *
		 * @return  String
		 *
		 * @since 1.0
		 */
		public function getjLikeViewpath($viewname, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
		{
			$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$app           = Factory::getApplication();

			if (!empty($layout))
			{
				$layoutname = $layout . '.php';
			}
			else
			{
				$layoutname = "default.php";
			}

			$override = $searchTmpPath . '/templates/' . $app->getTemplate() . '/html/com_jlike/' . $viewname . '/' . $layoutname;

			if (File::exists($override))
			{
				return $override;
			}
			else
			{
				return $useViewpath . '/components/com_jlike/views/' . $viewname . '/tmpl/' . $layoutname;
			}
		}

		/**
		 * Get Content Classification
		 *
		 * @param   object  $objectList  list array
		 *
		 * @return  object list with replace element value
		 *
		 * @since 1.0
		 */
		public function classificationsValue($objectList)
		{
			if ($objectList)
			{
				$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
				$classifications = parse_ini_file($brodfile);

				foreach ($objectList as $row)
				{
					$element = trim($row->element);

					if ($element)
					{
						if (array_key_exists($element, $classifications))
						{
							$row->element = $classifications[$element];
						}
						else
						{
							$row->element = $element;
						}
					}
				}
			}

			return $objectList;
		}

		/**
		 * Function to Get Formated data for Chart (morris)
		 *
		 * @param   Array  $data  array of data.
		 *
		 * @return Array
		 *
		 * @since 3.0
		 */
		public function getLineChartFormattedData($data)
		{
			/* $session = Factory::getSession();
			// $backdate=$session->get('q2c_from_date');
			// $backdate = !empty($backdate)?$backdate:(date('Y-m-d', strtotime(date('Y-m-d').' - 30 days')));*/

			$todate = $session->get('q2c_end_date');
			$todate = !empty($todate) ? $todate : date('Y-m-d');

			$incomedata = "[
		";
			$ordersData = "[
		";
			$firstdate  = $backdate;

			//  Will be
			$keydate    = "";

			foreach ($data as $key => $income)
			{
				$keydate = date('Y-m-d', strtotime($key));

				if ($firstdate < $keydate)
				{
					while ($firstdate < $keydate)
					{
						$incomedata .= " { period:'" . $firstdate . "', amount:0 },
					";
						$ordersData .= " { period:'" . $firstdate . "', orders:0 },
					";
						$firstdate = $this->add_date($firstdate, 1);
					}
				}

				$incomedata .= " { period:'" . $income->cdate . "', amount:" . $income->amount . "},
			";
				$ordersData .= " { period:'" . $income->cdate . "', orders:" . $income->orders_count . "},
			";
				$firstdate = $keydate;
			}

			// Vm: remaing date to last date
			while ($keydate < $todate)
			{
				$keydate = $this->add_date($keydate, 1);
				$incomedata .= " { period:'" . $keydate . "', amount:0 },
			";
				$ordersData .= " { period:'" . $keydate . "', orders:0 },
			";
			}

			$incomedata .= '
		]';
			$ordersData .= '
		]';

			$returnArray    = array();
			$returnArray[0] = $incomedata;
			$returnArray[1] = $ordersData;

			return $returnArray;
		}

		/**
		 * Getting users lable list.
		 *
		 * @param   Integer  $user_id  user_id.
		 *
		 * @return array
		 *
		 * @since 3.0
		 */
		public function getLableList($user_id = '')
		{
			$db = Factory::getDbo();

			if ($user_id)
			{
				try
				{
					$query = $db->getQuery(true)->select('list.id,list.title')->from('#__jlike_like_lists AS list');

					if ($user_id)
					{
						$query->where('list.user_id=' . $user_id);
					}

					$db->setQuery($query);

					return $db->loadObjectList();
				}
				catch (Exception $e)
				{
					//  $e->getMessage();
					return array();
				}
			}
		}

		/**
		 * Getting users lable list.
		 *
		 * @param   Integer  $user_id     user_id.
		 * @param   Integer  $content_id  user_id.
		 * @param   ARRAY    $lableLists  new lable list array eg (0=>15,1=18).
		 *
		 * @return Boolean
		 *
		 * @since 3.0
		 */
		public function mapLikeWithLable($user_id, $content_id, $lableLists)
		{
			$db = Factory::getDbo();

			try
			{
				$previousMapping = $this->getMyContentLables($content_id, $user_id, 1);

				if (!empty($previousMapping) && is_array($previousMapping))
				{
					foreach ($previousMapping as $ind => $listID)
					{
						//  Chcek old lable exist in current lable list array
						$key = array_search($listID->id, $lableLists);

						if ($key)
						{
							//  Then remove from new list array.
							unset($lableLists[$key]);
						}
						else
						{
							//  For removed lable
							$query = $db->getQuery(true)->delete('#__jlike_likes_lists_xref')->where('content_id =' . $content_id)->where('list_id =' . $listID->id);
							$db->setQuery($query);

							if (!$db->execute())
							{
								// $this->setError($db->getErrorMsg());
								return false;
							}
						}
					}
				}

				//  Only add the newly checked lables
				if (!empty($lableLists))
				{
					foreach ($lableLists as $listId)
					{
						$obj             = new stdClass;
						$obj->content_id = $content_id;
						$obj->list_id    = $listId;
						$db->insertObject('#__jlike_likes_lists_xref', $obj);
					}
				}
			}
			catch (Exception $e)
			{
				//  $e->getMessage();

				return false;
			}

			return true;
		}

		/**
		 * Using this function
		 *
		 * @param   Integer  $importEmailId  user_id.
		 *
		 * @return Array
		 *
		 * @since 3.0
		 */
		public function getJlikeDetailFromInvitexRefTb($importEmailId)
		{
			$db = Factory::getDbo();

			try
			{
				$query = $db->getQuery(true);
				$query->select('content.*');
				$query->from($db->qn('#__jlike_content_inviteX_xref', 'invRef'));
				$query->join('INNER', $db->qn('#__jlike_content', 'content') . ' ON (' . $db->qn('invRef.content_id') . ' = ' . $db->qn('content.id') . ')');
				$query->where($db->qn('invRef.importEmailId') . ' = ' . $db->qn($importEmailId));

				$db->setQuery($query);
				$lists = $db->loadObjectList();

				if (!empty($lists))
				{
					return $lists;
				}
			}
			catch (Exception $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $importEmail_id  Import if of invitex's import table.
		 *
		 * @return  Array
		 *
		 * @since   1.3
		 */
		public function addEntryJlikeInvitexRefTb($importEmail_id)
		{
			$db            = Factory::getDbo();
			$jinput        = Factory::getApplication()->getInput();
			$res = array();
			$res['status'] = 0;
			$res['msg']    = Text::_('JLIKE_MISSING_CONTENT_IDS');

			/*if (empty($cids))
			{
			$cid = $post->get('cid', array(), 'ARRAY');
			$cids = implode(',', $cid);
			}*/

			//   Get contant ids from session
			$session = Factory::getSession();
			$cids    = $session->get('jlikeContentIds');

			if (empty($cids))
			{
				return $res;
			}

			$cidArray = explode(',', $cids);

			try
			{
				foreach ($cidArray as $content_id)
				{
					$obj                = new stdClass;
					$obj->content_id    = $content_id;
					$obj->importEmailId = $importEmail_id;

					if (!$db->insertObject('#__jlike_content_inviteX_xref', $obj, 'id'))
					{
						// $this->setError($db->getErrorMsg());
						$res['msg'] = $db->getErrorMsg();
					}
				}

				$res['status'] = 1;

				return $res;
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $importEmail_id  Import if of invitex's import table.
		 *
		 * @return  Integer
		 *
		 * @since   1.3
		 */
		public function DelEntryJlikeInvitexRefTb($importEmail_id)
		{
			$db = Factory::getDbo();

			if (empty($importEmail_id))
			{
				return 0;
			}

			try
			{
				$query = $db->getQuery(true)->delete('#__jlike_content_inviteX_xref')->where('importEmailId =' . $importEmail_id);

				$db->setQuery($query);
				$db->execute();

				return 1;
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $userId  User ID
		 * @param   integer  $limit   Import if of invitex's import table.
		 *
		 * @return  Array
		 *
		 * @since   1.3
		 */
		public function getUserLikeDetail($userId, $limit = '')
		{
			if (empty($userId))
			{
				return array();
			}

			try
			{
				//  Build query as you want
				$db   = Factory::getDbo();
				$user = Factory::getUser();
				$query = $db->getQuery(true)
					->select('likecontent.*')
					->select($db->qn('likes.date', 'likedate'))
					->from($db->qn('#__jlike_content', 'likecontent'))
					->join('INNER', $db->qn('#__jlike_likes', 'likes') . ' ON (' . $db->qn('likecontent.id') . ' = ' . $db->qn('likes.content_id') . ')')
					->group($db->qn('likecontent.id'))
					->where($db->qn('likes.userid') . ' = ' . $db->q($userId))
					->where($db->qn('likes.like') . ' = 1');

				if (!empty($limit))
				{
					$query->order($db->qn('likes.date') . ' DESC');
					$query->setLimit($limit);
				}

				$db->setQuery($query);

				return $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Get All statuses
		 *
		 * @since   2.2
		 * @return  Array
		 */
		public function getAllStatus()
		{
			/*if (empty($likeCont_id))
			{
				return array();
			}*/

			//  Check get status id for content
			try
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->qn(array('s.id','s.status_code')));
				$query->from($db->qn('#__jlike_statuses', 's'));
				$db->setQuery($query);

				return $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * This return weight unite symbol.
		 *
		 * @param   integer  $likeCont_id  Jlike Conten id.
		 *
		 * @since   2.2
		 * @return  Array|Integer
		 */
		public function getUsersContStatus($likeCont_id)
		{
			$users = Factory::getUser();
			$db = Factory::getDbo();

			if (empty($likeCont_id) || empty($users->id))
			{
				return 0;
			}

			//  Check get status id for content
			try
			{
				$query = $db->getQuery(true);
				$query->select($db->qn('s.status_id'));
				$query->from($db->qn('#__jlike_likeStatusXref', 's'));
				$query->where($db->qn('content_id') . ' = ' . $db->q($likeCont_id));
				$query->where($db->qn('user_id') . ' = ' . $db->q($users->id));
				$db->setQuery($query);

				return $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Get Content id
		 *
		 * @param   integer  $element_id  import_id.
		 * @param   string   $element     Element .
		 *
		 * @since   2.2
		 * @return  Array
		 */
		public function getContentId($element_id, $element)
		{
			if ($element_id &&  $element)
			{
				try
				{
					$db = Factory::getDbo();
					$query = $db->getQuery(true);
					$query->select($db->qn('c.id'));
					$query->from($db->qn('#__jlike_content', 'c'));
					$query->where($db->qn('c.element_id') . ' = ' . $db->q($element_id));
					$query->where($db->qn('c.element') . ' = ' . $db->q($element));
					$db->setQuery($query);

					return $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					// $this->setError($e->getMessage());
					throw new Exception($db->getErrorMsg());
				}
			}
		}

		/**
		 * Get Content id
		 *
		 * @param   string  $path       path of file to load.
		 * @param   string  $classname  class name to load .
		 *
		 * @since   2.2
		 * @return  Object
		 */
		public function loadJlClass($path, $classname)
		{
			if (!class_exists($classname))
			{
				JLoader::register($classname, $path);
				JLoader::load($classname);
			}

			return new $classname;
		}

		/**
		 * On like : Store extra data. Currently status related things are stored.
		 *
		 * @param   integer  $content_id     import_id.
		 * @param   object   $like_statusId  postdata .
		 *
		 * @since   2.2
		 * @return  Integer
		 */
		public function storeExtraData($content_id, $like_statusId)
		{
			$db = Factory::getDbo();
			$user = Factory::getUser();
			$userId = $user->id;

			if (!isset($like_statusId))
			{
				return 0;
			}

			try
			{
				// Check whethere rec already exist or not
				$query = $db->getQuery(true)
							->select($db->qn('sref.id'))
							->from($db->qn('#__jlike_likeStatusXref', 'sref'));
				$query->where($db->qn('sref.content_id') . ' = ' . $db->q($content_id));
				$query->where($db->qn('sref.user_id') . ' = ' . $db->q($userId));
				$db->setQuery($query);
				$refId = $db->loadResult();
				$action = 'insertObject';

				$row = new stdClass;

				if (!empty($refId))
				{
					$action = 'updateObject';
					$row->id = $refId;
					$row->cdate = date("Y-m-d H:i:s");
					$row->mdate = date("Y-m-d H:i:s");
				}
				else
				{
					$row->mdate = date("Y-m-d H:i:s");
				}

				$row->content_id = $content_id;
				$row->user_id = $userId;
				$row->status_id = $like_statusId;

				if (!$db->$action('#__jlike_likeStatusXref', $row, 'id'))
				{
					// $this->setError($db->getErrorMsg());

					return 0;
				}

				return 1;
			}
			catch (RuntimeException $e)
			{
				// $this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Check whether user liked to content or not.
		 *
		 * @param   integer  $content_id  import_id.
		 * @param   integer  $user_id     user id .
		 *
		 * @since   2.2
		 * @return  Array
		 */
		public function isUserLikedContent($content_id, $user_id)
		{
			if ($content_id &&  $user_id)
			{
				try
				{
					$db = Factory::getDbo();
					$query = $db->getQuery(true);
					$query->select($db->qn('c.id'));
					$query->from($db->qn('#__jlike_likes', 'c'));
					$query->where($db->qn('c.content_id') . ' = ' . $db->q($content_id));
					$query->order($db->qn('c.userid') . ' = ' . $db->qn($user_id));
					$query->where($db->qn('c.like') . ' = 1');
					$db->setQuery($query);

					return $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					// $this->setError($e->getMessage());
					throw new Exception($db->getErrorMsg());
				}
			}
		}

		/**
		 * Function to send recommendation
		 *
		 * @param   ARRAY  $data      data
		 * @param   ARRAY  $formdata  formdata
		 *
		 * @return  boolean
		 *
		 * @since  1.0.0
		 */
		public function send_recommendation($data, $formdata)
		{
			require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';

			$db	= Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->qn('jc.id'));
			$query->from($db->qn('#__jlike_content', 'jc'));
			$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $formdata['element_id']));
			$query->where($db->qn('jc.element') . ' = ' . $db->q($formdata['element']));
			$db->setQuery($query);
			$content_id = $db->loadResult();

			if (!$content_id)
			{
					$insert_obj = new stdClass;
					$insert_obj->element_id = $formdata['element_id'];
					$insert_obj->element 	= $formdata['element'];
					$insert_obj->url = $formdata['url'];
					$insert_obj->title	=	$formdata['title'];
					$db->insertObject('#__jlike_content', $insert_obj);
					$content_id = $db->insertid();
			}

			$insert_obj = new stdClass;
			$insert_obj->content_id = $content_id;
			$insert_obj->recommend_by = Factory::getUser()->id;
			$insert_obj->params = '';

			foreach ($data as $eachrecommendation)
			{
				$insert_obj->id = '';
				$insert_obj->recommend_to 	= $eachrecommendation;

				try
				{
					// If it fails, it will throw a RuntimeException
					$db->insertObject('#__jlike_recommend', $insert_obj, 'id');
					PluginHelper::importPlugin('system');
					Factory::getApplication()->triggerEvent('onAfterRecommend', array(
															$eachrecommendation,
															Factory::getUser()->id,
															$formdata['element_id'])
															);
				}
				catch (RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage());

					return false;
				}
			}

			return true;
		}

		/**
		 * Declare language constants to use in .js file
		 *
		 * @params  void
		 *
		 * @return  void
		 *
		 * @since   1.7
		 */
		public static function getLanguageConstant()
		{
			Text::script('COM_JLIKE_SELECT_USER_TO_RECOMMEND');
			Text::script('COM_JLIKE_SELECT_FILL_DATES');
			Text::script('COM_JLIKE_START_GT_THAN_DUE_DATE');
			Text::script('COM_JLIKE_INVALID_DATE_FORMAT');
			Text::script('COM_JLIKE_START_GT_THAN_TODAY');
			Text::script('COM_JLIKE_START_DATE_GT_THAN_TODAY');
			Text::script('COM_JLIKE_FORM_REMINDER_DAYS_ZERO');
			Text::script('COM_JLIKE_FORM_REMINDER_NOTVALID_CC');
			Text::script('COM_JLIKE_FORM_REMINDER_CONTENTTYPE_EMPTY');
			Text::script('COM_JLIKE_SAVE_SUCCESS_MSG');
			Text::script('COM_JLIKE_SAVE_NOTE_ERROR_MSG');
			Text::script('COM_JLIKE_RATING_INVALID_FIELD_RATING');
		}

		/**
		 * getAvarageRating.
		 *
		 * @since   2.2
		 * @return  String
		 */
		public function getAvarageRating()
		{
			$mainframe     = Factory::getApplication();
			$user          = Factory::getUser();
			$componentPath = JPATH_SITE . DS . 'components' . DS . 'com_jlike';
			require_once $componentPath . DS . 'models' . DS . 'jlike_likes.php';
			require_once $componentPath . DS . 'controller.php';

			$component = new jLikeController(array('name' => 'jlike'));
			$model = $component->getModel('jlike_likes');
			$jinput = $mainframe->input;
			$setdata = $jinput->get('request');

			// $setdata       = JFactory::getApplication()->getInput()->get('request');
			$urldata = json_decode($setdata['data']);

			require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';
			$commentFilex = $this->getjLikeViewpath('jlike', 'ratingAvg');

			// Get Rating avarage
			if (!empty($urldata))
			{
				$getRatingAvg = $model->getProductRatingAvg($urldata->cont_id, $urldata->element);
			}

			ob_start();
				include $commentFilex;
				$htmlx = ob_get_contents();
			ob_end_clean();

			return $htmlx;
		}

		/**
		 * addRating.
		 *
		 * @param   integer  $cnt_id       content id.
		 * @param   integer  $user_rating  user ratings.
		 * @param   integer  $rating_upto  allow rating 1 to 5.
		 * @param   string   $plg_name     plg_name
		 * @param   string   $element      element.
		 * @param   string   $url          url.
		 * @param   string   $title        title.
		 *
		 * @since   2.2
		 * @return  Integer
		 */
		public function addRating($cnt_id, $user_rating, $rating_upto, $plg_name, $element, $url, $title)
		{
			$db         = Factory::getDbo();
			$element_id = $this->addContent($cnt_id, $element, $url, $title);

			if ($element_id)
			{
				$insert_obj                = new stdClass;
				$insert_obj->content_id    = $element_id;
				$insert_obj->user_rating   = $user_rating;
				$insert_obj->rating_upto   = $rating_upto;
				$insert_obj->user_id       = Factory::getUser()->id;
				$insert_obj->created_date  = Factory::getDate()->toSql();
				$insert_obj->modified_date = '';

				$db->insertObject('#__jlike_rating', $insert_obj);

				return $db->insertid();
			}
		}

		/**
		 * updateRating.
		 *
		 * @param   integer  $rating_id       rating id.
		 * @param   integer  $user_rating  user ratings.
		 *
		 * @since   2.2
		 * @return  Integer
		 */
		public function updateRating($rating_id, $user_rating)
		{
			$db         = Factory::getDbo();
			// $element_id = $this->addContent($cnt_id, $element, $url, $title);

			if ($rating_id)
			{
				$update_obj       			= new stdClass;
				$update_obj->id    			= $rating_id;
				$update_obj->user_rating   	= $user_rating;
				$update_obj->modified_date 	= Factory::getDate()->toSql();
				return $db->updateObject('#__jlike_rating', $update_obj,'id');
			}
		}


		/**
		 * deleteUserRating.
		 *
		 * @param   int  $element_id  content element id
		 *
		 * @since   2.2
		 * @return  String
		 */
		public function deleteUserRating($element_id)
		{
			$db    = Factory::getDbo();
			$query = "DELETE FROM #__jlike_rating
					WHERE user_id=" . Factory::getUser()->id . "
					AND content_id= " . $element_id;
				$db->setQuery($query);

				if (!$db->execute($query))
				{
					return $db->stderr();
				}
				else
				{
					return;
				}
		}


		public function saveRating($cnt_id, $user_rating, $rating_upto, $plg_name, $element, $url, $title, $rating_id = null)
		{
			$db = Factory::getDbo();
			$user_id = Factory::getUser()->id;
			$current_date = Factory::getDate()->toSql();

			if ($rating_id) {
				// Update existing rating
				$update_obj = new stdClass;
				$update_obj->id = $rating_id;
				$update_obj->user_rating = $user_rating;
				$update_obj->modified_date = $current_date;

				return $db->updateObject('#__jlike_rating', $update_obj, 'id');
			} else {
				// Insert new rating
				$element_id = $this->addContent($cnt_id, $element, $url, $title);

				if ($element_id) {
					$insert_obj = new stdClass;
					$insert_obj->content_id = $element_id;
					$insert_obj->user_rating = $user_rating;
					$insert_obj->rating_upto = $rating_upto;
					$insert_obj->user_id = $user_id;
					$insert_obj->created_date = $current_date;
					$insert_obj->modified_date = '';

					$db->insertObject('#__jlike_rating', $insert_obj);

					return $db->insertid();
				}
			}
			return false;
		}


		/**
		 * CheckUserRating.
		 *
		 * @param   int  $element_id  content element id
		 *
		 * @since   2.2
		 * @return  String
		 */
		public function checkUserRating($element_id)
		{
			$db    = Factory::getDbo();
			$query = "SELECT * FROM #__jlike_rating
					WHERE user_id=" . Factory::getUser()->id . "
					AND content_id= " . $element_id;
			$db->setQuery($query);

			return $db->loadResult();
				
		}

		/**
		 * Checkifinstalled.
		 *
		 * @param   integer  $cnt_id   content id.
		 * @param   string   $element  element.
		 * @param   string   $url      url.
		 * @param   string   $title    title.
		 *
		 * @since   2.2
		 * @return  Integer
		 */
		public function addContent($cnt_id, $element, $url, $title)
		{
			$element 	 = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $element);
			$element 	 = ltrim($element, '.');
			$title 	 = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $title);
			$title 	 = ltrim($title, '.');
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->qn(array('jc.id','jc.like_cnt','jc.dislike_cnt')));
			$query->from($db->qn('#__jlike_content', 'jc'));
			$query->where($db->qn('jc.element_id') . ' = ' . $db->q((int) $cnt_id));
			$query->where($db->qn('jc.element') . ' = ' . $db->q($element));

			$db->setQuery($query);
			$content_id       = $db->loadResult();

			if (!$content_id)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = (int) $cnt_id;
				$insert_obj->element    = $element;
				$insert_obj->url        = $url;
				$insert_obj->title      = $title;
				$db->insertObject('#__jlike_content', $insert_obj);
				$content_id = $db->insertid();
			}

			return $content_id;
		}

		/**
		 * Update user assignment status
		 *
		 * @param   int     $element_id   Element id
		 * @param   int     $element      Eelement  e.g  com_content.article
		 * @param   int     $assigned_to  The id for the user against to update assignment status
		 * @param   string  $status       Status I- Incomplete , C- Completed, S- Started
		 *
		 * @return  boolean true/false
		 *
		 * @since  1.2
		 *
		 */
		public static function updateToDoStatus($element_id, $element, $assigned_to, $status)
		{
			if ($element_id && $element && $assigned_to && $status)
			{
				// Get the content id
				$db = Factory::getDbo();

				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__jlike_content'));
				$query->where($db->quoteName('element_id') . ' = ' . $db->q($element_id));
				$query->where($db->quoteName('element') . ' = ' . $db->q($element));

				$db->setQuery($query);

				$content_id = $db->loadResult($query);

				// Update the assignment status
				if ($content_id)
				{
					$query = $db->getQuery(true);

					$fields = array(
						$db->quoteName('status') . ' = "' . $status . '"'
					);

					$cond = array(
						$db->quoteName('content_id') . ' = ' . $content_id,
						$db->quoteName('assigned_to') . ' = ' . $assigned_to
					);

					$query->update($db->quoteName('#__jlike_todos'))->set($fields)->where($cond);
					$db->setQuery($query);

					return $db->execute();
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Get start and due date of assignment
		 *
		 * @param   int  $content_id  Content id
		 * @param   int  $user_id     user id
		 *
		 * @return  Object
		 *
		 * @since  1.2
		 *
		 */
		public function getTodos($content_id, $user_id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('td.start_date', 'td.due_date', 'u.name')));
			$query->from($db->quoteName('#__jlike_todos', 'td'));
			$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('td.assigned_by') . ')');
			$query->where($db->quoteName('assigned_to') . ' = ' . $db->quote($user_id));
			$query->where($db->quoteName('content_id') . ' = ' . $db->quote($content_id));

			$db->setQuery($query);
			$result = $db->loadObject();

			return $result;
		}
	}
}
