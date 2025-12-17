<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

/**
 * subscription controller class.
 *
 * @since  1.6
 */
class JlikeControllerPathUsers extends JLikeControllerBase
{
	protected $app;

	protected $path_id;
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
		$this->path_id  = $this->getApplication()->getInput()->get('path_id');

		if (empty($this->path_id))
		{
			$this->path_id  = $this->getApplication()->getInput()->post->get('path_id', '', 'int');
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. config array
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function &getModel( $name = 'PathUsers', $prefix = 'JlikeModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
