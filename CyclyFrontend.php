<?php

use \HfCore\HtmlNode;

class CyclyFrontend extends CyclySystem {

	public function __construct() {
		parent::__construct();

		$this->getTemplateController()->addCssFile('tpl/cycly.less');
		$this->getTemplateController()->addJsFile('tpl/cycly.js');

		// Wordpress Tags erfassen
		add_shortcode('show_bikes', [$this, 'drawBikes']); // Veloübersicht
		add_shortcode('show_employees', [$this, 'drawEmployees']); // Mitarbeiterübersicht

		// Api Endpunkte
		$this->getApi()->addEndpoint('bike/(?P<id>\d+)', [$this, 'apiGetBike']);
	}

	/**
	 * WP-Tag Veloliste
	 * @param $atts ['branch', 'categories','manufacturers','sort']
	 * @return string
	 */
	public function drawBikes($atts): string {
		// Geschäftsstelle
		$branch = Query::param($atts, 'branch', HfCore\T_INT, 1);

		// Verfügbare Fahrzeuge
		$vehicles = new VehicleSet($branch);

		/**
		$mFilter = new VehicleFilter($vehicles, 'manufacturerId', $this->getManufactures($branch), 'Hersteller');
		$cFilter = new VehicleFilter($vehicles, 'categoryId', $this->getVehicleCategories($branch), 'Kategorie');

		if (!empty(Query::param($atts, 'manufacturers', HfCore\T_STR, ''))) {
			$mFilter->reduceOptions(explode(',', str_replace(' ', '', Query::param($atts, 'manufacturers', HfCore\T_STR))));
		}

		if (!empty(Query::param($atts, 'categories', HfCore\T_STR, ''))) {
			$cFilter->reduceOptions(explode(',', str_replace(' ', '', Query::param($atts, 'categories', HfCore\T_STR))));
		}*/

		// Body
		$body = HtmlNode::div()->addClass('cycly-vehicles');

		// Vergüfbare Hersteller
		$manufacturers = [];

		if (!empty(Query::param($atts, 'manufacturers', HfCore\T_STR, ''))) {
			foreach (explode(',', str_replace(' ', '', Query::param($atts, 'manufacturers', HfCore\T_STR))) as $item) {
				foreach ($this->getManufactures($branch) as $key => $manufacturer) {
					if ($key == $item)
						$manufacturers[$key] = $manufacturer;
				}
			}
		}
		else {
			$manufacturers = $this->getManufactures($branch);
		}

		// Verfügbare Kategorien
		$categories = [];

		if (!empty(Query::param($atts, 'categories', HfCore\T_STR, ''))) {
			foreach (explode(',', str_replace(' ', '', Query::param($atts, 'categories', HfCore\T_STR))) as $item) {
				foreach ($this->getVehicleCategories($branch) as $category) {
					if (strtolower(str_replace('-', '', $category->title)) == $item)
						$categories[$category->id] = $category;
				}
			}
		}
		else {
			$categories = $this->getVehicleCategories($branch);
		}

		// Vehicles
		$items = HtmlNode::div()->addClass('items')->hide()->appendTo($body);

		foreach ($vehicles->getAll() as $vehicle) {
			if (!array_key_exists($vehicle->categoryId, $categories))
				continue;

			if (!array_key_exists($vehicle->manufacturerId, $manufacturers))
				continue;

			$bike = HtmlNode::div()
				->addClass('item')
				->data('categoryid', $vehicle->categoryId)
				->data('typeId', $vehicle->typeId)
				->data('manufacturerid', $vehicle->manufacturerId)
				->data('year', $vehicle->year)
				->data('price', (int)$vehicle->price);

			$img = HtmlNode::img();
			$bike->append(HtmlNode::a($img)->addClass('image')->data('id', $vehicle->id));

			if (count($vehicle->images))
				$img->attr('src', $vehicle->images[0]->getResizedImageLink(500, 500))->attr('title', $vehicle->images[0]->title);
			else
				$img->attr('src', $this->getPluginUrl().'/tpl/vehicle-empty.png')->attr('title', 'Kein Bild vorhanden');

			$bike->append(HtmlNode::div(HtmlNode::strong($vehicle->manufacturer))->append($vehicle->model)->addClass('title'));
			$bike->append(HtmlNode::div(\HfCore\Price::format($vehicle->price))->addClass('price'));

			$items->append($bike);
		}

		// Mehr-Button
		HtmlNode::div('Mehr anzeigen')->addClass('moreButton')->appendTo($body);

		// Filter
		$fitlers = HtmlNode::div()->prependTo($body)->addClass('filters');

		$categoryOptions = [];

		if (count($categories) > 1)
			$categoryOptions[0] = 'Alle';

		foreach ($categories as $category) {
			if ($category->count)
				$categoryOptions[$category->id] = $category->title;
		}

		$fitlers->append($this->generateFilter('categoryId', 'Kategorie', $categoryOptions, array_key_first($categoryOptions), count($categories) > 1));

		$manufacturerOptions = [];

		if (count($manufacturers) > 1)
			$manufacturerOptions = [0 => 'Alle'];

		foreach ($manufacturers as $key => $name) {
			$manufacturerOptions[$key] = $name;
		}

		$fitlers->append($this->generateFilter('manufacturerId', 'Hersteller', $manufacturerOptions, array_key_first($manufacturerOptions), count($manufacturers) > 1));

		// Sortierfunktion
		$fitlers->append($this->generateFilter('sort', 'Sortieren', [2 => 'Preis', 1 => 'Preis absteigend', 4 => 'Jahrgang', 3 => 'Jahrgang absteigend'], Query::param($atts, 'sort', HfCore\T_INT, 2)));

		// Dialog
		$close = HtmlNode::a(HtmlNode::span(), HtmlNode::span())->addClass('button-close');
		HtmlNode::div()
			->append(HtmlNode::div($close, HtmlNode::div()->addClass('dialog-container'))->addClass('dialog'))
			->addClass('dialog-wrapper')
			->appendTo($body);

		return $body;
	}

