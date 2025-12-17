<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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

/**
 * Main helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeSubHelperExtensibleParent
{
	/**
	 * checks for view override
	 *
	 * @param   String   $s     (string) name of view
	 * @param   string   $l     layout name eg order
	 * @param   integer  $i     iterator
	 * @param   string   $e     it may be admin or site. it is side(admin/site) where to search override view
	 * @param   array    $tags  array of tags
	 *
	 * @return  String
	 *
	 * @since 1.0
	 */
	public function returnSubStrWithHTML($s, $l, $i, $e, $tags)
	{
		$contentString = substr($s, 0, $l = min(strlen($s), $l + $i));

		// Show till last word, skip the incomplete last word
		$contentString = substr($contentString, 0, (strrpos($contentString, ' ')));

		return $contentString
		. (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . (strlen($s) > $l ? $e : '');
	}

	/**
	 * checks for view override
	 *
	 * @param   String  $s       (string) name of view
	 * @param   string  $l       layout name eg order
	 * @param   string  $e       it may be admin or site. it is side(admin/site) where to search override view
	 * @param   string  $isHTML  it may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
	 *
	 * @return  String
	 *
	 * @since 1.0
	 */
	public function getsubstrwithHTML($s, $l, $e = '...', $isHTML = false)
	{
		$i    = 0;
		$tags = array();

		if ($isHTML !== false)
		{
			preg_match_all('/<[^>]+>([^<]*)/', $s, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

			foreach ($m as $o)
			{
				if ($o[0][1] - $i >= $l)
				{
					break;
				}

				$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);

				if ($t[0] != '/')
				{
					$tags[] = $t;
				}
				elseif (end($tags) == substr($t, 1))
				{
					array_pop($tags);
				}

				$i += $o[1][1] - $o[0][1];
			}
		}

		return $this->returnSubStrWithHTML($s, $l, $i, $e, $tags);
	}

	/**
	 * Sendmail
	 *
	 * @param   string  $recipient    Email
	 * @param   string  $subject      Email sub
	 * @param   string  $body         Email body
	 * @param   array   $extraParams  Email bcc
	 *
	 * @return boolean
	 */
	public static function sendmail($recipient, $subject, $body, $extraParams = array())
	{

		try
		{
			$config     = Factory::getConfig();
			$from       = $config->get('mailfrom');
			$fromname   = $config->get('fromname');
			$recipient  = trim($recipient);
			$cc         = array();
			$bcc        = array();
			$attachment = null;
			$mode       = 1;

			// Extra parameters to the email
			if (!empty($extraParams))
			{
				$paramValues = array("cc", "bcc", "attachment");

				foreach ($extraParams as $param => $value)
				{
					if (in_array($value, $paramValues))
					{
						$$param = $value;
					}
					/*if ($param == 'cc')
					{
						$cc = $value;
					}

					if ($param == 'bcc')
					{
						$bcc = $value;
					}

					if ($param == 'attachment')
					{
						$attachment = $value;
					}*/
				}
			}

			return Factory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * updateJlikeLikes
	 *
	 * @param   integer  $element_id  element_id.
	 * @param   array    $data        data.
	 * @param   integer  $like_id     like_id.
	 *
	 * @since   2.2
	 * @return  array
	 */
	public function updateJlikeLikes($element_id, $data, $like_id)
	{
		$db    = Factory::getDBO();
		$content_uobj    = new stdClass;
		$like_uobj       = new stdClass;
		$like_uobj->date = time();
		$returnData = array();
		$like_uobjverb = '';

		switch ($data['method'])
		{
			case 'like':
				$like_uobj->like    = 1;
				$like_uobj->dislike = 0;
				$like_uobj->date    = time();
				$like_uobjverb      = Text::_('COM_JLIKE_LIKE_VERB');
				break;

			case 'dislike':
				$like_uobj->dislike = 1;
				$like_uobj->like    = 0;
				$like_uobj->date    = time();
				$like_uobjverb      = Text::_('COM_JLIKE_DISLIKE_VERB');
				break;

			case 'unlike':
				$like_uobj->like        = 0;
				$like_uobj->dislike     = 0;
				$like_uobjverb          = Text::_('COM_JLIKE_UNLIKE_VERB');
				break;

			case 'undislike':
				$like_uobj->like           = 0;
				$like_uobj->dislike        = 0;
				$like_uobjverb             = Text::_('COM_JLIKE_UNDISLIKE_VERB');
				break;
		}

		$like_uobj->id = $like_id;
		$like_uobj->modified = date("Y-m-d H:i:s");
		$db->updateObject('#__jlike_likes', $like_uobj, 'id');

		$comjlikeHelper = new comjlikeHelper;
		$count_res = $comjlikeHelper->getLikeDislikeCount($element_id);
		$content_uobj->like_cnt = $count_res->likecnt;
		$content_uobj->dislike_cnt = $count_res->dislikecnt;
		$content_uobj->id = $element_id;

		if (!empty($data['url']))
		{
			$content_uobj->url = $data['url'];
		}

		/*Get total number of likes and dislikes*/

		$db->updateObject('#__jlike_content', $content_uobj, 'id');

		$returnData['like_uobjverb'] = $like_uobjverb;
		$returnData['timestamp'] = $like_uobj->date;

		// Trigger the after save like, dislike, unlike, undislike event or campaign.

		// Append inserted like entry id in action log data
		$data['entry_id'] = $like_id;

		Factory::getApplication()->triggerEvent('onAfterJlikeLikeDislikeSave', array($data));

		return $returnData;
	}

	/**
	 * getConnectedUser
	 *
	 * @return  array
	 */
	public function getConnectedUser()
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.memberid')
			->from('#__comprofiler_members AS a')
			->join('LEFT', '#__comprofiler AS b ON a.memberid = b.user_id')
			->where('(a.accepted=1) AND a.referenceid = ' . $db->quote(Factory::getUser()->id));

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * getConnectedUserForJSIntegration
	 *
	 * @return  string
	 */
	public function getConnectedUserForJSIntegration()
	{
		$comjlikeHelper = new comjlikeHelper;
		$query = '';

		if ($comjlikeHelper->Checkifinstalled('com_community'))
		{
			include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
			$friends = CFactory::getUser()->_friends;

			if ($friends)
			{
				$friends .= ',' . Factory::getUser()->id;
				$query = " jl.userid IN($friends)";
			}
		}

		return $query;
	}

	/**
	 * getConnectedUserForESIntegration
	 *
	 * @return  string
	 */
	public function getConnectedUserForESIntegration()
	{
		$comjlikeHelper = new comjlikeHelper;
		$query = '';

		if ($comjlikeHelper->Checkifinstalled('com_easysocial'))
		{
			$model = FD::model('friends');
			$options = array();
			$options['idonly'] = 1;

			$esfriends_ids = $model->getFriends(Factory::getUser()->id, $options);

			if ($esfriends_ids)
			{
				$friends = '';
				$friends .= ',' . Factory::getUser()->id;
				$query = " jl.userid IN($esfriends_ids)";
			}
		}

		return $query;
	}

	/**
	 * getConnectedUserForCBIntegration
	 *
	 * @return  string
	 */
	public function getConnectedUserForCBIntegration()
	{
		$comjlikeHelper = new comjlikeHelper;
		$query = '';

		$connections_a = $this->getConnectedUser();

		if ($comjlikeHelper->Checkifinstalled('com_comprofiler') && !empty($connections_a))
		{
			$connections = implode(',', $connections_a);
			$connections .= ',' . Factory::getUser()->id;
			$query = " jl.userid IN($connections)";
		}

		return $query;
	}

	/**
	 * getConnectedUserByIntegrationType
	 *
	 * @param   string  $integration  integration
	 *
	 * @return  string
	 */
	public function getConnectedUserByIntegrationType($integration)
	{
		$query = '';

		if ($integration == 'js')
		{
			$query = $this->getConnectedUserForJSIntegration();
		}
		elseif ($integration == 'easysocial')
		{
			$query = $this->getConnectedUserForESIntegration();
		}
		elseif ($integration == 'cb')
		{
			$query = $this->getConnectedUserForCBIntegration();
		}

		return $query;
	}

	/**
	 * getPeoplelikedthisContentBeforInetgrationType
	 *
	 * @return  string
	 */
	public function getPeoplelikedthisContentBeforInetgrationType()
	{
		$jlikemainhelperObj = new ComjlikeMainHelper;
		$jlike_settings = $jlikemainhelperObj->getjLikeParams();
		$integration = $jlikemainhelperObj->getSocialIntegration();
		$query = '';

		if ($jlike_settings->get('which_users_to_show') == 'friends')
		{
			$query = $this->getConnectedUserByIntegrationType($integration);
		}

		return $query;
	}

	/**
	 * getPeoplelikedthisContentBefor
	 *
	 * @param   INT    $content_id   id of the content
	 * @param   Array  $extraParams  extraparams
	 *
	 * @return  object list
	 */
	public function getPeoplelikedthisContentBefor($content_id, $extraParams)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('jl.userid as id ,u.email,u.name,u.username')
			->from('#__jlike_likes AS jl')
			->join('INNER', '#__users AS u ON jl.userid = u.id')
			->where('jl.like = 1 AND jl.content_id = ' . $db->quote($content_id));

		$appendQuery = $this->getPeoplelikedthisContentBeforInetgrationType();

		if (!empty($appendQuery))
		{
			$query->where($appendQuery);
		}

		$db->setQuery($query);
		$pwltcb = $db->loadObjectList();

		if ($pwltcb)
		{
			foreach ($pwltcb as $ind => $obj)
			{
				$userObject = Factory::getUser($obj->id);
				$udetails               = comjlikeHelper::getUserDetails($userObject, $extraParams);
				$pwltcb[$ind]->img_url  = $udetails['img_url'];
				$pwltcb[$ind]->link_url = $udetails['link_url'];
			}
		}

		return $pwltcb;
	}
}
