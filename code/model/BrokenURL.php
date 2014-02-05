<?php

class BrokenURL extends DataObject {
	private static $db = array(
		'URL' => 'Varchar(255)',
		'Script' => 'Varchar(255)',
		'Module' => 'Varchar',
		'HTTPCode' => 'Int'
	);

	private static $summary_fields = array(
		'URL' => 'URL',
		'Script' => 'Script',
		'Module' => 'Module',
		'HTTPCode' => 'HTTP Error Code'
	);
}

class BrokenURLModelAdmin extends ModelAdmin {
	private static $managed_models = array('BrokenURL');
	private static $url_segment = 'BrokenURLs';
	private static $menu_title = 'Broken URLs';
}
