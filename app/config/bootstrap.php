<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
 function strip_diacritic($text, $is_file = false) {
	$text = str_replace(",", "", $text); // carky
	$text = str_replace("(", "", $text); // leve zavorky
	$text = str_replace(")", "", $text); // prave zavorky
	$text = str_replace("=", "", $text); // rovnitko
	$text = str_replace("+", "", $text); // plus
	$text = str_replace("!", "", $text); // vykricnik
	$text = str_replace('%', '', $text); // procenta
	$text = str_replace(':', '', $text);
	$text = str_replace('/', '-', $text);
	if (!$is_file) {
		$text = str_replace(".", "", $text); // tecka
	}
	
	// nejdriv odstranim zbytecne mezery,
	// kolem pomlcek zleva
	while ( eregi(" -", $text) ){
		$text = str_replace(" -", "-", $text);
	}
	// zprava
	while ( eregi("- ", $text) ){
		$text = str_replace("- ", "-", $text);
	}

	// odstranim pismena s diakritikou
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'-', 'Ř'=>'R', 'ř'=>'r', 'Ť'=>'T', 'ť'=>'t', 'ě'=>'e'
    );
    $text = strtr($text, $table);

	// dve pomlcky za sebou 
	while ( eregi("--", $text) ){
		$text = str_replace("--", "-", $text);
	}
	
	// hodim text na mala pismena
	$text = strtolower($text);

	return $text;
}

/**
 * 
 * z data nacteneho z databaze udela datum v ceskem tvaru
 * @param string $date
 */
function czech_date($date) {
	if ($date) {
		if (preg_match('/([^ ]+) .*/', $date, $matches)) {
			$date = $matches[1];
		}
		$date = explode('-', $date);
		$date = array_map('intval', $date);
		$date = $date[2] . '. ' . $date[1] . '. ' . $date[0];
	}
	return $date;
}

/**
 * z data ve formatu d.m.Y (z kalendare) posklada datum Y-m-d (do databaze)
 */
function cal2db_date($date) {
	if ($date) {
		$res = explode('.', $date);
		return array(
			'year' => $res[2],
		 	'month' => $res[1],
			'day' => $res[0]
		);
	} else {
		return false;
	}
}

/**
 * inverzni fce ke cal2db
 */
function db2cal_date($date) {
	if ($date) {
		$res = explode('-', $date);
		return $res[2] . '.' . $res[1] . '.' . $res[0];
	} else {
		return false;
	}	
}

function download_url_like_browser($url = null) {
	$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
	if ($url) {
		$content = false;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	return false;
}

define('CUST_MAIL', 'no-reply@c.pharmacorp.cz');
define('CUST_ROOT', 'c.pharmacorp.cz');
define('CUST_NAME', 'CRM PharmaCorp CZ');
define('DL_FOLDER', 'files/delivery_notes/');

/**
 * This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do What The Fuck You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details.
*/
/**
 * Tests if an input is valid PHP serialized string.
*
* Checks if a string is serialized using quick string manipulation
* to throw out obviously incorrect strings. Unserialize is then run
* on the string to perform the final verification.
*
* Valid serialized forms are the following:
* <ul>
* <li>boolean: <code>b:1;</code></li>
* <li>integer: <code>i:1;</code></li>
* <li>double: <code>d:0.2;</code></li>
* <li>string: <code>s:4:"test";</code></li>
* <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
* <li>object: <code>O:8:"stdClass":0:{}</code></li>
* <li>null: <code>N;</code></li>
* </ul>
*
* @author		Chris Smith <code+php@chris.cs278.org>
* @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
* @license		http://sam.zoy.org/wtfpl/ WTFPL
* @param		string	$value	Value to test for serialized form
* @param		mixed	$result	Result of unserialize() of the $value
* @return		boolean			True if $value is serialized data, otherwise false
*/
function is_serialized($value, &$result = null)
{
	// Bit of a give away this one
	if (!is_string($value))
	{
		return false;
	}
	// Serialized false, return true. unserialize() returns false on an
	// invalid string or it could return false if the string is serialized
	// false, eliminate that possibility.
	if ($value === 'b:0;')
	{
		$result = false;
		return true;
	}
	$length	= strlen($value);
	$end	= '';
	switch ($value[0])
	{
		case 's':
			if ($value[$length - 2] !== '"')
			{
				return false;
			}
		case 'b':
		case 'i':
		case 'd':
			// This looks odd but it is quicker than isset()ing
			$end .= ';';
		case 'a':
		case 'O':
			$end .= '}';
			if ($value[1] !== ':')
			{
				return false;
			}
			switch ($value[2])
			{
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					break;
				default:
					return false;
			}
		case 'N':
			$end .= ';';
			if ($value[$length - 1] !== $end[0])
			{
				return false;
			}
			break;
		default:
			return false;
	}
	if (($result = @unserialize($value)) === false)
	{
		$result = null;
		return false;
	}
	return true;
}

function months() {
	return array(
		1 => 'Leden',
		2 => 'Únor',
		3 => 'Březen',
		4 => 'Duben',
		5 => 'Květen',
		6 => 'Červen',
		7 => 'Červenec',
		8 => 'Srpen',
		9 => 'Září',
		10 => 'Říjen',
		11 => 'Listopad',
		12 => 'Prosinec'
	);
}

function price_vat($price, $vat) {
	$coef = 100 + $vat;
	$price_vat = $price * $coef / 100;
	
	return $price_vat;
}

function price_wout_vat($price_vat, $vat) {
	$price = $price_vat - ($price_vat * $vat / 100);

	return $price;
}