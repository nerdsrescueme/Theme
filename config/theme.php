<?php

/**
 * Attempt to load the theme array from the application
 * before returning the values from this file.
 *
 * @return    array
 */

return array_merge([

	'root' => join(DS, [\Nerd\DOCROOT, 'themes']),

	'default' => 'default',

	'info' => [

		'enabled' => true,

		'format' => 'json', // Must correspond to Nerd\Format class drivers...

		'file' => 'theme.json',
	],

], \Nerd\Config::get('theme', []));