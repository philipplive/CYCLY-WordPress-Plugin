<?php
namespace Cycly;

/**
 * Fahrzeug
 * Class Vehicle
 * @package Cycly
 */
class Vehicle extends Item {
	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * Modell
	 * @var string
	 */
	public $model = '';

	/**
	 * 1 = order, 2 = ordered, 10 = delivered, 20 = assembly, 21 = assembled
	 * @var int
	 */
	public $stateId = 0;

	/**
	 * Hersteller
	 * @var string
	 */
	public $manufacturer = '';

	/**
	 * Hersteller Tag (z.B. 'santacruz')
	 * @var string
	 */
	public $manufacturerKey = '';

	/**
	 * Typ als String
	 * @var string
	 */
	public $type = '';

	/**
	 * Type als Int
	 * @var int
	 */
	public $typeId = 0;

	/**
	 * Kategorie als String
	 * @var string
	 */
	public $category = '';

	/**
	 * Kategorie-id
	 * @var int
	 */
	public $categoryId = 0;

	/**
	 * Jahr
	 * @var int
	 */
	public $year = 0;

	/**
	 * Rahmengrösse in cm
	 * @var float
	 */
	public $frameSize = 0.0;

	/**
	 * Radgrösse in Zoll
	 * @var float
	 */
	public $wheelSize = 0.0;

	/**
	 * Gewicht
	 * @var float
	 */
	public $weight = 0.0;

	/**
	 * Motor
	 * @var string
	 */
	public $engine = '';

	/**
	 * Akku
	 * @var string
	 */
	public $battery = '';

	/**
	 * Im Laden
	 * @var bool
	 */
	public $stock = false;

	/**
	 * Voraussichtlicher Liefertermin
	 * @var \DateTime
	 */
	public $deliveryDate;

	/**
	 * Garantie
	 * @var \DateInterval
	 */
	public $warranty;

	/**
	 * Auf Webseite anzeigen
	 * @var bool
	 */
	public $website = false;

	/**
	 * Hervorheben
	 * @var bool
	 */
	public $highlight = false;

	/**
	 * Erfasst am
	 * @var \DateTime
	 */
	public $time;

	/**
	 * Preis
	 * @var float
	 */
	public $price = 0.0;

	/**
	 * Aktionspreis
	 * @var float
	 */
	public $discountPrice = 0.0;

	/**
	 * Aktionspreis?
	 * @var bool
	 */
	public $isDiscount = false;

	/**
	 * Fahrzeuparameter
	 * @var VehicleParameter[]
	 */
	public $parameters = [];

	/**
	 * Bilder
	 * @var Image[]
	 */
	public $images = [];

	/**
	 * @var VehicleCategory|null
	 */
	private $categoryCache = null;

	/**
	 * @return VehicleCategory
	 */
	public function getCategory(): VehicleCategory {
		if ($this->categoryCache)
			return $this->categoryCache;

		return $this->categoryCache = \System::getInstance()->getVehicleCategoryById($this->categoryId);
	}

	public function getParameterCategories(): array {
		$categories = [];

		foreach ($this->parameters as $parameter)
			$categories[$parameter->category] = $parameter->category;

		return $categories;
	}

	/**
	 * @param $categoryName
	 * @return VehicleParameter[]
	 */
	public function getParametersByCategory($categoryName): array {
		$parameters = [];

		foreach ($this->parameters as $parameter) {
			if ($parameter->category == $categoryName && !empty($parameter->valueFormated) && $parameter->valueFormated != '-')
				$parameters[] = $parameter;
		}

		return $parameters;
	}

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->fetchInProperty($data, 'model');
		$this->fetchInProperty($data, 'stateId', null, 0);
		$this->fetchInProperty($data, 'manufacturer');
		$this->fetchInProperty($data, 'type');
		$this->fetchInProperty($data, 'typeId', null, 0);
		$this->fetchInProperty($data, 'category');
		$this->fetchInProperty($data, 'categoryId', null, 0);
		$this->fetchInProperty($data, 'year', null, 0);
		$this->fetchInProperty($data, 'frameSize', null, 0.0);
		$this->fetchInProperty($data, 'wheelSize', null, 0.0);
		$this->fetchInProperty($data, 'weight', null, 0.0);
		$this->fetchInProperty($data, 'engine');
		$this->fetchInProperty($data, 'battery');
		$this->fetchInProperty($data, 'stock', null, 0);
		$this->fetchInProperty($data, 'highlight', null, false);
		$this->fetchInProperty($data, 'website', null, false);
		$this->fetchInProperty($data, 'price', null, 0.0);
		$this->fetchInProperty($data, 'discountPrice', null, 0.0);
		$this->warranty = $this->fetchIn($data, 'warranty', null) ? new \DateInterval($this->fetchIn($data, 'warranty')) : null;
		$this->time = $this->fetchIn($data, 'time', null) ? new \DateTime($this->fetchIn($data, 'time')) : new \DateTime();
		$this->deliveryDate = $this->fetchIn($data, 'deliveryDate', null) ? new \DateTime($this->fetchIn($data, 'deliveryDate')) : new \DateTime();
		$this->manufacturerKey = str_replace([' ', '-'], '', strtolower($this->manufacturer));

		$this->images = [];

		foreach ($data->images as $imgdata) {
			$image = new Image();
			$image->fromData($imgdata);
			$this->images[] = $image;
		}

		if (isset($data->parameters)) {
			foreach ($data->parameters as $parameter) {
				$param = new VehicleParameter();
				$param->fromData($parameter);
				$this->parameters[] = $param;
			}
		}

		return $this;
	}
}