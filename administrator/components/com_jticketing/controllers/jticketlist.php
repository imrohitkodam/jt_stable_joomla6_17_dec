<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Lengths list controller class.
 *
 * @package     JTicketing
 * @subpackage  com_jticketing
 * @since       2.2
 */
class JTicketingControllerJticketlist extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unfeatured', 'featured');
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
			'unpublish' => 0,
			'archive' => 2,
			'trash' => -2,
			'report' => -3
		);

		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		// Get called controller name
		$controllerName = get_called_class();
		$controllerName = str_split($controllerName, strlen('JTicketingController'));
		$currentController = $controllerName[1];
		$currentListView = strtolower($currentController);

		// Get called controller's - singular and plural names
		$singular_name = Text::_('COM_JTICKETING_SINGULAR_' . strtoupper($currentController));
		$plural_name   = Text::_('COM_JTICKETING_PLURAL_' . strtoupper($currentController));

		// Get some variables from the request
		if (empty($cid))
		{
			Log::add(Text::sprintf('COM_JTICKETING_NO_JT_ITEMS_SELECTED', $plural_name), Log::WARNING, 'jerror');
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
				$count = count($cid);

				// Multiple records.
				if ($count > 1)
				{
					if ($value === 1)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_PUBLISHED', $count, $plural_name);
					}
					elseif ($value === 0)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_UNPUBLISHED', $count, $plural_name);
					}
					elseif ($value == 2)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_ARCHIVED', $count, $plural_name);
					}
					else
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_TRASHED', $count, $plural_name);
					}
				}
				// Single record.
				else
				{
					if ($value === 1)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_PUBLISHED', $count, $singular_name);
					}
					elseif ($value === 0)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_UNPUBLISHED', $count, $singular_name);
					}
					elseif ($value == 2)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_ARCHIVED', $count, $singular_name);
					}
					else
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_TRASHED', $count, $singular_name);
					}
				}

				$this->setMessage($ntext);
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect('index.php?option=com_jticketing&view=' . $currentListView);
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = Factory::getApplication()->getInput()->get('cid', array(), 'array');

		// Get called controller name
		$controllerName = get_called_class();
		$controllerName = str_split($controllerName, strlen('JTicketingController'));
		$currentController = $controllerName[1];
		$currentListView = strtolower($currentController);

		// Get called controller's - singular and plural names
		$singular_name = Text::_('COM_JTICKETING_SINGULAR_' . strtoupper($currentController));
		$plural_name   = Text::_('COM_JTICKETING_PLURAL_' . strtoupper($currentController));

		// Get some variables from the request
		if (!is_array($cid) || count($cid) < 1)
		{
			Log::add(Text::sprintf('COM_JTICKETING_NO_JT_ITEMS_SELECTED', $plural_name), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Remove the items.
			try
			{
				$status = $model->delete($cid);
				$count = count($cid);

				if ($status)
				{
					// Multiple records.
					if ($count > 1)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_DELETED', $count, $plural_name);
					}
					// Single record.
					else
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_DELETED', $count, $singular_name);
					}

					$this->setMessage($ntext);
				}
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		$this->setRedirect('index.php?option=com_jticketing&view=' . $currentListView);
	}

	/**
	 * Method to feature records.
	 *
	 * @return void
	 *
	 * @since 2.2
	 */
	public function featured()
	{
		$cid = Factory::getApplication()->getInput()->get('cid', array(), 'array');
		$data = array(
			'featured' => 1,
			'unfeatured' => 0
		);

		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		// Get called controller name
		$controllerName = get_called_class();
		$controllerName = str_split($controllerName, strlen('JTicketingController'));
		$currentController = $controllerName[1];
		$currentListView = strtolower($currentController);

		// Get called controller's - singular and plural names
		$singular_name = Text::_('COM_JTICKETING_SINGULAR_' . strtoupper($currentController));
		$plural_name   = Text::_('COM_JTICKETING_PLURAL_' . strtoupper($currentController));

		// Get some variables from the request
		if (empty($cid))
		{
			Log::add(Text::sprintf('COM_JTICKETING_NO_JT_ITEMS_SELECTED', $plural_name), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Feature the items.
			try
			{
				$model->featured($cid, $value);
				$count = count($cid);

				// Multiple records.
				if ($count > 1)
				{
					if ($value === 1)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_FEATURED', $count, $plural_name);
					}
					elseif ($value === 0)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_UNFEATURED', $count, $plural_name);
					}
				}
				// Single record.
				else
				{
					if ($value === 1)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_FEATURED', $count, $singular_name);
					}
					elseif ($value === 0)
					{
						$ntext = Text::sprintf('COM_JTICKETING_N_JT_ITEMS_UNFEATURED', $count, $singular_name);
					}
				}

				$this->setMessage($ntext);
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect('index.php?option=com_jticketing&view=' . $currentListView);
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->getInput();
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
}
