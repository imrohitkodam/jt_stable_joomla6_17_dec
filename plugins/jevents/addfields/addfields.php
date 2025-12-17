<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$lang = Factory::getLanguage();
$lang->load('plg_jevents_addfields', JPATH_ADMINISTRATOR);
$lang->load('com_jticketing', JPATH_SITE);
$mainframe = Factory::getApplication();

JHtml::script(Juri::root() . 'media/com_jticketing/integrations/js/integrations.js');
JHtml::script(Juri::root() . 'media/com_jticketing/js/jticketing.js');

/**
 * Class for adding ticket types in JTicketing
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class PlgJeventsaddFields extends CMSPlugin
{
	public $accessLevel;
	
	/**
	 * function to validate Integration
	 *
	 * @return  boolean  true or false
	 *
	 * @since   1.0
	 */
	public function onJtValidateIntegration()
	{
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration != 3)
		{
			return false;
		}

		return true;
	}

	/**
	 * This is called when jomsocial event is stored
	 *
	 * @param   object  &$extraTabs  tabs
	 * @param   object  &$att        attributes
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventEdit(&$extraTabs, &$att)
	{
		$document   = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'media/com_jticketing/css/jticketing.css');
		$app = Factory::getApplication();
		$site = $app->isClient("site");
		$event_id     = 0;

		if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
		{
			require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
			TjStrapper::loadTjAssets('com_jticketing');
		}

		if (!$this->onJtValidateIntegration())
		{
			return false;
		}

		if ($att->ev_id)
		{
			$db  = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('rep.rp_id'));
			$query->from($db->quoteName('#__jevents_repetition', 'rep'));
			$query->where($db->quoteName('rep.eventid') . ' = ' . (int) $att->ev_id);
			$db->setQuery($query);

			$rpId = $db->loadResult();
			$event_id = $rpId;
		}

		$lang = Factory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir = JPATH_ADMINISTRATOR;
		$lang->load($extension, $base_dir);
		$this->onLoadJTclasses();
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$com_params = ComponentHelper::getParams('com_jticketing');
		$attendeeCheckoutConfig = $com_params->get('collect_attendee_info_checkout');
		$this->accessLevel = $com_params->get('show_access_level');

		if (!$this->accessLevel)
		{
		?>
		<style>
		.subform-repeatable-group .form-group:last-child{
		display: none;
		}
		</style>
		<?php
		}

		$userId     = Factory::getUser()->id;
		JLoader::register('TJVendors', JPATH_SITE . "/components/com_tjvendors/includes/tjvendors.php");
		$vendor     = TJVendors::vendor()->loadByUserId($userId, JT::getIntegration());
		$vendor_id  = $vendor->getId();
		$emailCheck = $vendor->getPaymentConfig() ? true : false;
		$params     = JT::config();
		$handle_transactions = $params->get('handle_transactions');
		$adaptivePayment     = $params->get('gateways');

		$document   = Factory::getDocument();
		$eventStartDateMsg = Text::_('COM_JTICKETING_STARTDATE_VALIDATION');
		$eventDateMsg = Text::_('COM_JTICKETING_ENDDATE_VALIDATION');
		$document->addScriptDeclaration('var eventDateMsg= "' . $eventDateMsg . '";');
		$document->addScriptDeclaration('var eventStartDateMsg= "' . $eventStartDateMsg . '";');

		if ($emailCheck == "true" && ($handle_transactions == 1 || in_array('adaptive_paypal', $adaptivePayment)))
		{
		?>
			<div class="alert alert-warning">
			<?php
				if ($site)
				{
					$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jticketing';
				}
				else
				{
					$link = 'index.php?option=com_tjvendors&view=vendor&layout=update&client=com_jticketing';
				}

				echo Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');?>
					<a href="<?php echo Route::_($link . '&vendor_id=' . $vendor_id, false);?>" target="_blank">
					<?php echo Text::_('COM_JTICKETING_VENDOR_FORM_LINK'); ?></a>
				<?php echo Text::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2');?>
				</div>
		<?php
		}

		$customFields = array();

		if (!$event_id) 
		{

			return false;
		}

		$customTicketFields = JT::event($event_id, 'com_jevents')->getCustomFieldTypes('ticketFields');

		if ($site)
		{
			$customFields['ticketFields'] = '<div class="jticketing-wrapper">
			<div class="jticketing_params_container">
				<div>' . $customTicketFields . '</div>
			</div>
		</div>';
		}
		else
		{
			$customFields['ticketFields'] = $customTicketFields;
		}

		$extraTab['title']   = Text::_("ADD_TICKET");
		$extraTab['paneid']  = 'jt_ticket_types';
		$extraTab['content'] = $customFields['ticketFields'];
		$extraTabs[]         = $extraTab;

		if ($attendeeCheckoutConfig == 1)
		{
			$customAttendeeFields = JT::event($event_id, 'com_jevents')->getCustomFieldTypes('attendeeFields');

			if ($site)
			{
				$customFields['attendeeFields'] = '<div class="jticketing-wrapper">
					<div class="jticketing_params_container">
						<div>' . $customAttendeeFields . '</div>
					</div>
				</div>';
			}
			else
			{
				$customFields['attendeeFields'] = $customAttendeeFields;
			}

			$extraTab['title']   = Text::_('COM_JTICKETING_EVENT_TAB_EXTRA_FIELDS_ATTENDEE');
			$extraTab['paneid']  = 'jt_attendee_fields';
			$extraTab['content'] = $customFields['attendeeFields'];
			$extraTabs[]         = $extraTab;
		}
	}

	/**
	 * This is called on before saving event
	 *
	 * @param   object  $typedetail  typedetail
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeSaveEvent($typedetail)
	{
		// Validate JEvents integration.
		if (!$this->onJtValidateIntegration())
		{
			return false;
		}

		$Session = Factory::getSession();

		if (!empty($typedetail))
		{
			$Session->set('typedetail', $typedetail);
		}

		$typea = $Session->get('typedetail');
	}

	/**
	 * This is called when after saving event
	 *
	 * @param   object  $aftersave  aftersave
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterSaveEvent($aftersave)
	{
		// To check jsevents id
		if (!$aftersave->_repetitions['0']->rp_id)
		{
			return false;
		}

		if (!$this->onJtValidateIntegration())
		{
			return false;
		}

		$this->onLoadJTclasses();
		$jteventHelper = new jteventHelper;

		$jteventHelper->saveEvent($aftersave->_repetitions['0']->rp_id, '3');
	}

	/**
	 * This is called when after saving event
	 *
	 * @param   object  $event  event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventUpdate($event)
	{
		// Validate JEvents integration.
	}

	/**
	 * This function updates jomsocial table
	 *
	 * @param   object  $event  event object passed from jomsocial
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onEventJoin($event)
	{
		// Validate JEvents integration.
		if (!$this->onJtValidateIntegration())
		{
			return false;
		}

		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query->select('*');
		$query->from($db->quoteName('#__community_events_members'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event->id));
		$query->where($db->quoteName('memberid') . ' = ' . $db->quote($user->id));
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		if ($event->creator == $user->id)
		{
			$eventDetails = JT::event($event->id, JT::getIntegration());

			if (!$eventDetails)
			{
				return '';
			}

			$tickets = $eventDetails->getTicketTypes();

			if (count($tickets) == 1)
			{
				$typeid = $tickets[0]->id;
			}
			else
			{
				usort(
					$tickets,
					function($ticket1, $ticket2)
					{
						return $ticket1->price > $ticket2->price;
					}
				);

				$typeid  = end($tickets)->id;
			}

			if ($event->confirmedcount == 1)
			{
				$fields = array($db->quoteName('count') . ' = 1');
				$conditions = array($db->quoteName('id') . ' = ' . $db->quote($typeid));
				$query->update($db->quoteName('#__jticketing_types'))->set($fields)->where($conditions);
			}
			else
			{
				$fields = array($db->quoteName('count') . ' = count+1');
				$conditions = array($db->quoteName('id') . ' = ' . $db->quote($typeid));
				$query->update($db->quoteName('#__jticketing_types'))->set($fields)->where($conditions);
			}

			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * This function updates jomsocial table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onLoadJTclasses()
	{
		require_once JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';
		require_once JPATH_SITE . '/components/com_tjvendors/includes/tjvendors.php';

		// Load all required helpers.
		$jticketingmainhelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $jticketingmainhelperPath);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jteventHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}
	}
}
