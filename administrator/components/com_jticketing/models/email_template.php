<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class for email template
 *
 * @since  1.6
 */
class JticketingModelEmail_Template extends BaseDatabaseModel
{
	/**
	 * Method to store email template
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function store()
	{
		$app      = Factory::getApplication();
		$input    = Factory::getApplication()->getInput();
		$config   = Factory::getApplication()->getInput()->get('data', '', 'post', 'array', JREQUEST_ALLOWHTML);
		$file     = JPATH_ADMINISTRATOR . "/components/com_jticketing/email_template.php";
		$msg      = '';
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
				$msg      = Text::_('CONFIG_SAVE_PROBLEM');
				$msg_type = 'error';
			}

			$cssfile = JPATH_SITE . "/components/com_jticketing/assets/css/email_template.css";
			File::write($cssfile, $template_css);
		}

		$app->enqueueMessage($msg, $msg_type);
		$app->redirect('index.php?option=com_jticketing&view=email_template');
	}

	/**
	 * Method to get data
	 *
	 * @param   array  $row    row for template
	 * @param   array  $dvars  dvars
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function row2text($row, $dvars = array())
	{
		reset($dvars);

		foreach ($dvars as $idx => $var)
		{
			unset($row[$var]);
		}

		$text = '';
		reset($row);
		$flag = 0;
		$i    = 0;

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

		return ($text);
	}
}
