<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   ce_sql
 * @author    Thomas Pahl
 * @license   GNU/LGPL
 * @copyright Fleckwerk
 */


/**
 * Namespace
 */
namespace Fleckwerk;


/**
 * Class ContentSql
 *
 * @copyright  Fleckwerk
 * @author     Thomas Pahl
 * @package    ce_sql
 */
class ContentSql extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_sql_table';

	/**
	 * Formatting: class for numeric columns
	 * @var string
	 */
	public $classNumeric = 'numeric';

	/**
	 * Formatting: Formats for some standard columns
	 * @var array
	 *
	 * Valid values: date,time,datetime,email,url
	 */
	public $stdFormat = array(
		'tstamp'=>'datetime',
		'deadline'=>'datetime',
		'date'=>'date',
		'time'=>'time',
		'dateAdded'=>'datetime',
		'lastLogin'=>'datetime',
		'currentLogin'=>'datetime',
		'email'=>'email',
		'website'=>'url',
		'uuid'=>'uuid'
		);

	/*
	 * Formatting: Substitute for NULL or empty value
	 * @var string
	 */
	public $nullValue = '&nbsp;';
	
	/**
	 * Generate the element HTML!
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			return '<pre>' . $this->ce_sql_query . '</pre>';
		}
		if (!empty($this->ce_sql_template))
		{
			$this->strTemplate = $this->ce_sql_template;
		}
		return parent::generate();
	}

	/**
	 * Compile the result data
	 */
	protected function compile()
	{
		$strQuery = $this->ce_sql_query;

		/**
		 * Validate SELECT statement
		 */
		if (strtolower(substr($strQuery,0,6)) != 'select')
		{
			return;
		}
		// shoulds also test for multiple statements...

		/**
		 * Apply insert tags
		 */
		if (strpos($strQuery, '{{') !== false)
		{
			$strQuery = $this->replaceInsertTags($strQuery);
		}
		
		/**
		 * Get the selected records
		 */
		$objDataStmt = $this->Database->prepare($strQuery);
		$objData = $objDataStmt->execute($varKeyword);


		/**
		 * Prepare the data arrays
		 */
		$arrTh = array();
		$arrTd = array();
		$arrFields = array();			// will collect the column names/aliases, see #6337

		$j = 0;
		$arrRows = $objData->fetchAllAssoc();

		// TBODY.
		for ($i=0, $c=count($arrRows); $i<$c; $i++)
		{
			$j = 0;
			$class = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i + 1) == count($arrRows)) ? ' row_last' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

			foreach ($arrRows[$i] as $k=>$v)
			{
				// Strip format code from column alias
				$kFormat = null;
				$matches = array();
				if (preg_match('/^(.*)::?(date|time|datetime|url|email|none)$/', $k, $matches))
				{
					$k = $matches[1];
					$kFormat = $matches[2];
				}
				// Collect column names for header (in first row only)
				if ($i == 0)
				{
					$arrFields[] = $k;
				}

				list($value, $valclass) = $this->formatValue($k, $v, $kFormat);

				$arrTd[$class][$k] = array
				(
					'raw' => $v,
					'content' => ($value ?: $this->nullValue),
					'class' => 'col_' . $j . (($j++ == 0) ? ' col_first' : '') . ($j >= (count($arrRows[$i]) - 1) ? ' col_last' : '') . ($valclass ? ' ' . $valclass : ''),
					'field' => $k,
				);
			}
		}

		// THEAD
		// uses collected column names from TBODY
		if ($this->ce_sql_noheader == 1)
		{
			$arrTh = null;
		}
		else
		{
			for ($i=0, $c=count($arrFields); $i<$c; $i++)
			{
				$label = $GLOBALS['TL_LANG']['ce_sql'][$arrFields[$i]];
				$arrTh[] = array
				(
					'link' => $label?:$arrFields[$i],
				);
			}
		}

		$this->Template->thead = $arrTh;
		$this->Template->tbody = $arrTd;

		/**
		 * Template variables
		 */

	 }


	/**
	 * Format a value
	 * @param string column name
	 * @param mixed column value
	 * @param string optional column format
	 * @return array(string, string)
	 */
	protected function formatValue($k, $value, $kFormat=null)
	{
		global $objPage;
		
		$value = deserialize($value);
		$valclass = '';

		// Return if empty
		if (empty($value))
		{
			return array('', $valclass);
		}

		// Array
		if (is_array($value))
		{
			return array(implode(', ', $value), $valclass);
		}
		// set numeric class
		if (is_numeric($value)
		or (preg_match('/[0-9]+/', value) and preg_match('/^[0-9,. ]+$/', $value)))
		{
			$valclass = $this->classNumeric;
		}

		if (empty($kFormat))
		{
			$kFormat = $this->stdFormat[$k];
		}
		
		// Suppress all auto formats
		if (empty($kFormat) or $kFormat == 'none')
		{
			// No formatting
		}
		
		// Date
		elseif ($kFormat == 'date')
		{
			$value = \Date::parse($objPage->dateFormat, $value);
		}

		// Time
		elseif ($kFormat == 'time')
		{
			$value = \Date::parse($objPage->timeFormat, $value);
		}

		// Date and time
		elseif ($kFormat == 'datetime')
		{
			$value = \Date::parse($objPage->datimFormat, $value);
		}

		// URLs
		elseif ($kFormat == 'url' or preg_match('@^(https?://|ftp://)@i', $value))
		{
			$value = \Idna::decode($value); // see #5946
			$value = '<a href="' . (preg_match('@^(https?://|ftp://)@i', $value) ? '' : 'http://') . $value . '"' . ' target="_blank"' . '>' . $value . '</a>';
		}

		// E-mail addresses
		elseif ($kFormat == 'email')
		{
			$value = \String::encodeEmail(\Idna::decode($value)); // see #5946
			$value = '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . $value . '">' . $value . '</a>';
		}

		// UUID
		elseif ($kFormat == 'uuid')
		{
			$value = \String::binToUuid($value);
		}

		return array($value, $valclass);
	}
}

