<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View for Categories
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewCategories extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->utilities = JT::utilities();

		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$menus = $app->getMenu();
		$model = $this->getModel('categories');
		$this->params        = $app->getParams('com_jticketing');
		$JticketingCategoriesPath = JPATH_SITE . '/components/com_jticketing/helpers/category.php';
		$this->items         = $this->get('Items');
		$menuParams = $app->getParams('com_jticketing');
		$show_child_categories = $menuParams->get('show_child_categories');
		$this->categoryItemId = JT::utilities()->getItemId('index.php?option=com_jticketing&view=categories&layout=default');

		if (!$show_child_categories)
		{
			$catId       = $app->getInput()->get('cat_id', '', 'INT');
			if (empty($this->items) && $catId)
			{
				$itemId  = $this->utilities->getItemId('index.php?option=com_jticketing&view=events');
				$app->redirect(Route::_('index.php?option=com_jticketing&view=events&layout=default&catid=' . $catId . '&Itemid=' . $itemId, false));
			}
		}

		parent::display($tpl);
	}
}
