<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package ce_sql
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespace('Fleckwerk');

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Elements
	'Fleckwerk\ContentSql' => 'system/modules/ce_sql/elements/ContentSql.php',

));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_sql_table' => 'system/modules/ce_sql/templates',
	'ce_sql_ul' => 'system/modules/ce_sql/templates',
	'ce_sql_ol' => 'system/modules/ce_sql/templates',
));
