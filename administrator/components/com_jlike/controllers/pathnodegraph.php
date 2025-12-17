<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;


/**
 * The PathNodeGraph controller
 *
 * @since  1.0.0
 */
class JlikeControllerPathNodeGraph extends FormController
{
		protected $view_list;

	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'pathnodegraphs';

		parent::__construct();
	}
}