	/**
	 * WP-Tag Mitarbeiterliste
	 * @param $atts
	 * @return HtmlNode
	 */
	public function drawEmployees($atts) {
		$body = HtmlNode::div()->id('cycly-employees')->data('branch', isset($atts['branch']) ? $atts['branch'] : 1);

		foreach ($this->getEmployees(isset($atts['branch']) ? $atts['branch'] : 1) as $emplyee) {
			$item = HtmlNode::div()->addClass('employee')->appendTo($body);
			$item->append(HtmlNode::img()->attr('src', $emplyee->image->getUrlTiny())->attr('title', $emplyee->image->title));
			$item->append(HtmlNode::p($emplyee->firstname.' '.$emplyee->lastname)->addClass('name'));
		}

		return $body;
	}

	/**
	 * WP-API aufruf (Einzelnes Velo)
	 * @param $data
	 */
	public function apiGetBike($data): void {
		$vehicle = $this->getVehicleById($data['id']);

		$element = HtmlNode::div()
			->addClass('vehicle-details');

		HtmlNode::h2()
			->appendText($vehicle->manufacturer)
			->appendText(' ')
			->append(HtmlNode::strong()->setText($vehicle->model))
			->appendTo($element);

		HtmlNode::div()
			->addClass('vehicle-category')
			//->append(HtmlNode::span()->addClass('label')->setText('Fahrzeugkategorie: '))
			//->append(HtmlNode::span()->addClass('value')->setText($vehicle->category))
			->appendTo($element);

		$images = HtmlNode::div()
			->addClass('vehicle-images')
			->appendTo($element);

		$imageMain = HtmlNode::div()
			->addClass('image-main')
			->appendTo($images);

		$imageList = HtmlNode::div()
			->addClass('image-list')
			->appendTo($images);

		$first = true;
		foreach ($vehicle->images as $image) {
			if ($first) {
				$imageMain->append(HtmlNode::img()->attr('src', $image->getUrlFull())->attr('alt', $image->title ? $image->title : $vehicle->model));
			}
			else {
				$imageList->append(HtmlNode::div()->addClass('list-item')->append(HtmlNode::img()->attr('src', $image->getUrlTiny())->attr('alt', $image->title ? $image->title : $vehicle->model)));
			}

			$first = false;
		}

		if (!count($vehicle->images))
			$imageMain->append(HtmlNode::img()->attr('src', '/wp-content/plugins/cycly-connector/tpl/vehicle-empty.png')->attr('title', 'Kein Bild vorhanden'));

		$infos = HtmlNode::div()
			->addClass('vehicle-infos')
			->appendTo($element);

		$container = HtmlNode::div()
			->addClass('description-container')
			->addClass('vehicle-price')
			->appendTo($infos);

		$dl = HtmlNode::dl()
			->appendTo($container);

		HtmlNode::dt()
			->setText('Netto Preis:')
			->appendTo($dl);

		HtmlNode::dd()
			->addClass('price')
			->setText(\HfCore\Price::format($vehicle->discountPrice ? $vehicle->discountPrice : $vehicle->price))
			->appendTo($dl);

		if ($vehicle->discountPrice) {
			HtmlNode::dt()
				->setText('Preis:')
				->appendTo($dl);

			HtmlNode::dd()
				->addClass('oldPrice')
				->setText(\HfCore\Price::format($vehicle->price))
				->appendTo($dl);
		}

		HtmlNode::dt()
			->setText('Garantie:')
			->appendTo($dl);

		HtmlNode::dd()
			->setText('ja')
			//->setText($vehicle->warranty)
			->appendTo($dl);

		HtmlNode::dt()
			->setText('Im Laden:')
			->appendTo($dl);

		HtmlNode::dd()
			->setText(!$vehicle->stock && $vehicle->deliveryDate ? 'Liefertermin: '.$vehicle->deliveryDate->format('Y-m-d H:i:s') : $vehicle->stock)
			->appendTo($dl);

		// Spezifikationen
		$container = HtmlNode::div()
			->addClass('description-container')
			->addClass('vehicle-specs')
			->appendTo($infos);

		HtmlNode::h3()
			->setText('Spezifikationen')
			->appendTo($container);

		$dl = HtmlNode::dl()
			->appendTo($container);

		foreach (['Kategorie' => 'category', 'Jahr' => 'year', 'Farbe' => 'color', 'Rahmengrösse' => 'frameSize', 'Radgrösse' => 'wheelSize', 'Gewicht' => 'weight', 'Grösse' => 'height', 'Bremsen' => 'brakes', 'Schaltung' => 'shifting', 'Motor' => 'engine', 'Batterie' => 'battery'] as $title => $property) {
			$value = $vehicle->$property;

			if (!$value || $value == '-' || $value == '0 kg')
				continue;

			HtmlNode::dt()
				->setText($title.':')
				->appendTo($dl);

			HtmlNode::dd()
				->setText($value)
				->appendTo($dl);
		}

		foreach ($vehicle->getDescriptionProperties() as $title => $value) {
			HtmlNode::dt()
				->setText($title.':')
				->appendTo($dl);

			HtmlNode::dd()
				->setText($value)
				->appendTo($dl);
		}

		// Beschreibung
		$description = $vehicle->getDescription();
		if ($description) {
			$container = HtmlNode::div()
				->addClass('description-container')
				->addClass('vehicle-description')
				->appendTo($infos);

			HtmlNode::h3()
				->setText('Beschreibung')
				->appendTo($container);

			HtmlNode::p()
				->setText($vehicle->getDescription())
				->appendTo($container);
		}

		echo $element;
	}

