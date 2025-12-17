<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Methods supporting a list of ticket types.
 *
 * @since  2.5.0
 */
class JticketingModelTickettypes extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   2.5.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'id', 'a.id',
			'title', 'a.title',
			'desc', 'a.desc',
			'ticket_enddate', 'a.ticket_enddate',
			'ticket_startdate', 'a.ticket_startdate',
			'price', 'a.price',
			'deposit_fee', 'a.deposit_fee',
			'available', 'a.available',
			'count', 'a.count',
			'unlimited_seats','a.unlimited_seats',
			'eventid', 'a.eventid',
			'max_limit_ticket', 'a.max_limit_ticket',
			'access', 'a.access',
			'state', 'a.state'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get the query for retrieving a list of ticket types to the model state.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   2.5.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'DISTINCT a.*'));
		$query->from('`#__jticketing_types` AS a');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' OR a.desc LIKE ' . $search .
				' )');
			}
		}

		// Filters
		if ($this->getState('filter.eventid'))
		{
			$query->where('a.eventid = ' . (int) $this->getState("filter.eventid"));
		}

		// Filtering by State
		if ($this->getState('filter.state'))
		{
			$query->where('a.state = ' . (int) $this->getState("filter.state"));
		}

		// Filtering by end Date
		if ($this->getState('filter.ticket_enddate'))
		{
			$query->where('(a.ticket_enddate >= ' . $db->quote($this->getState("filter.ticket_enddate")) . ' OR a.ticket_enddate is NULL)');
		}

		// Filtering by start Date
		if ($this->getState('filter.ticket_startdate'))
		{
			$query->where('(a.ticket_startdate <= ' . $db->quote($this->getState("filter.ticket_startdate")) . ' OR a.ticket_startdate is NULL)');
		}

		// Filtering by access
		if ($this->getState('filter.access') != '')
		{
			if (JT::config()->get('ticket_access') == 'exclude')
			{
				$query->where($db->qn('a.access') . "!= " . max(explode(',', str_replace("'", '', $this->getState("filter.access")))));
			}
			else
			{
				$query->where($db->qn('a.access') . "IN ('" . ($this->getState("filter.access")) . "')");
			}
		}

		// Filtering by available
		if ($this->getState('filter.available') != '')
		{
			$query->where('(' . $db->qn('a.count') . '>= 1 OR ' . $db->qn('a.unlimited_seats') . '= 1)');
		}

		return $query;
	}

	/**
	 * Method to generate pdf from html
	 *
	 * @param   string  $html      replacement data for tags
	 * @param   string  $pdffile   name of pdf file
	 * @param   string  $download  int
	 *
	 * @return  string  html with css applied
	 *
	 * @since  3.2.0
	 */
	public function generatepdf($html, $pdffile, $download = 0)
	{

		$params      = JT::config();
		$pageSize    = $params->get('ticket_page_size', 'A4');
		$orientation = $params->get('ticket_orientation', 'portrait');

		// If the pagesize is custom then get the correct size and width.
		if ($pageSize === 'custom')
		{
			$height   = $params->get('ticket_pdf_width', '15') * 28.3465;
			$width    = $params->get('ticket_pdf__height', '400') * 28.3465;
			$pageSize = array(0, 0, $width, $height);
		}

		$fontFamily   = $params->get('pdf_ticket_font', 'dejavu sans');;

		require_once  JPATH_SITE . "/libraries/techjoomla/dompdf/autoload.inc.php";

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html><head><meta http-equiv="Content-Type" content="charset=utf-8" />
			<style type="text/css">* {font-family: "'. $fontFamily .'" !important}</style></head><body>' . $html . '</body></html>';

		$html = stripslashes($html);

		// Set font for the pdf download.
		$options = new Options;
		$options->setDefaultFont($fontFamily);
		$options->set('isRemoteEnabled', true);

		$dompdf = new DOMPDF($options);
		$dompdf->loadHTML($html, 'UTF-8');

		// Set the page size and oriendtation.
		$dompdf->setPaper($pageSize, $orientation);

		$dompdf->render();
		$output = $dompdf->output();
		file_put_contents($pdffile, $output);

		if ($download == 1)
		{
			header('Content-Description: File Transfer');
			header('Cache-Control: public');
			header('Content-Type: ' . $type);
			header("Content-Transfer-Encoding: binary");
			header('Content-Disposition: attachment; filename="' . basename($pdffile) . '"');
			header('Content-Length: ' . filesize($pdffile));
			ob_clean();
			flush();
			readfile($pdffile);
			jexit();
		}

		return $pdffile;
	}
}
