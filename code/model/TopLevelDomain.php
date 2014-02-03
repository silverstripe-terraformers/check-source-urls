<?php

class TopLevelDomain extends DataObject {
	private static $db = array(
		'TLD' => 'Varchar',
		'Enabled' => 'Boolean',
		'Punycode' => 'Boolean'
	);

	private $tldURL = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt';

	private function getTopLevelDomainsFromIANA() {
		$tlds = file($this->tldURL);
		return $tlds;
	}


	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		$tlds = $this->getTopLevelDomainsFromIANA();
		foreach ($tlds as $tld) {
			$pos = strpos($tld, '#');
			if ($pos === false) {
				$dbObj = TopLevelDomain::get()
					->filter('TLD', strtoupper($tld));
				if ($dbObj->exists()) {
					$row = $dbObj->First();
				} else {
					$row = new TopLevelDomain();
				}
				$row->TLD = strtoupper($tld);
				$row->Enabled = true;
				$row->Punycode = (strpos($tld, 'XN--') === false) ? false : true;
				$row->write();
			}
		}
	}
}
