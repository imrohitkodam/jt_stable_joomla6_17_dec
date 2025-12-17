<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
/**
 * @package		jlike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

jimport ( 'joomla.application.component.model' );

class JLikeModelJLike extends BaseDatabaseModel {
	protected $_id = null;
	protected $_data = null;

	function __construct() {
		parent::__construct ();

		$array = Factory::getApplication()->getInput()->get('cid', 0, 'array');
		$this->setId ( ( int ) $array [0] );
	}

	function setId($id) {
		$this->_id = $id;
		$this->_data = null;
	}

	function getData() {
		// Load the data
		if (empty ( $this->_data )) {
			$query = $this->_buildQuery ();
			$this->_data = $this->_getList ( $query );
		}

		if (! $this->_data) {
			$this->_data = new stdClass ();
			$this->_data->id = 0;
		}
		return $this->_data;
	}

	function _buildQuery() {
		$query = ' SELECT * FROM #__jlike ';
		return $query;
	}

	function store() {
		$id = Factory::getApplication()->getInput()->get ('published', 0);

		// This looks dumb, but joomla wouldn't allow me to chain them.
		$sql = "UPDATE `#__jlike` set published=0";
		$this->_db->setQuery ( $sql );
		$this->_db->execute();
		$sql = "UPDATE `#__jlike` set published=1 where id='{$id}'";
		$this->_db->setQuery ( $sql );
		$this->_db->execute();

		return true;
	}

	function delete() {
		$id = Factory::getApplication()->getInput()->get('published', 0);
		$row =  $this->getTable ();

		if (! $row->delete ( $id )) {
			$this->setError ( $row->getErrorMsg () );
			return false;
		}

		return true;
	}

}