	/**
	 * @param $name Html Name
	 * @param $title Beschreibung
	 * @param $options Einzelne Optionen [1 => 'option']
	 * @param $default Vorselektierter Wert
	 * @param $show Filter anzeigen
	 * @return HtmlNode|null
	 */
	private function generateFilter(string $name, string $title, array $options, $default = null, $show = true): ?HtmlNode {
		$select = HtmlNode::select()->attr('name', strtolower($name));

		foreach ($options as $index => $option) {
			$option = HtmlNode::option($option)
				->attr('value', $index)
				->appendTo($select);

			if ($default && $default == $index)
				$option->attr('selected', 'selected');
		}

		$label = HtmlNode::label()
			->append(HtmlNode::span($title)->addClass('title'))
			->append($select);

		if ($show)
			return $label;

		return null;
	}
}

class VehicleSet {
	/**
	 * @var \HfCore\System
	 */
	private $system;

	/**
	 * @var \Cycly\Vehicle[]
	 */
	private $vehicles = [];

	/**
	 * @var VehicleFilter[]
	 */
	public $filter = [];

	public function __construct(?int $branch = null) {
		$this->system = \HfCore\System::getInstance();

		if ($branch)
			$this->vehicles = $this->system->getVehicles($branch);
		else {
			foreach ($this->system->getBranches() as $branch)
				$this->vehicles = array_merge($this->system->getVehicles($branch->id), $this->vehicles);
		}
	}

