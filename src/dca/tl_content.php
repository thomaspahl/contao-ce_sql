<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   ce_sql
 * @author    Thomas Pahl
 * @license   GNU/LGPL
 * @copyright Fleckwerk
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['sql'] = '{type_legend},type,headline;{ce_sql_legend},ce_sql_query,ce_sql_noheader,ce_sql_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';


$GLOBALS['TL_DCA']['tl_content']['fields']['ce_sql_query'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_content']['ce_sql_query'],
	'default'       => '',
	'exclude'		=> true,
	'inputType'		=> 'textarea',
	'eval'			=> array('mandatory'=>true, 'class'=>'monospace', 'preserveTags'=>true, 'rows'=>5),
	'save_callback'	=> array(array('tl_ce_sql', 'checkQuery')),
	'sql'			=> 'mediumtext NULL'
);

$GLOBALS['TL_DCA']['tl_content']['fields']['ce_sql_noheader'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_content']['ce_sql_noheader'],
	'default'       => '',
	'exclude'		=> true,
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'w50'),
	'sql'			=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['ce_sql_template'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_content']['ce_sql_template'],
	'default'       => 'ce_sql_table',
	'exclude'		=> true,
	'inputType'		=> 'select',
	'options_callback' => array('tl_ce_sql', 'getTemplates'),
	'eval'			=> array('tl_class'=>'w50'),
	'sql'			=> "varchar(64) NOT NULL default ''"
);


class tl_ce_sql extends Backend
{
	public function getTemplates()
	{
		return $this->getTemplateGroup('ce_sql');
	}
	public function checkQuery($sql, $dca)
	{
		$sql = trim($sql);
		if (strtolower(substr($sql,0,6)) != 'select')
		{
			throw new Exception($GLOBALS['TL_LANG']['tl_content']['ce_sql_error1']); 
		}
		return $sql;
	}
}