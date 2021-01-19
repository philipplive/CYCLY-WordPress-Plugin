<?php

class CyclySystem extends HfCore\System {
	public function __construct() {
		parent::__construct();

		$this->getCronjobController()->addCronjob('cycly-cronjob', [$this, 'cleanCache']);
		$this->getTemplateController()->addJsFile('tpl/cycly.js');
		$this->addWidget('CyclyWidget');

		// Api Endpunkte
		$this->getApi()->addEndpoint('cleancache', [$this, 'cleanCacheForce']);
	}

	/**
	 * Cashordner leeren
	 */
	public function cleanCacheForce(){
		if(!$this->isAdmin())
			throw new Exception('Kein Zugriff',403);

		$this->cleanCache('P0D');
	}

	/**
	 * Cashordner aufräumen
	 * @param string $maxAge
	 */
	public function cleanCache($maxAge = 'P7D') {
		foreach (\HfCore\IO::getFolder(HfCore\System::getInstance()->getPluginCachePath())->getFiles() as $file) {
			if ($file->getLastChange() < HfCore\Time::goBack($maxAge))
				$file->delete();
		}
	}

	/**
	 * @return \Cycly\Branch[]
	 */
	public function getBranches(): array {
		$branches = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'branches']) as $data) {
			$item = new \Cycly\Branch();
			$item->fromData($data);
			$branches[$item->id] = $item;
		}

		return $branches;
	}
}
