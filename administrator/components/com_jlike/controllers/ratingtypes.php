<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Rating Type list controller class.
 *
 * @since  3.0.0
 */
class JlikeControllerRatingtypes extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   3.0.0
	 */
	public function getModel($name = 'Ratingtype', $prefix = 'JLikeModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to publish records.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function publish()
	{
		$cid = Factory::getApplication()->getInput()->get('cid', array(), 'array');
		$data = array(
			'publish' => 1,
			'unpublish' => 0
		);

		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		// Get some variables from the request
		if (empty($cid))
		{
			throw new Exception(Text::_('COM_JLIKE_NO_RATING_TYPE_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->publish($cid, $value);

				if ($value === 1)
				{
					$ntext = 'COM_JLIKE_N_RATING_TYPE_PUBLISHED';
				}
				elseif ($value === 0)
				{
					$ntext = 'COM_JLIKE_N_RATING_TYPE_UNPUBLISHED';
				}

				if (count($cid) >= 1)
				{
					$this->setMessage(Text::plural($ntext, count($cid)));
				}
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect('index.php?option=com_jlike&view=ratingtypes');
	}

	/**
	 * Method to set the default rating type.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setDefault()
	{
		// Check for request forgeries
		$this->checkToken();

		$pks = $this->getInput()->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_JLIKE_NO_RATING_TYPE_SELECTED'));
			}

			$pks = ArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();

			$item = $model->getItem($id);

			if ($item->state == 0)
			{
				$this->setMessage(Text::_('COM_JLIKE_ERROR_UNPUBLISHED_RATING_TYPE_DEFAULT_SET'), 'error');
			}
			else
			{
				$model->setDefaultRatingType($id);
				$this->setMessage(Text::_('COM_JLIKE_SUCCESS_RATING_TYPE_DEFAULT_SET'));
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), 500);
		}

		$this->setRedirect('index.php?option=com_jlike&view=ratingtypes');
	}

	/**
	 * Method to unset the default rating type
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function unsetDefault()
	{
		// Check for request forgeries
		$this->checkToken('request');

		$pks = $this->getInput()->post->get('cid', array(), 'array');
		$pks = ArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks))
			{
				throw new Exception(Text::_('COM_JLIKE_NO_RATING_TYPE_SELECTED'));
			}

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->unsetDefaultRatingType($id);
			$this->setMessage(Text::_('COM_JLIKE_SUCCESS_RATING_TYPE_DEFAULT_UNSET'));
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), 500);
		}

		$this->setRedirect('index.php?option=com_jlike&view=ratingtypes');
	}
}
