<?php
/**
 * @package    JTicketing
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com*
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

 // No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

HTMLHelper::_('bootstrap.tooltip');

$document = Factory::getDocument();
$renderer = $document->loadRenderer('module');
$com_params   = ComponentHelper::getParams('com_jticketing');
$modules  = ModuleHelper::getModules('tj-filters-mod-pos');

if ($modules)
{
	$moduleParams = new Registry($modules['0']->params);
	$params   = array();
	if ($this->params->get('show_filters') == 1)
	{
		if ($moduleParams->get('client_type') == "com_jticketing.event")
		{
			foreach ($modules as $module)
			{
				echo $renderer->render($module, $params);
			}
		}
		else
		{
			echo Text::_('COM_JTICKETING_EVENT_NOTICE');
		}
	}
}
elseif ($this->params->get('show_filters') === 'both' || $this->params->get('show_filters') === 'advanced')
{
?>
	<!--Category Filter code for core filters-->
	<?php
		/* Core Category Filter code ended*/
		$basePath = JPATH_SITE . '/components/com_jticketing';
		$layout = new FileLayout('corefilters.horizontal.' . JTICKETING_LOAD_BOOTSTRAP_VERSION . '.corefilters', $basePath);
		$data = "";
		echo $layout->render($data);
		$selected = null;

		if ($this->params->get('show_category_filter') == 'advanced')
		{
			?>
			<div class="col-xs-12 col-sm-3 col-md-2 eventFilter__ht af-mb-10">
				<div>
					<?php echo HTMLHelper::_('select.genericlist', $this->cat_options, "filter_events_cat", 'class="form-control" size="1" onchange="this.form.submit();"
						name="filter_events_cat"', "value", "text", $this->lists['filter_events_cat']
						);
					?>
				</div>
			</div>
			<?php
		}
}
