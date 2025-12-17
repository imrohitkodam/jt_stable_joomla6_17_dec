<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;

/**
 * JTicketing api class.
 *
 * @since  3.0.0
 */
class JTicketingTechjoomlaapi extends CMSObject
{
	/**
	 * The auto incremental primary key of the techjoomlaapi
	 *
	 * @var    integer
	 * @since  3.0.0
	 */
	public $id = 0;

	/**
	 * Api detail
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	public $api = '';

	/**
	 * User credentials
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	public $token = '';

	/**
	 * Joomla user id
	 *
	 * @var    int
	 * @since  3.0.0
	 */
	public $user_id = 0;

	/**
	 * Client for which the entry is being added
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	public $client = '';

	/**
	 * Constructor activating the default information of the api users
	 *
	 * @param   int  $id  The unique api user id key to load.
	 *
	 * @since   3.0.0
	 */
	public function __construct($id = 0)
	{
		if (!empty($id))
		{
			$this->load($id);
		}
	}

	/**
	 * Returns the global api user object
	 *
	 * @param   integer  $id  The primary key of the api user to load (optional).
	 *
	 * @return  JTicketingTechjoomlaapi  The api user object.
	 *
	 * @since   3.0.0
	 */
	public static function getInstance($id = 0)
	{
		if (!$id)
		{
			return new JTicketingTechjoomlaapi;
		}

		if (empty(self::$apiUser[$id]))
		{
			self::$apiUser[$id] = new JTicketingTechjoomlaapi($id);
		}

		return self::$apiUser[$id];
	}

	/**
	 * Method to load a api user properties
	 *
	 * @param   int  $id  The api id
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function load($id)
	{
		$table = JT::table("techjoomlaapi");

		if ($table->load($id))
		{
			$this->id      = (int) $table->get('id');
			$this->api     = $table->get('api');
			$this->token   = $table->get('token');
			$this->client  = $table->get('client');
			$this->user_id = (int) $table->get('user_id');

			return true;
		}

		return false;
	}

	/**
	 * Method to save the api user object to the database
	 *
	 * @param   array  $array  The details to be save
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function save($array)
	{
		$isNew = $this->isNew();
		$table = JT::table('techjoomlaapi');
		$table->bind($array);

		try
		{
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			$result = $table->store();

			if ($result && $isNew)
			{
				$this->id = $table->get('id');

				return true;
			}
			elseif ($result && !$isNew)
			{
				$this->load($this->id);

				return true;
			}
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $result;
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param   array  $array  The associative array to bind to the object
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function bind($array)
	{
		if (isset($array['user_id']))
		{
			$this->user_id = $array['user_id'];
		}

		if (isset($array['token']))
		{
			$this->token = $array['token'];
		}

		if ($array['api'])
		{
			$this->api = $array['api'];
		}

		if ($array['client'])
		{
			$this->client = $array['client'];
		}

		return true;
	}

	/**
	 * Method to check is entry new or not
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * Method to load a api users by user id
	 *
	 * @param   int  $userId  The user id
	 *
	 * @return  Array|Boolean  false on fail and Array on success
	 *
	 * @since   3.0.0
	 */
	public function loadByUserId($userId)
	{
		$table = JT::table("techjoomlaapi");

		if ($table->load(array('user_id' => $userId)))
		{
			$this->setProperties($table->getProperties());
			$this->id      = (int) $table->get('id');
			$this->api     = $table->get('api');
			$this->token   = $table->get('token');
			$this->client  = $table->get('client');
			$this->user_id = (int) $table->get('user_id');

			return $this;
		}

		return false;
	}

	/**
	 * Method to delete a api users by id
	 *
	 * @return  Boolean  false on fail and true on success
	 *
	 * @since   3.0.0
	 */
	public function delete()
	{
		$table = JT::table("techjoomlaapi");

		if (!$table->delete($this->id))
		{
			$this->setError($table->getError());

			return false;
		}

		return true;
	}
}
