<?php
namespace HfCore;

class GitHub {
	private $system = null;

	public function __construct(System $system) {
		$this->system = $system;
	}

	public function getLocalHeader(): array {
		$file = IO::readFile($this->system->getPluginPath().$this->system->getPluginName().'.php');

		return $this->parseHeader($file);
	}

	public function getMasterHeader(): array {
		$name = $this->githubApiRequest(['repositories', $this->getLocalHeader()['githubid']])->full_name;
		$file = CurlClient::create(sprintf('https://raw.githubusercontent.com/%s/master/%s.php', $name, $this->system->getPluginName()))->exec()->getBody();

		return $this->parseHeader($file);
	}

	/**
	 * Headerbereich parsen
	 * @param string $file
	 * @return array
	 */
	private function parseHeader(string $file): array {
		$vars = [];
		$output = null;
		preg_match_all('/^\s\*\s(.*)$/m', $file, $output);

		foreach ($output[1] as $line) {
			$parts = preg_split('/\t+/', $line);

			if (count($parts) == 2)
				$vars[strtolower(str_replace([':', ' '], '', trim($parts[0])))] = trim($parts[1]);
		}

		return $vars;
	}

	private function downloadMasterZip() : FileLocal {
		$data = $this->githubApiRequest(['repositories', $this->getLocalHeader()['githubid']]);
		$url = sprintf('https://codeload.github.com/%s/zip/refs/heads/master', $data->full_name);

		return IO::getFile($this->system->getPluginCachePath().'master.zip')->write(CurlClient::create($url)->exec()->getBody());
	}

	/**
	 * Instanz updaten
	 */
	public function update() {
		$file = $this->downloadMasterZip();

		$zip = new \ZipArchive;
		if ($zip->open($file->getPath()) === true) {
			$zip->extractTo($this->system->getPluginCachePath('test'));
			$zip->close();
		} else {
			throw new \Exception('Fehler beim entpacken');
		}
	}

	public function checkUpdateFunctionality(): bool {
		if (!class_exists('\ZipArchive'))
			throw new \Exception('ZipArchive nicht vorhanden');
	}

	/**
	 * Request zur GitHub API
	 * @param array $query
	 * @return object
	 */
	public function githubApiRequest(array $query): object {
		return CurlClient::create('https://api.github.com/'.implode('/', $query))->setUserAgent('User-Agent: Cycly')->exec()->getFromJSON();
	}

	/**
	 * Version
	 * @param bool $localInstance
	 * @return string
	 */
	public function getVersion(bool $localInstance = true): string {
		if ($localInstance)
			return $this->getLocalHeader()['version'];
		else
			return $this->getMasterHeader($localInstance)['version'];
	}

	/**
	 * Ist die aktuelle Instanz aktuell?
	 * @return bool
	 */
	public function isUpToDate(): bool {
		return $this->getVersion() == $this->getVersion(false);
	}
}