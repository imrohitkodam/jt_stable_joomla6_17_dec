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


// Load frontend venueform model
if (file_exists(JPATH_SITE . '/components/com_jticketing/controllers/venueform.php')) { require_once JPATH_SITE . '/components/com_jticketing/controllers/venueform.php'; }
if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }

/**
 * Venue controller class.
 *
 * @since  1.6
 */
class JticketingControllerVenue extends JticketingControllerVenueForm
{
}
