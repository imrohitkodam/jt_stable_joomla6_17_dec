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
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

if (file_exists(JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php')) { require_once JPATH_SITE . '/components/com_tjvendors/helpers/fronthelper.php'; }
if (file_exists(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php')) { require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables/vendorclientxref.php'; }

/**
 * Couponform view class.
 *
 * @since  2.4.0
 */
class JticketingViewCouponform extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * The coupon object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The user object
	 *
	 * @var  \JUser|null
	 */
	protected $user;

	/**
	 * JTRouteHelper helper handle itemid
	 */
	protected $JTRouteHelper;

	/**
	 * Constructor
	 *
	 * @since  2.4.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  An optional associative array.
	 *
	 * @return  mixed
	 *
	 * @since  2.4.0
	 */
	public function display($tpl = null)
	{
		$app              = Factory::getApplication();
		$this->user       = Factory::getUser();
		$this->state      = $this->get('State');
		$this->item       = $this->get('Item');
		$this->form       = $this->get('Form');
		$this->params = ComponentHelper::getParams('com_jticketing');

		// Validate user login.
		if (!$this->user->id)
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		/* Checking for new coupon creation checking create access
		else for edit case checking is edit own coupon or have admin access*/
		$authorised = (!isset($this->item->id) && $this->user->authorise('coupon.create', 'com_jticketing')) ||
			($this->user->authorise('coupon.edit', 'com_jticketing') && ($this->item->created_by == $this->user->id));

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Check loggded in user is vendor.
		$tjvendorFrontHelper = new TjvendorFrontHelper;
		$isVendor            = $tjvendorFrontHelper->checkVendor('', 'com_jticketing');
		$silentVendor        = $this->params->get('silent_vendor', 0, 'INTEGER');

		$vendorXrefTable = Table::getInstance('vendorclientxref', 'TjvendorsTable', array());
		$vendorXrefTable->load(array('vendor_id' => $isVendor, 'client' => 'com_jticketing'));
		$isVendorApproved = $vendorXrefTable->approved;

		if ($isVendor)
		{
			if ($isVendorApproved != 1 && $silentVendor == 0)
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_VENDOR_NOT_APPROVED_MESSAGE'), 'notice');

				return false;
			}

			// Check is vendor unpublished.
			if ($vendorXrefTable->state != 1)
			{
				$app->enqueueMessage(Text::_('COM_JTICKETING_VENDOR_UNPUBLISHED'), 'warning');

				return false;
			}
		}
		else
		{
			$app->enqueueMessage(
			Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_ERROR') . Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_MESSAGE'), 'notice'
			);
		?>
			<a href="<?php echo Route::_('index.php?option=com_tjvendors&view=vendor&layout=edit&client=com_jticketing');?>"
				target="_blank"
				class="btn btn-primary">
				<?php echo Text::_('COM_JTICKETING_VENDOR_ENFORCEMENT_EVENT_REDIRECT_LINK'); ?>
			</a>
		<?php
			return false;
		}

		parent::display($tpl);
	}
}
