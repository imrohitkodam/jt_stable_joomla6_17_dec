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
defined('_JEXEC') or die;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;


require_once JPATH_SITE . '/components/com_jlike/models/comment.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotation.php';
require_once JPATH_SITE . '/components/com_jlike/models/content.php';

// Load jlike lang. file
$lang = Factory::getLanguage();
$lang->load('com_jlike', JPATH_SITE, $lang->getTag(), true);


use Joomla\Utilities\ArrayHelper;
/**
 * Jlike model.
 *
 * @since  1.6
 */
class JlikeModelAnnotationForm extends FormModel
{
	private $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since  1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_jlike');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->getInput()->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_jlike.edit.annotation.id');
		}
		else
		{
			$id = Factory::getApplication()->getInput()->get('id');
			Factory::getApplication()->setUserState('com_jlike.edit.annotation.id', $id);
		}

		$this->setState('annotation.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('annotation.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id           The id of the object to get.
	 * @param   Array    $extraParams  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function &getData($id = null, $extraParams=array())
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('annotation.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id))
			{
				$user = Factory::getUser();
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_jlike') || $user->authorise('core.create', 'com_jlike');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_jlike'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, CMSObject::class);
			}
		}

		$annotationFormModel = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $extraParams);

		// Append comment author info
		$this->item->user = $this->getAuthor($this->item->user_id);

		// Parse the Comment
		$this->item->annotation_html = self::parsedMention($this->item->annotation, $extraParams);
		$this->item->annotation_html = self::replaceSmileyAsImage($this->item->annotation_html);

		$this->item->annotation_date = HTMLHelper::date($this->item->annotation_date, 'Y-m-d H:i:s', true);
		$this->item->is_mine = false;

		if ($this->item->user_id == Factory::getUser()->id)
		{
			$this->item->is_mine = true;
		}

		if ($this->item->parent_id == '0')
		{
			$this->item->parent_id = null;
		}

		$this->item->user       = new stdclass;
		$this->item->user->id   = $this->item->user_id;
		$this->item->user->name = Factory::getUser($this->item->user_id)->name;

		$ment_usr = Factory::getUser($this->item->user_id);

		$link = '';
		$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

		if ($profileUrl)
		{
			if (!parse_url($profileUrl, PHP_URL_HOST))
			{
				$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
			}
		}

		$this->item->user->profile_link  = $link;
		$this->item->user->avatar        = $sLibObj->getAvatar($ment_usr, 50);
		$this->item->annotation_id       = $this->item->id;

		return $this->item;
	}

	/**
	 * Method to get the author details for a comment
	 *
	 * @param   string  $userid  Joomla userid for the user
	 *
	 * @return  object   Author details (name, id and avatar)
	 */
	private function getAuthor($userid)
	{
		$res = new stdClass;
		$author = Factory::getUser($userid);
		$res->name = $author->name;
		$res->id = $author->id;
		$res->avatar = '';

		return $res;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the Table class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for Table object
	 *
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Annotation', $prefix = 'JlikeTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jlike/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('annotation.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('annotation.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    Form    A Form object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jlike.annotation', 'annotationform', array(
			'control'   => 'jform',
			'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_jlike.edit.annotation.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('annotation.id');
		$state = (!empty($data['state'])) ? 1 : 0;

		$data['annotation_date'] = Factory::getDate('now')->toSQL();

		if (!empty($data["user"]))
		{
			$user  = $data["user"];
		}
		else
		{
			$user  = Factory::getUser();
		}

		if ($id)
		{
			// Check the user can edit this item
			$authorised = ($user->authorise('core.edit', 'com_jlike') || $user->authorise('core.edit.own', 'com_jlike')) ? true : false;
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_jlike');
		}

		if ($authorised !== true)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Code Written For Private Comment to Check Context Value
		PluginHelper::importPlugin('content');
		$triggerResult = Factory::getApplication()->triggerEvent('onBeforeSaveComment', array($data));

		if (!empty($triggerResult) && !$triggerResult[0])
		{
			$result = new stdClass;
			$result->success = false;
			$result->result  = Text::_('JERROR_ALERTNOAUTHOR');
			echo new JsonResponse($result);
			Factory::getApplication()->close();
		}

		// End Code of Private Comment

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			$data['id'] = $table->id;

			$mentioned_info  = self::getMentionsInfo($data['annotation']);

			// Get mention users id
			$mentioned_users = $mentioned_info[2];

			$data["annotation_html"] = self::parsedMention($data["annotation"], $data);
			$data["mentioned_users"] = $mentioned_users;

			// Get Social Integration form each component
			PluginHelper::importPlugin($data["plg_type"], $data["plg_name"]);
			Factory::getApplication()->triggerEvent('onAfter' . $data["plg_name"] . 'OnCommentAfterSave',  array($data));

			// Create object of content model to get comment data
			$JlikeModelContent = new JlikeModelContent;

			$commentData = (array) $JlikeModelContent->getData($data['content_id']);

			// Append inserted comment entry id in action log data
			$commentData['entry_id'] = $table->id;

			// Trigger the after save event.
			Factory::getApplication()->triggerEvent('onAfterJlikeCommentSave', array($commentData, true));

			return $table->id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get the type id
	 *
	 * @param   array  $data  The form data
	 *
	 * @return int type Id
	 */
	public function getTypeId($data)
	{
		extract($data);

		$db  = Factory::getDbo();
		$app = Factory::getApplication();

		try
		{
			// Add entry in type table if type is not exist
			$query
				->select($db->quoteName('tp.id'))
				->from($db->quoteName('#__jlike_types', 'tp'))
				->where($db->quoteName('tp.type') . ' LIKE "%' . $db->quote($type) . '%"')
				->where($db->quoteName('tp.subtype') . ' LIKE "%' . $db->quote($subtype) . '%"')
				->where($db->quoteName('tp.client') . ' LIKE "%' . $db->quote($client) . '%"');

			$db->setQuery($query);

			$type_Id = $db->loadResult();

			return $type_Id;
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * Method to add the type
	 *
	 * @param   array  $data  The form data
	 *
	 * @return int type Id
	 */
	public function addType($data)
	{
		extract($data);
		$db = Factory::getDBO();

		try
		{
			$typeObj           = new stdClass;
			$typeObj->type     = $type;
			$typeObj->subtype = $subtype;
			$typeObj->client   = $client;

			$db->insertObject('#__jlike_types', $typeObj);

			return $type_Id = $db->insertid();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * Method to delete data
	 *
	 * @param   array  $data  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($data)
	{

		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('annotation.id');

		if (Factory::getUser()->authorise('core.delete', 'com_jlike') !== true)
		{
			throw new Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$table = $this->getTable();

		// Create object of recommendation model
		$JlikeModelAnnotation = new JlikeModelAnnotation;

		// Get content id of added comment
		$annotationData = (array) $JlikeModelAnnotation->getData($data['id']);

		// Create object of content model
		$JlikeModelContent = new JlikeModelContent;

		// Get content data - title, url, element from content table
		$commentData = (array) $JlikeModelContent->getData($annotationData['content_id']);

		if ($table->delete($data['id']) === true)
		{
			// Execute trigger after deleting comment data
			Factory::getApplication()->triggerEvent('onAfterJlikeCommentDelete', array($commentData));

			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

	/**
	 * Get the list of users who are mentioned in comment
	 *
	 * @param   String  $comment  Comment to be parsed to get the list of mentioned users
	 *
	 * @return  Array   Matches array contain 0=> Mentioned tag  1=> Mentioned user name 3=> Mentioned user Id
	 * (
	 * [0] => Array
	 * (
	 *  [0] => @[Madhuchandra R](80)
	 *  [1] => @[Ashwin K Date](86)
	 *  )
	 *  [1] => Array
	 *  (
	 *  [0] => Madhuchandra R
	 *  [1] => Ashwin K Date
	 *  )
	 *  [2] => Array
	 *  (
	 *  [0] => 80
	 *  [2] => 86
	 *  )
	 *  )
	 *
	 * @since  1.7.5
	 */
	public static function getMentionsInfo($comment = '')
	{
		if (!empty($comment))
		{
			preg_match_all('/@\[([^\]]+)\]\(([^ \)]+)\)/', $comment, $matches);

			return $matches;
		}
	}

	/**
	 * Replace smiley text to smiley image
	 *
	 * @param   String  $comment  Comment to parse
	 *
	 * @return  Smiley parsed comment
	 *
	 * @since 1.0
	 */
	public function replaceSmileyAsImage($comment)
	{
		$replacements = array(
			":)" => "smile.jpg",
			":-)" => "smile.jpg",
			":(" => "sad.jpg",
			":-(" => "sad.jpg",
			";)" => "wink.jpg",
			";-)" => "wink.jpg",
			";(" => "cry.jpg",
			"B-)" => "cool.jpg",
			"B)" => "cool.jpg",
			":D" => "grin.jpg",
			":-D" => "grin.jpg",
			":o" => "shocked.jpg",
			":0" => "shocked.jpg",
			":-o" => "shocked.jpg",
			":-0" => "shocked.jpg",
			":-3" => "love.png"
		);

		$smileyimgPath = Uri::root(true) . '/components/com_jlike/assets/images/smileys';

		foreach ($replacements as $code => $image)
		{
			$html    = '<img src="' . $smileyimgPath . '/' . $image . '" alt="' . $code . '"/>';
			$comment = str_replace($code, $html, $comment);
		}

		return $comment;
	}

	/**
	 * Parsed Mentions with Avatar
	 *
	 * @param   String  $comment  Comment to parse
	 * @param   Array   $exParam  Should Contain plg_type, plg_name
	 *
	 * @return  String  Comment after parsing mentions
	 *
	 * @since 1.2.2
	 */
	public static function parsedMention($comment, $exParam)
	{
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$plgData            = array(
			"plg_type" => $exParam['plg_type'],
			"plg_name" => $exParam['plg_name']
		);
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $plgData);

		preg_match_all('/@\[([^\]]+)\]\(([^ \)]+)\)/', $comment, $matches);
		$mentioned_users = $matches[2];

		/** Matches array contain 0=> Mentioned tag  1=> Mentioned user name 3=> Mentioned user Id
		Array
		(
		[0] => Array
		(
		[0] => @[Madhuchandra R](80)
		[1] => @[Ashwin K Date](86)
		)

		[1] => Array
		(
		[0] => Madhuchandra R
		[1] => Ashwin K Date
		)

		[2] => Array
		(
		[0] => 80
		[2] => 86
		)
		)*/

		// Replace the mentioned tag with user name & link to user profile
		foreach ($mentioned_users as $key => $ment_usrId)
		{
			$ment_usr = Factory::getUser($ment_usrId);

			$link = '';
			$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
				}
			}

			// Profile link html $matches[1][$key]=> Mentioned User Name
			$profile_link_html = '<a href="' . $link . '" target="_blank">' . $matches[1][$key] . '</a>';

			// Replace the mentioned user tag with profile link
			$comment = str_replace($matches[0][$key], $profile_link_html, $comment);
		}

		return $comment;
	}

	/**
	 * Get Totals
	 *
	 * @param   int  $conent_id  Content id
	 * @param   int  $type       type
	 *
	 * @return  int  Annontations count
	 *
	 * @since 1.2.2
	 */
	public function getTotal($conent_id, $type)
	{
		$db = Factory::getDBO();
		$app = Factory::getApplication();

		try
		{
			$query = $db->getQuery(true);

			$query->select('COUNT(id)');
			$query->from($db->qn('#__jlike_annotations'));

			if (!empty($conent_id))
			{
				$query->where($db->qn('content_id') . ' = ' . $db->q($conent_id));
			}

			if (!empty($type))
			{
				$query->where($db->qn('type') . ' = ' . $db->q($type));
			}

			$db->setQuery($query);

			return $count = $db->loadResult();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$app->enqueueMessage($e->getMessage(), "error");
		}
	}

	/**
	 * Get the users list
	 *
	 * @param   Array  $data  data
	 *
	 * @return  $users
	 */
	public function getUsersList($data)
	{
		$plg_type = $data['plg_type'];
		$plg_name = $data['plg_name'];

		PluginHelper::importPlugin($plg_type, $plg_name);

		$users = Factory::getApplication()->triggerEvent('onAfter' . $plg_name . 'GetUsersList', array($data));

		$userData = array();

		foreach ($users[0] as $user)
		{
			// TODO: check if allow self mention like google doc
			if ($user->id != Factory::getUser()->id)
			{
				$userInfo           = self::getUserInfo($data, $user->id);
				$user->avatar       = $userInfo->avatar;
				$user->profile_link = $userInfo->profile_link;
				$userData[]         = $user;
			}
		}

		return $userData;
	}

	/**
	 * Get the users list
	 *
	 * @param   Array  $data    data
	 * @param   Int    $userId  userId of user
	 *
	 * @return  $users
	 */
	public function getUserInfo($data, $userId = null)
	{
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;
		$sLibObj            = $ComjlikeMainHelper->getSocialLibraryObject('', $data);

		if ($userId !== null)
		{
			$user     = new stdclass;
			$ment_usr = Factory::getUser($userId);
		}
		else
		{
			$user       = new stdclass;
			$user->id   = Factory::getUser()->id;
			$user->name = Factory::getUser()->name;
			$ment_usr   = Factory::getUser();
		}

		$link = '';
		$link = $profileUrl = $sLibObj->getProfileUrl($ment_usr);

		if ($profileUrl)
		{
			if (!parse_url($profileUrl, PHP_URL_HOST))
			{
				$link = Uri::root() . substr(Route::_($sLibObj->getProfileUrl($ment_usr)), strlen(Uri::base(true)) + 1);
			}
		}

		$user->profile_link  = $link;
		$user->avatar        = $sLibObj->getAvatar($ment_usr, 50);

		return $user;
	}
}
