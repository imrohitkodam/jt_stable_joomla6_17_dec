<?php
/**
 * @package     Jlike
 * @subpackage  com_jlike
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

if (isset($getRatingAvg) && $getRatingAvg)
{
	$getRatingAvg = $getRatingAvg;
}
else
{
	$getRatingAvg = 0;
}
?>

<div>
	<!-- ***************************** Rating ********************** -->
	<span id="" class="">
	<div class="basic_avg" data-average="<?php echo $getRatingAvg; ?>" data-id="1"></div>
	</span>
	<!-- ***************************** Rating ********************** -->
</div>
