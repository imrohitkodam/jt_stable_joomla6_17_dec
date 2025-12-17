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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class is for email config
 *
 * @since  1.0.0
 */
class JticketingModelEmail_Config extends BaseDatabaseModel
{
/**
 * Function saves configuration data to a file
 * 
 * @return  String
 *
 * @since	1.0
 */
	public function store()
	{
		$app 	  = Factory::getApplication();
		$input    = $app->input;
		$config   = $input->post->get('data', '', 'ARRAY');

		$file     = JPATH_ADMINISTRATOR . "/components/com_jticketing/config.php";
		$msg 	  = '';
		$msg_type = '';

		if ($config)
		{
			$template_css = $config['template_css'];
			unset($config['template_css']);

			$file_contents = "<?php \n\n";
			$file_contents .= "\$emails_config=array(\n" . $this->row2text($config) . "\n);\n";
			$file_contents .= "\n?>";

			if (File::write($file, $file_contents))
			{
				$msg = Text::_('CONFIG_SAVED');
			}
			else
			{
				$msg = Text::_('CONFIG_SAVE_PROBLEM');
				$msg_type = 'error';
			}

			$cssfile = JPATH_SITE . "/components/com_jticketing/assets/css/email.css";
			File::write($cssfile, $template_css);
		}

		$app->enqueueMessage($msg, $msg_type);
		$app->redirect('index.php?option=com_jticketing&view=email_config');
	}
	// Store() ends

/**
 * This formats the data to be stored in the config file
 *
 * @param   ARRAY  $row    row.
 * 
 * @param   ARRAY  $dvars  dvars.
 * 
 * @return  String
 *
 * @since	1.0
 */
	public function row2text($row,$dvars=array())
	{
		reset($dvars);

		foreach ($dvars as $idx => $var)
		{
			unset($row[$var]);
		}

		$text = '';
		reset($row);
		$flag = 0;
		$i = 0;

		foreach ($row as $var => $val)
		{
			if ($flag == 1)
			{
				$text .= ",\n";
			}
			elseif ($flag == 2)
			{
				$text .= ",\n";
			}

			$flag = 1;

			if (is_numeric($var))
			{
				if ($var[0] == '0')
				{
					$text .= "'$var'=>";
				}
				else
				{
					if ($var !== $i)
					{
						$text .= "$var=>";
					}

					$i = $var;
				}
			}
			else
			{
				$text .= "'$var'=>";
			}

			$i++;

			if (is_array($val))
			{
				$text .= "array(" . $this->row2text($val, $dvars) . ")";
				$flag = 2;
			}
			else
			{
				$text .= "\"" . addslashes($val) . "\"";
			}
		}

		return($text);
	}
}
