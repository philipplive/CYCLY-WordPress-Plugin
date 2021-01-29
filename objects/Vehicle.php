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
	 * Hersteller
	 * @var string
	 */
	public $manufacturer = '';

	/**
	 * Hersteller Tag (z.B. 'santacruz')
	 * @var string
	 */
	public $manufacturerId = '';

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
	 * Farbe
	 * @var string
	 */
	public $color = '';

	/**
	 * Rahmengrösse in cm
	 * @var float
	 */
	public $frameSize = 0.0;

	/**
	 * Rahmengrösse formatiert
	 * @var string
	 */
	public $frameSizeFormated = '';

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
	 * Für Körpergrösse von
	 * @var int
	 */
	public $heightFrom = 0;

	/**
	 * Für Körpergrösse bis
	 * @var int
	 */
	public $heightTo = 0;

	/**
	 * Schaltung
	 * @var string
	 */
	public $shifting = '';

	/**
	 * Bremsen
	 * @var string
	 */
	public $brakes = '';

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
	 * Erweiterte Informationen
	 * @var string
	 */
	public $description = '';

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


	public function getDescription(): string {
		return trim(preg_replace('/^(.+)\:(.+)$/m', '', $this->description));
	}

	public function getDescriptionProperties(): array {
		$list = [];
		$data = null;
		preg_match_all('/^(.+)\:(.+)$/m', $this->description, $data);
		foreach ($data[1] as $i => $title) {
			$list[$title] = $data[2][$i];
		}
		return $list;
	}

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->model = $data->model;
		$this->manufacturer = $data->manufacturer;
		$this->manufacturerId = str_replace([' ', '-'], '', strtolower($data->manufacturer));
		$this->type = $data->type;
		$this->typeId = $data->typeId;
		$this->category = $data->category;
		$this->categoryId = $data->categoryId;
		$this->year = $data->year;
		$this->color = $data->color;
		$this->frameSize = $data->frameSize;
		$this->frameSizeFormated = $data->frameSizeFormated;
		$this->wheelSize = $data->wheelSize;
		$this->weight = $data->weight;
		$this->heightFrom = $data->heightFrom;
		$this->heightTo = $data->heightTo;
		$this->shifting = $data->shifting;
		$this->brakes = $data->brakes;
		$this->engine = $data->engine;
		$this->battery = $data->battery;
		$this->description = $data->description;
		$this->stock = $data->stock;
		$this->deliveryDate = $data->deliveryDate ? new \DateTime($data->deliveryDate) : null;
		$this->warranty = new \DateInterval($data->warranty);
		$this->website = $data->website;
		$this->highlight = $data->highlight;
		$this->time = new \DateTime($data->time);
		$this->price = $data->price;
		$this->discountPrice = $data->discountPrice;

		$this->images = [];

		foreach ($data->images as $imgdata) {
			$image = new \Cycly\Image();
			$image->fromData($imgdata);
			$this->images[] = $image;
		}

		return $this;
	}
}