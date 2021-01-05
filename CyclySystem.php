<?php

class CyclySystem extends HfCore\System {
	public function __construct() {
		parent::__construct();

		$this->getCronjobController()->addCronjob('cycly-cronjob', [$this, 'cleanCache']);
		$this->getTemplateController()->addJsFile('tpl/cycly.js');
		$this->addWidget('CyclyWidget');
	}

	/**
	 * Cashordner aufräumen
	 */
	public function cleanCache() {
		foreach (\HfCore\IO::getFolder(HfCore\System::getInstance()->getPluginCachePath())->getFiles() as $file) {
			if ($file->getLastChange() < HfCore\Time::goBack('P1D'))
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
