<?php

class BrokenScriptsURLS extends BuildTask {
	protected $title = 'Search CMS/Framework files for URL that are broken';

	protected $description = 'A task that records external broken links in the comments of scripts';

	protected $enabled = true;

	protected $checkExtensions = array(
		'php', 'md', 'js', 'ss'
	);

	function run($request) {
		$module = ($request->getVar('module')) ? $request->getVar('module') : 'cms';
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
				$scripts[] = $path;
			}
		}
		$tlds = '';
		$tldObj = TopLevelDomain::get()
			->filter(array('Enabled' => 1, 'Punycode' => 0));
		foreach ($tldObj as $tld) {
			$tlds = (empty($tlds)) ? $tld->TLD : $tlds . '|' . trim($tld->TLD);
		}
		$search = "~(\w*\.)+($tlds)/?[\n\r\z]+~i";
		foreach ($scripts as $script) {
			echo "<p>Checking script $script</p>";
			$fileText = file_get_contents($script);
			preg_match_all($search, $fileText, $matches);
			$href = false;
			if (count($matches) > 0) {
				foreach ($matches[0] as $value) {
					echo "<p>Checking URL $value</p>";
					$href = $value;
					if($href && function_exists('curl_init')) {
						$handle = curl_init($href);
						curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
						$response = curl_exec($handle);
						$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);  
						echo "<p>HTTP Code $httpCode</p>";
						curl_close($handle);
						if (($httpCode < 200 || $httpCode > 302)
							|| ($href == '' || $href[0] == '/')) {
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
