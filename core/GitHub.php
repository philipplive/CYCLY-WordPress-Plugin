<?php
namespace HfCore;

class GitHub {
	const REPOSITORY_ZIP_URL = 'http://repository.cycly.bike/wp-plugin/wp-plugin.zip';
	const REPOSITORY_VERSION_URL = 'http://repository.cycly.bike/wp-plugin/version.txt';

	private $system = null;

	public function __construct(System $system) {
		$this->system = $system;
	}

	public function getLocalHeader(): array {
		$file = IO::readFile($this->system->getPluginPath().$this->system->getPluginName().'.php');

		return $this->parseHeader($file);
	}

	/**
	 * Version vom Repository abrufen
	 * @return string|null
	 */
	public function getRemoteVersion(): ?string {
		try {
			$version = trim(CurlClient::create(self::REPOSITORY_VERSION_URL)->exec()->getBody());
		}
		catch (\Exception $ex) {
			return null;
		}

		return $version ?: null;
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

	private function downloadMasterZip(): FileLocal {
		return IO::getFile($this->system->getPluginCachePath().'master.zip')->write(CurlClient::create(self::REPOSITORY_ZIP_URL)->exec()->getBody());
	}

	/**
	 * Instanz updaten
	 */
	public function update() {
		$this->checkUpdateFunctionality();

		if ($this->isUpToDate())
			throw new \Exception('Version bereits aktuell');

		$file = $this->downloadMasterZip();

		$tmp = IO::getFolder($this->system->getPluginCachePath('update'));

		// Entpacken
		$zip = new \ZipArchive;
		if ($zip->open($file->getPath()) === true) {
			$zip->extractTo($tmp);
			$zip->close();
		}
		else {
			throw new \Exception('Fehler beim entpacken');
		}

		// Verschieben und ersetzen (Dateien liegen direkt im Root des Zips)
		$tmp->copy(IO::getFolder($this->system->getPluginPath()));

		// Tmp löschen
		$tmp->getParentFolder()->clear();

		// Cache leeren
		foreach ($this->system->getCacheController()->getAll() as $item)
			$this->system->getCacheController()->delete($item);
	}

	/**
	 * Prüfe ob Update möglich
	 * @return bool
	 */
	public function checkUpdateFunctionality(): bool {
		if (!class_exists('\ZipArchive'))
			throw new \Exception('ZipArchive nicht vorhanden');

		return true;
	}

	/**
	 * Version
	 * @param bool $localInstance
	 * @return string|null
	 */
	public function getVersion(bool $localInstance = true) {
		if ($localInstance)
			return $this->getLocalHeader()['version'] ?? null;
		else
			return $this->getRemoteVersion();
	}

	/**
	 * Ist die aktuelle Instanz aktuell?
	 * @return bool
	 */
	public function isUpToDate(): bool {
		$result = $this->system->getCacheController()->get('upToDate');

		// Nicht aktuell oder Cache abgelaufen, dann erneut versuchen
		if ($result === false) {
			$currentVersion = $this->getVersion();
			$remoteVersion = $this->getVersion(false);

			// Git nicht abrufbar aktuell
			if ($currentVersion == null || $remoteVersion == null)
				return true;

			$result = $this->getVersion() === $this->getVersion(false);
			$this->system->getCacheController()->set('upToDate', $result, 360);
		}

		return $result;
	}
}