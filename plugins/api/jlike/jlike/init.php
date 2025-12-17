<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
require_once JPATH_SITE . '/components/com_jlike/models/contentform.php';
require_once JPATH_SITE . '/components/com_jlike/models/annotationform.php';

/**
 * Class for checkin to tickets for mobile APP
 *
 * @package     Jlike
 * @subpackage  component
 * @since       1.0
 */

class JlikeApiResourceInit extends ApiResource
{
	/**
	 * Comment to tickets for mobile APP
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function post()
	{

		$input = Factory::getApplication()->getInput();
		$post  = Factory::getApplication()->getInput();

		$data = array();

		// Item Url
		$data['url']      = $post->getString('url', '');

		// E.g comment
		$data['type']     = $post->getString('type', '');

		// E.g collaborator
		$data['subtype']  = $post->getString('subtype', '');

		// E.g com_xyz
		$data['element']     = $post->getString('client', '');

		// Item Id
		$data['element_id']  = $post->getInt('cont_id', '');

		// Item title
		$data['title'] = $post->getString('title', '');

		$client        = $post->getString('client', '');

		list($data['plg_type'], $data['plg_name']) = explode(".", $client);

		// Load AnnotationForm Model
		$model = BaseDatabaseModel::getInstance('contentform', 'JlikeModel');

		$result = new stdclass;

		if ($content_id = $model->getContentID($data))
		{
			$result->success = true;
			$result->content_id = $content_id;

			// Get the users to mention
			if ($data['type'] == "annotations")
			{
				$model = BaseDatabaseModel::getInstance('AnnotationForm', 'JlikeModel');
				$result->userslist = $model->getUsersList($data);
				$result->usersInfo = $model->getUserInfo($data);
			}
		}
		else
		{
			$result->success = false;
			$result->message = Text::_("COM_JLIKE_ERROR_IN_INIT");
		}

		$this->plugin->setResponse($result);
	}
}