	/**
	 * Alle Fahrzeuge (gefiltert)
	 * @return \Cycly\Vehicle[]
	 */
	public function getAll(): array {
		$items = [];

		foreach ($this->vehicles as $vehicle) {
			$add = true;

			//foreach ($this->filter as $filter) {
				//if (!$this->filter[1]->compare($vehicle))
				//	$add = false;
			//}

			if ($add)
				$items[] = $vehicle;
		}

		return $items;
	}

	public function addFilter(VehicleFilter $filter) {
		$this->filter[] = $filter;
	}
}

class VehicleFilter {

	private $options = [];
	private $title = '';
	private $propertyName = '';
	private $vehicleSet;

	/**
	 * @param string $propertyName Fahrzeugeigenschaft welche gefiltert werden soll
	 * @param $options ['scott' => 'Scott']
	 * @param string $title
	 */
	public function __construct(VehicleSet $vehicleSet, string $propertyName, array $options, string $title) {
		$this->title = $title;
		$this->options = $options;
		$this->propertyName = $propertyName;
		$this->vehicleSet = $vehicleSet;

		// Options konvertieren falls es sich um Objekte handelt
		if (!is_string(current($this->options))) {

			$newOptions = [];

			foreach ($this->options as $option)
				$newOptions[strtolower(str_replace([' ', '-'], '', $option->title))] = $option->title;

			$this->options = $newOptions;
		}


		$this->vehicleSet->addFilter($this);
	}

	/**
	 * Nur überschneidende Optionen weiterverwenden
	 * @param $options ['scott','santacruz']
	 */
	public function reduceOptions(array $options) {
		if (empty($options))
			return;

		$newOptions = [];

		foreach ($this->options as $key => $value) {
			foreach ($options as $acceptedKey) {
				if ($key == $acceptedKey)
					$newOptions[$key] = $value;
			}
		}

		$this->options = $newOptions;
	}

	public function compare(\Cycly\Vehicle $vehicle): bool {
		if (array_key_exists($vehicle->{$this->propertyName}, $this->options))
			return true;

		return false;
	}

	public function getSelect(): ?HtmlNode {
		$select = HtmlNode::select()->attr('name', strtolower($this->propertyName));

		foreach ($this as $index => $option) {
			$option = HtmlNode::option($option)
				->attr('value', $index)
				->appendTo($select);

			if (array_key_first($this->options))
				$option->attr('selected', 'selected');
		}

		$label = HtmlNode::label()
			->append(HtmlNode::span($this->title)->addClass('title'))
			->append($select);

		if (count($this->options > 1))
			return $label;

		return null;
	}
}