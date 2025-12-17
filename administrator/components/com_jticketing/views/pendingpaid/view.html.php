<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
Use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;

class jticketingViewpendingpaid extends HtmlView
{
  function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		if(JVERSION>=3.0)
		{
			HTMLHelper::_('bootstrap.tooltip');
			HTMLHelper::_('behavior.multiselect');
			// Joomla 6: formbehavior.chosen removed - using native select
		}
		$input=Factory::getApplication()->getInput();
		$this->jticketingmainhelper=new jticketingmainhelper();
		global $mainframe, $option;
		$mainframe = Factory::getApplication();
 		$option = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event','', 'string' );
		$search_event = StringHelper::strtolower( $search_event );
		$user=Factory::getUser();
		$layout=Factory::getApplication()->getInput()->get('layout','default');

		$status_event = array();
		$eventlist=$this->jticketingmainhelper->geteventnamesByCreator();
		if(JVERSION<3.0)
		$status_event[] = HTMLHelper::_('select.option','', Text::_('SELONE_EVENT'));

		if(!empty($eventlist))
		{
			foreach($eventlist as $key=>$event)
			{
				$event_id=$event->id;
				$event_nm=$event->title;
				if($event_nm)
				$status_event[] = HTMLHelper::_('select.option',$event_id, $event_nm);
			}
		}

		$model = $this->getModel();
		$eventid = Factory::getApplication()->getInput()->get('event');
		$this->status_event=$status_event;

		$this->user_filter_options=$this->get('UserFilterOptions');

  		$user_filter=$mainframe->getUserStateFromRequest('com_jticketing'.'user_filter','user_filter');

		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir','filter_order_Dir','desc','word');
		$filter_type=$mainframe->getUserStateFromRequest('com_jticketing.filter_order','filter_order','id','string');

		$lists['search_event']     = $search_event;

		$Data 	=$this->get('Data');
		foreach($Data as &$data)
		{

			$data->pendingcount=$model->pendingcount($data->eventid);
			$data->confirmcount=$model->confirmcount($data->eventid);
		}

		$pagination =$this->get('Pagination');

 		$Itemid = $input->get('Itemid');
		if(empty($Itemid))
		{
			$Session=Factory::getSession();
			$Itemid=$Session->get("JT_Menu_Itemid");
		}
		$this->Data=$Data;
		$this->pagination=$pagination;
		$this->lists=$lists;
		$this->Itemid=$Itemid;
		$this->status_event=$status_event;



		$title='';
		$lists['order_Dir']='';
		$lists['order']='';
		$title=$mainframe->getUserStateFromRequest('com_jticketing'.'title','', 'string' );
		 if($title==null){
			$title='-1';
		   }
 		$lists['title']=$title;
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_type;
		$lists['pagination']=$pagination;

		$lists['user_filter']=$user_filter;
		$this->lists=$lists;

		$JticketingHelper=new JticketingHelperadmin();
		$JticketingHelper->addSubmenu('pendingpaid');


		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0' && JVERSION < '4.0')
		{	JHtmlBehavior::framework();

		}
		else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '3.0')
			HTMLHelper::_('behavior.mootools');
		$this->setToolBar();

		if(JVERSION>='3.0')
		$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		$this->setLayout($layout);

		parent::display($tpl);
	}

	function setToolBar()
	{
		ToolbarHelper::title(Text::_('COM_USERS_VIEW_USERS_TITLE'), 'user');
		$document =Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/css/jticketing.css');
		$bar =ToolBar::getInstance('toolbar');
		ToolbarHelper::title( Text::_( 'COM_JTICKETING_PENDING_PAID_VIEW' ), 'icon-48-jticketing.png' );
			if(JVERSION>=3.0)
				ToolbarHelper::custom(Text::_('COM_JTICKETING_COMPONENT') . 'csvexport', 'icon-32-save.png', 'icon-32-save.png',Text::_("CSV_EXPORT"), false);
			else{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'csvexport';document.getElementById('controller').value = 'pendingpaid';document.adminForm.submit();\" ><span class='icon-32-save' title='Export'></span>".Text::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}
		ToolbarHelper::back( Text::_('JT_HOME') , 'index.php?option=com_jticketing&view=cp');
		//ToolbarHelper::deleteList('', 'remove','JTOOLBAR_DELETE');

		$layout=Factory::getApplication()->getInput()->get('layout','default');

		if(JVERSION>=3.0 and $layout=='default')
		{
			// Joomla 6: HTMLHelperSidebar::setAction() removed

			HTMLHelperSidebar::addFilter(
				Text::_('SELONE_EVENT'),
				'search_event',
				HTMLHelper::_('select.options', $this->status_event, 'value', 'text', $this->lists['search_event'], true)

			);
		}

		ToolbarHelper::preferences('com_jticketing');
	}
}
?>
