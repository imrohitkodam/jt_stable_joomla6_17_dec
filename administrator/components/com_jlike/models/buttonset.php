<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
class JLikeModelButtonset extends BaseDatabaseModel
{
	protected $_id = null;

	protected $_data = null;

	public function __construct()
	{
		parent::__construct();

		$array = Factory::getApplication()->getInput()->get('cid', array(), 'array');

		if (isset($array[0]))
		{
			$this->setId((int) $array[0]);
		}
	}

	public function setId($id)
	{
		$this->_id   = $id;
		$this->_data = null;
	}

	public function getData()
	{
		// Load the data
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query);
		}

		if (!$this->_data)
		{
			$this->_data     = new stdClass();
			$this->_data->id = 0;
		}

		return $this->_data;
	}

	public function _buildQuery()
	{
		return ' SELECT * FROM #__jlike ';
	}

	public function store()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->getArray($_POST);
		$id    = $post['buttonset'];
		// This looks dumb, but joomla wouldn't allow me to chain them.
		$sql = "UPDATE `#__jlike` set published=0";
		$this->_db->setQuery($sql);
		$this->_db->execute();
		$sql = "UPDATE `#__jlike` set published=1 where id='{$id}'";
		$this->_db->setQuery($sql);
		$this->_db->execute();

		return true;
	}

	public function delete()
	{
		$id  = Factory::getApplication()->getInput()->get('published', 0);
		$row = $this->getTable();

		if (!$row->delete($id))
		{
			$this->setError($row->getErrorMsg());

			return false;
		}

		return true;
	}
}
