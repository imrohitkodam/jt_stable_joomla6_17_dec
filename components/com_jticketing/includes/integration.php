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
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

/**
 * JTicketing integration class for integration methods
 *
 * @since  3.2.0
 */
class JTicketingIntegration
{
	/**
	 * Get user Avatar
	 *
	 * @param   integer  $userid    userid
	 * @param   integer  $relative  relative
	 *
	 * @return  string  profile url
	 *
	 * @since   3.2.0
	 */
	public function getUserAvatar($userid, $relative = false)
	{
		$user                    = Factory::getUser($userid);
		$params                  = JT::config();
		$socialIntegrationOption = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');
		$gravatar                = $params->get('gravatar');
		$uimage                  = '';

		if ($socialIntegrationOption == "joomla")
		{
			if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/joomla.php'; }

			if ($gravatar)
			{
				$user     = Factory::getUser($userid);
				$usermail = $user->get('email');

				// Refer https://en.gravatar.com/site/implement/images/php/
				$hash     = md5(strtolower(trim($usermail)));
				$uimage   = 'http://www.gravatar.com/avatar/' . $hash . '?s=32';

				return $uimage;
			}
			else
			{
				if ($relative)
				{
					$uimage = 'media/com_jticketing/images/default_avatar.png';
				}
				else
				{
					Uri::root() . 'media/com_jticketing/images/default_avatar.png';
				}
			}
		}
		else
		{
			if ($socialIntegrationOption == "cb")
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/cb.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/cb.php'; }
				$sociallibraryclass = new JSocialCB;
			}
			elseif ($socialIntegrationOption == "jomsocial")
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/jomsocial.php'; }
				$sociallibraryclass = new JSocialJomsocial;
			}
			elseif ($socialIntegrationOption == "EasySocial")
			{
				if (file_exists(JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php')) { require_once JPATH_LIBRARIES . '/techjoomla/jsocial/easysocial.php'; }
				$sociallibraryclass = new JSocialEasysocial;
			}

			$uimage = $sociallibraryclass->getAvatar($user, '', $relative);
		}

		return $uimage;
	}

