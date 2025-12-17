<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

if(!defined('DS')){
       define('DS',DIRECTORY_SEPARATOR);
}

$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (File::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}


?>

<div class="techjoomla-bootstrap">
<?php

if($recentlikes){
foreach($recentlikes as $likedata)
{
?>
		<div class="well" style="margin:1px;">
			<div>
					<a href="<?php echo $likedata->url;?>"><?php echo $likedata->title;?></a>
			</div>
			<div style="float:right;">
				<?php echo Text::sprintf( 'MOD_JLIKE_CREATED_DATE', date('d-m-Y H:i A',$likedata->likedate) );	?>
			</div>
			<div style="clear:both"></div>
		</div>
<?php
}
}
else
{?>
		<div class="alert alert-info"><?php echo Text::_('NO_DATA');?></div>
<?php
}
?>
</div>


