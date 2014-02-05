<?php

class BrokenScriptsURLS extends BuildTask {
	protected $title = 'Search module scripts and templates for URLs that are broken';

	protected $description = 'A task that records external broken links in the source code of scripts and templates';

	protected $enabled = true;

	protected $checkExtensions = array(
		'php', 'md', 'js', 'ss'
	);

	// list of fairly generic domains used as examples
	protected $skipDomains = array(
		'my-host.com',
		'myhost.com',
		'mysite.com',
		'test.com',
		'example.com',
		'example.org',
		'mydomain',
		'website.com',
		'playboy.com'
	);

	function run($request) {
		// no point continuing without curl_init
		if(!function_exists('curl_init')) {
			echo '<p>curl_init is not available</p>';
			return;
		}
		$module = ($request->getVar('module')) ? $request->getVar('module') : 'cms';
		// some folders we may want to ignore like changelogs in the framework module
		$excludeDir = ($request->getVar('excludeDir')) ? $request->getVar('excludeDir') : '';
		$folder = BASE_PATH . DIRECTORY_SEPARATOR . $module;
		if (!file_exists($folder)) {
			return;
		}
		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST,
			RecursiveIteratorIterator::CATCH_GET_CHILD
		);
		$scripts = array();
		foreach ($iter as $path => $dir) {
			if (!$dir->isDir() && in_array(pathinfo($path, PATHINFO_EXTENSION), $this->checkExtensions)) {
				if (empty($excludeDir) ||
					strpos(pathinfo($path, PATHINFO_DIRNAME), $excludeDir) == FALSE) {
					$scripts[] = $path;
				}
			}
		}
		$tlds = '';
		// TODO: Will need to add support for Punycode URLs at some point
		$tldObj = TopLevelDomain::get()
			->filter(array('Enabled' => 1, 'Punycode' => 0));
		foreach ($tldObj as $tld) {
			$tlds = (empty($tlds)) ? $tld->TLD : $tlds . '|' . trim($tld->TLD);
		}

		// create a RegExp for domains we want to ignore
		$ignoreDomainRegExp = '';
		foreach ($this->skipDomains as $domain) {
			$ignoreDomainRegExp = (empty($ignoreDomainRegExp)) ? $domain 
				: $ignoreDomainRegExp . '|' . $domain;
		}

		// the tlds are a important part of this regexp mainly so lines without a valid tld
		// are not matched
		$search = "~([\.-\w]*)(\.)($tlds)(\?|/)([\?\.a-zA-Z0-9\/=_#&%\~-]*)[\n\r\z]*~i";
		foreach ($scripts as $script) {
			$fileText = file_get_contents($script);
			preg_match_all($search, $fileText, $matches);
			$href = false;
			if (count($matches) > 0) {
				foreach ($matches[0] as $value) {
					// set href to null for domains we want to skip
					$href =  preg_match("~($ignoreDomainRegExp)~i", $value) ? null : $value;
					if($href) {
						$handle = curl_init($href);
						curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
						$response = curl_exec($handle);
						$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);  
						curl_close($handle);
						if (($httpCode < 200 || $httpCode > 302)
							|| ($href == '' || $href[0] == '/')) {
							echo "<p>Checking script $script</p>";
							echo "<p>URL $value returns HTTP Code $httpCode</p>";
							$brokenLink = BrokenURL::get()
								->filter(array(
									'URL' => $href,
									'Module' => $module,
									'Script' => basename($script)
								));
							if (!$brokenLink->exists()) {
								$brokenLink = new BrokenURL();			
								$brokenLink->URL = $href;
								$brokenLink->Module = $module;
								$brokenLink->Script = basename($script);
								$brokenLink->HTTPCode = $httpCode;
								$brokenLink->write();
							}

						}	

					}
				}
			}
		}
	}
}