	/**
	 * Method to get jomsocial toolbar footer
	 *
	 * @return  html
	 *
	 * @since   3.2.0
	 */
	public function getJSfooter()
	{
		$params          = JT::config();
		$show_js_toolbar = $params->get('show_js_toolbar');
		$html            = '';

		if (file_exists(JPATH_SITE . '/components/com_community'))
		{
			// Load the local XML file first to get the local version
			$xml       = simplexml_load_file(JPATH_ROOT . '/administrator/components/com_community/community.xml');
			$jsversion = (float) $xml->version;

			if ($jsversion >= 2.4 and $show_js_toolbar == 1)
			{
				$html = '</div>';
			}
		}

		return $html;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function profileImport($paymentform = '')
	{
		$cdata['userbill'] = new stdclass;

		$helper      = new JTicketingIntegrationsHelper();
		$params      = JT::config();
		$socialIntegrationOption = $params->get('social_integration');

		if ($socialIntegrationOption == 'joomla')
		{
			$cdata = $this->joomlaProfileImport($paymentform);
		}
		elseif ($socialIntegrationOption == 'jomsocial')
		{
			$cdata = $this->jomsocialProfileImport($paymentform);
		}
		elseif ($socialIntegrationOption == 'cb')
		{
			$cdata = $helper->cbProfileImport($paymentform);
		}
		elseif ($socialIntegrationOption == 'EasySocial')
		{
			$cdata = $this->easySocialProfileImport($paymentform);
		}

		return $cdata;
	}

	/**
	 * Method to get jomsocial toolbar header
	 *
	 * @return  html
	 *
	 * @since   3.2.0
	 */
	public function getJSheader()
	{
		$com_params      = JT::config();
		$show_js_toolbar = $com_params->get('show_js_toolbar');
		$html            = '';

		// Newly added for JS toolbar inclusion
		if (file_exists(JPATH_SITE . '/components/com_community'))
		{
			// Load the local XML file first to get the local version
			$xml       = simplexml_load_file(JPATH_ROOT . '/administrator/components/com_community/community.xml');
			$jsversion = (float) $xml->version;

			if ($jsversion >= 2.4 and $show_js_toolbar == 1)
			{
				require_once JPATH_SITE . '/components/com_community/libraries/core.php';
				require_once JPATH_ROOT . '/components/com_community/libraries/toolbar.php';
				$toolbar = CFactory::getToolbar();
				$tool    = CToolbarLibrary::getInstance();
				$html    = '<div id="community-wrap">';
				$html .= $tool->getHTML();
			}
		}

		return $html;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  mixed
	 *
	 * @since   3.2.0
	 */
	public function easySocialProfileImport($paymentform = '')
	{
		$db     = Factory::getDbo();
		$params = JT::config();

		if (!ComponentHelper::isEnabled('com_easysocial', true))
		{
			echo Text::_('COM_JTICKETING_EASYSOCIAL_NOT_INSTALLED');

			return false;
		}

		$cdata             = array();
		$cdata['userbill'] = new stdclass;
		$mapping           = $params->get('easysocial_fieldmap');
		$mapping_field     = explode("\n", $mapping);
		$socialtypes       = '';

		foreach ($mapping_field as $each_field)
		{
			$field = explode("=", $each_field);

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$Esocial_field    = trim($field[1]);

				// Remove campalsory star
				$socialtypes .= "'" . trim(str_replace('*', '', $Esocial_field)) . "',";
			}
		}

		$socialtypes = substr($socialtypes, 0, -1);
		$userid      = Factory::getUser()->id;
		$query       = $db->getQuery(true);

		$query->select($db->quoteName(array('data', 'datakey')));
		$query->from($db->quoteName('#__social_fields_data'));
		$query->where($db->quoteName('uid') . ' = ' . $userid);
		$query->where($db->quoteName('datakey') . ' IN (' . $socialtypes . ')');
		$db->setQuery($query);
		$results = $db->loadObjectList('datakey');

		foreach ($results as $k => $row)
		{
			switch ($k)
			{
				case 'name':
					$name                         = explode(" ", $row->data);
					$cdata['userbill']->firstname = $first_name = $name['0'];
					$cnt                          = count($name);

					if ($cnt > 1)
					{
						$cdata['userbill']->lastname = $name['1'];
					}

					break;
				case 'address':
					$cdata['userbill']->address = $row->data;
					break;

				case 'address2':
					$cdata['userbill']->address2 = $row->data;
					break;
				case 'phon':
					$cdata['userbill']->phon = $row->textbox;
					break;

				case 'country':
					$cdata['userbill']->country      = $row->data;
					$country                         = "`country` LIKE '$row->data'";
					$cdata['userbill']->country_code = JT::utilities()->getCountry(null, $country)->country_code;
					break;

				case 'state':
					$state                         = "`region` LIKE '$row->data'";
					$cdata['userbill']->state_code = JT::utilities()->getRegion(null, $state)->region_code;
					break;

				case 'city':
					$cdata['userbill']->city = $row->data;
					break;

				case 'zip':
					$cdata['userbill']->zip = $row->data;
					break;
			}
		}

		// For payment_paymentform layout
		if ($paymentform)
		{
			$cdata['paypal_email'] = Factory::getUser()->email;
		}
		else
		{
			$cdata['userbill']->paypal_email = Factory::getUser()->email;
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  array
	 *
	 * @since   3.2.0
	 */
	public function jomsocialProfileImport($paymentform = '')
	{
		$cdata['userbill'] = new stdclass;
		$params            = JT::config();
		$jspath            = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';
		$userpro       = CFactory::getUser();
		$user          = CFactory::getUser();
		$userinfo      = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping       = $params->get('jomsocial_fieldmap');

		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field            = explode("=", $each_field);
			$jticketing_field = '';
			$jomsocial_field  = '';

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$jomsocial_field  = trim($field[1]);
				$jomsocial_field  = trim(str_replace(',*', '', $jomsocial_field));
			}

			if ($jomsocial_field != 'password')
			{
				if (array_key_exists($jomsocial_field, $userinfo))
				{
					if ($paymentform)
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata[$jticketing_field] = $userinfo[$jomsocial_field];
						}
					}
					else
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata['userbill']->$jticketing_field = $userinfo[$jomsocial_field];
						}
					}
				}
				else
				{
					$userInfo = $userpro->getInfo($jomsocial_field);

					if (!empty($userInfo))
					{
						if ($paymentform)
						{
							$cdata[$jticketing_field] = $userInfo;
						}
						else
						{
							$cdata['userbill']->$jticketing_field = $userInfo;
						}
					}
				}
			}
		}

		if (isset($cdata['userbill']->address))
		{
			$cdata['userbill']->address = json_decode($cdata['userbill']->address)->desc;
		}

		return $cdata;
	}

	/**
	 * This is used to generate custom fields
	 *
	 * @param   int  $fieldset  be it attendee fields or ticket types
	 *
	 * @param   int  $event_id  event id in case of edit event
	 *
	 * @param   int  $source    for multiple integrations
	 *
	 * @return  $html
	 *
	 * @since   3.2.0
	 */
	public function generateCustomFieldHtml($fieldset, $event_id, $source)
	{
		if ($source == "com_community")
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/jomsocial/eventjs.xml';
		}
		else if ($source == "com_jevents") 
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/jevents/eventint.xml';
		}
		else
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/integration/eventint.xml';
		}

		$form         = Form::getInstance('', $form_path);
		$ticketData   = array();
		$ticketFields = array();
		$attendeeData = array();

		// Load the subform as a separate instance
		$subformPath = JPATH_SITE . $form->getFieldAttribute('tickettypes', 'formsource');
		$subform = Form::getInstance('tickettypes', $subformPath);

		$jtConfig = JT::config();
		if (!$jtConfig->get('entry_number_assignment', 0,'INT'))
		{

			$subform->removeField('start_number_for_sequence');
			$subform->removeField('allow_ticket_level_sequence');
			$form->removeField('start_number_for_event_level_sequence');
			$form->setFieldAttribute('tickettypes', 'formsource', $subform->getXml()->asXML());
		}

		// Handle default for maximum_ticket_per_order
		$defaultMaxTickets = $jtConfig->get('max_noticket_peruserperpurchase', 8, 'INT'); // fallback default

		// Check if form field exists first
		if ($subform->getField('max_ticket_per_order'))
		{
			$currentValue = $subform->getValue('max_ticket_per_order');

			if (empty($currentValue) || !$currentValue)
			{
				// Set default value if not already set
				$subform->setFieldAttribute('max_ticket_per_order', 'default', $defaultMaxTickets);
				$form->setFieldAttribute('tickettypes', 'formsource', $subform->getXml()->asXML());
			}
		}

		HTMLHelper::_('script', 'system/core.js', false, true);
		HTMLHelper::_('behavior.formvalidator');
		JLoader::register('JticketingCommonHelper', JPATH_SITE . '/components/com_jticketing/helpers/common.php');
		JticketingCommonHelper::getLanguageConstant();

		$document = Factory::getDocument();
		$document->addScriptDeclaration('var root_url = "' . Uri::root() . '"');

		if (!empty($event_id))
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables', 'Tickettype');
			$xrefId                    = JT::event($event_id, "com_easysocial");
			$jticketingTickettypeModel = BaseDatabaseModel::getInstance('Tickettype', 'JticketingModel');
			$ticketData                = $jticketingTickettypeModel->getTicketTypes($xrefId->integrationId);
			$attendeeFieldModel        = BaseDatabaseModel::getInstance('Attendeefields', 'JTicketingModel');
			$attendeeCoreFieldModel    = BaseDatabaseModel::getInstance('Attendeecorefields', 'JTicketingModel');
			$eventFormModel            = BaseDatabaseModel::getInstance('EventForm', 'JTicketingModel');
			$alreadyBookedTickets      = $eventFormModel->getAllBookedTicketIds($event_id, 'com_jevents');

			if (count($alreadyBookedTickets))
			{
				$form->setFieldAttribute('start_number_for_event_level_sequence', 'readonly', 'true');

				$subformPath = JPATH_SITE . $form->getFieldAttribute('tickettypes', 'formsource');

				// Load the subform as a separate instance
				$subform = Form::getInstance('tickettypes', $subformPath);

				$subform->setFieldAttribute('start_number_for_sequence', 'readonly', 'true');
				$subform->setFieldAttribute('allow_ticket_level_sequence', 'readonly', 'true');
				$form->setFieldAttribute('tickettypes', 'formsource', $subform->getXml()->asXML());
			}

			if (isset($xrefId->integrationId))
			{
				$ticketData   = $jticketingTickettypeModel->getTicketTypes($xrefId->integrationId);
				$attendeeData = $attendeeCoreFieldModel->getAttendeeFields($xrefId->integrationId);
			}


			$customFieldData = array();
			$startNumberForEventLevelSequence = '1';
			$startNumberValueSet = false;

			foreach ($ticketData as $key => $ticket)
			{
				$ticketFields["tickettypes" . $key] = $jticketingTickettypeModel->getItem($ticket['id']);
				if (!$startNumberValueSet && !$ticketFields["tickettypes" . $key]->allow_ticket_level_sequence && $ticketFields["tickettypes" . $key]->start_number_for_sequence)
				{
					$startNumberValueSet = true;
					$startNumberForEventLevelSequence = $ticketFields["tickettypes" . $key]->start_number_for_sequence;
				}
			}

			$customTicketFieldData[] = array("tickettypes" => $ticketFields, 'start_number_for_event_level_sequence' => $startNumberForEventLevelSequence);

			if (!empty($attendeeData))
			{
				foreach ($attendeeData as $key => $attendee)
				{
					$attendeeFields["attendeefields" . $key] = (array) $attendeeFieldModel->getItem($attendee['id']);
				}

				$customAttendeeFieldData[] = array("attendeefields" => $attendeeFields);
				$form->bind($customAttendeeFieldData);
			}

			$form->bind($customTicketFieldData);
		}

		$fieldSet = $form->getFieldset($fieldset);
		$html = array();

		foreach ($fieldSet as $field)
		{
			if ($field->name == "tickettypes" || $field->name == "attendeefields")
			{
				if (Factory::getApplication()->isClient("administrator"))
				{
					$bsVersion = (JVERSION < '4.0.0') ? "bs3" : "bs5";
				}
				else
				{
					$bsVersion = JT::config()->get('bootstrap_version');
				}

				$field->layout = "JTsubformlayouts.layouts." . $bsVersion . ".subform.repeatable";
			}

			$html[] = $field->renderField();
		}

		$returnHtml = implode('', $html);

		if ($source == "com_easysocial")
		{
			if (Factory::getApplication()->isClient("administrator"))
			{
				if (str_contains($returnHtml, 'updateFormGroupToOFormGroup'))
				{
					// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
					{
						$returnHtml = str_replace('input-prepend', 'o-input-group', $returnHtml);
						$returnHtml = str_replace('input-append', 'o-input-group', $returnHtml);
						$returnHtml = str_replace('add-on', 'o-input-group__addon', $returnHtml);
						$returnHtml = str_replace('icon-calendar', 'far fa-calendar-alt', $returnHtml);
					}
					else 
					{
						$returnHtml = str_replace('input-group', 'o-input-group', $returnHtml);
						$returnHtml = str_replace('o-input-group-text', 'o-input-group__addon', $returnHtml);
					}
				}
			}
			else 
			{
				// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
				{
					$returnHtml = str_replace('icon-calendar', 'far fa-calendar-alt', $returnHtml);
				}
				else 
				{
					$returnHtml = str_replace('input-group', 'o-input-group', $returnHtml);
					$returnHtml = str_replace('o-input-group-text', 'o-input-group__addon', $returnHtml);
				}
			}
		}
		else if ($source == "com_community") 
		{
			// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
			{
				$returnHtml = str_replace('icon-calendar', 'fa fa-calendar', $returnHtml);
				$returnHtml = str_replace('removeWidthAndDisplayinline', 'removeWidthAndDisplayinline displayinline', $returnHtml);
				$returnHtml = str_replace(' removeWidth ', ' ', $returnHtml);
			}
			else 
			{
				$returnHtml = str_replace('<small class="form-text">', '<small class="form-text mt-2 mb-2">', $returnHtml);
				$returnHtml = str_replace('id="attendeefields-desc"', 'id="attendeefields-desc" class="mt-4"', $returnHtml);
			}
		}
		
		return $returnHtml;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function joomlaProfileImport($paymentform = '')
	{
		$cdata['userbill'] = new stdclass;
		$params            = JT::config();
		$user              = Factory::getuser();
		$userinfo          = ArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile      = UserHelper::getProfile($user->id);
		JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
		$customFields = FieldsHelper::getFields('com_users.user', $user, true);

		// Convert object to array
		$user_profile  = ArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping       = $params->get('fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field            = explode("=", $each_field);
			$jticketing_field = '';
			$joomla_field     = '';

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$joomla_field     = trim($field[1]);
				$joomla_field     = trim(str_replace(',*', '', $joomla_field));
			}

			if ($joomla_field != 'password')
			{
				if (array_key_exists($joomla_field, $userinfo))
				{
					if ($paymentform)
					{
						$cdata[$jticketing_field] = $userinfo[$joomla_field];
					}
					else
					{
						$cdata['userbill']->$jticketing_field = $userinfo[$joomla_field];
					}
				}
				else if (!empty($user_profile['profile']))
				{
					if (array_key_exists($joomla_field, $user_profile['profile']))
					{
						if ($paymentform)
						{
							$cdata[$jticketing_field] = $user_profile['profile'][trim($joomla_field)];
						}
						else
						{
							$cdata['userbill']->$jticketing_field = $user_profile['profile'][trim($joomla_field)];
						}
					}
				}
				else if (!empty($customFields))
				{
					foreach ($customFields as $customField)
					{
						if ($joomla_field == $customField->name)
						{
							if ($paymentform)
							{
								$cdata[$jticketing_field] = $customField->value;
							}
							else
							{
								$cdata['userbill']->$jticketing_field = $customField->value;
							}
						}
					}
				}
			}
		}

		return $cdata;
	}
}
