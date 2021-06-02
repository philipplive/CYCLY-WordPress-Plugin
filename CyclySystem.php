<?php

class CyclySystem extends HfCore\System {
	public function __construct() {
		parent::__construct();
		$this->getCacheController();

		$this->getCronjobController()->addCronjob('cycly-cronjob', [$this, 'cleanCache']);
		$this->addWidget('CyclyWidget');

		// Api Endpunkte
		$this->getApi()->addEndpoint('cleancache', [$this, 'cleanCacheForce']);
		$this->getApi()->addEndpoint('update', [$this, 'updateSystem']);
	}

	/**
	 * Cashordner komplett leeren
	 */
	public function cleanCacheForce() {
		if (!$this->isAdmin())
			throw new Exception('Kein Zugriff', 403);

		$this->cleanCache('P0D', 10000);
	}

	/**
	 * Cash aufräumen
	 * @param string $maxAge
	 * @param int $maxCount Maximale Anzahl an Files welche pro Durchgang gelöscht werden
	 */
	public function cleanCache($maxAge = 'P7D', $maxCount = 20) {
		// Transients
		foreach ($this->getCacheController()->getAll() as $item)
			$this->getCacheController()->delete($item);

		// Files
		foreach (\HfCore\IO::getFolder($this->getPluginCachePath())->getFiles() as $file) {
			if ($file->getLastChange() < HfCore\Time::goBack($maxAge)) {
				$file->delete();

				if (--$maxCount < 0)
					return;
			}
		}
	}

	public function updateSystem(){
		sleep(3);
		echo "WAAAAA";
		return;

		if (!$this->isAdmin())
			throw new Exception('Kein Zugriff', 403);

		$this->getGitHub()->update();
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

	/**
	 * @param $id
	 * @return \Cycly\Vehicle|null
	 */
	protected function getVehicleById($id): ?\Cycly\Vehicle {
		$item = new \Cycly\Vehicle();
		$item->fromData(\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', $id]));

		return $item;
	}

	/**
	 * Fahrzeuge einer Geschäftsstelle
	 * @param int $branchId
	 * @return \Cycly\Vehicle[]
	 */
	public function getVehicles(?int $branchId = null): array {
		if ($branchId === null)
			throw new Exception('Noch nicht supported');

		$vehicles = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', 'branch', $branchId]) as $data) {
			$item = new \Cycly\Vehicle();
			$item->fromData($data);

			// Filter für "Velos ohne Bild"
			if (get_option('cycly_hide_vehicle_without_image') && empty($item->images))
				continue;

			$vehicles[$item->id] = $item;
		}

		return $vehicles;
	}

	/**
	 * Mitarbeiter einer Geschäftsstelle
	 * @param int $branchId
	 * @return \Cycly\Employee[]
	 */
	protected function getEmployees($branchId = 1): array {
		$employees = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'employees', 'branch', $branchId]) as $data) {
			$item = new \Cycly\Employee();
			$item->fromData($data);
			$employees[$data->id] = $item;
		}

		return $employees;
	}

	/**
	 * @return \Cycly\VehicleCategory
	 */
	public function getVehicleCategoryById(int $id): \Cycly\VehicleCategory {
		$categories = $this->getVehicleCategories();

		if (!isset($categories[$id]))
			throw new \Exception('Kategorie nicht gefuden', 404);

		return $categories[$id];
	}

	/**
	 *  Verfügbare Fahrzeugkategorien
	 * @return \Cycly\VehicleCategory[]
	 */
	protected function getVehicleCategories(): array {
		$items = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', 'categories']) as $data) {
			$item = new \Cycly\VehicleCategory();
			$item->fromData($data);
			$items[$item->id] = $item;
		}

		return $items;
	}

	/**
	 * @return \Cycly\VehicleType[]
	 */
	protected function getVehicleTypes(): array {
		$this->items = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', 'types']) as $data) {
			$item = new \Cycly\VehicleType();
			$item->fromData($data);
			$items[$item->id] = $item;
		}

		return $items;
	}

	/**
	 * Verfügbare Marken
	 * @param int|null $branchId
	 * @return array ['santacruz' => 'Santa Cruz']
	 */
	protected function getManufactures(?int $branchId = null): array {
		$items = [];

		foreach ($this->getVehicles($branchId) as $item) {
			$items[$item->manufacturerKey] = $item->manufacturer;
		}

		return $items;
	}
}
