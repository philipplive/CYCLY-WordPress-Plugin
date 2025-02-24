<?php

use \HfCore\HtmlNode;

/**
 * WP-Tag für die Veloliste inkl. API
 * Trait CyclyFrontendVehicles
 */
trait CyclyFrontendVehicles {

	/**
	 * WP-Tag Veloliste
	 * @param $atts
	 * @return string
	 */
	public function tagVehicles($atts): string {
		// Parameter
		$attOnstock = Query::param($atts, 'onstock', HfCore\T_STR, '') == 'true' ? true : false;
		$attOnlyDiscount = Query::param($atts, 'discount', HfCore\T_STR, '') == 'true' ? true : false;
		$attNotOnstock = Query::param($atts, 'notonstock', HfCore\T_STR, '') == 'true' ? true : false;
		$attCategories = Query::param($atts, 'categories', HfCore\T_STR, '');
		$attManufacturers = Query::param($atts, 'manufacturers', HfCore\T_STR, '');
		$attTypes = Query::param($atts, 'types', HfCore\T_STR, '');
		$attSort = Query::param($atts, 'sort', HfCore\T_INT, 2);
		$attSortable = Query::param($atts, 'sortable', HfCore\T_STR, '') == 'false' ? false : true;
		$attLimit = Query::param($atts, 'limit', HfCore\T_INT, 10);
		$branch = Query::param($atts, 'branch', HfCore\T_INT, 1);

		// Verfügbare Fahrzeuge
		$vehicles = new VehicleSet($branch);

		// An Lager
		if ($attOnstock)
			new VehicleFilter($vehicles, 'stock', [1 => 'An Lager'], 'An Lager');

		// Nicht an Lager
		if ($attNotOnstock)
			new VehicleFilter($vehicles, 'stock', [0 => 'Nicht an Lager'], 'Nicht an Lager');

		// An Lager
		if ($attOnlyDiscount)
			new VehicleFilter($vehicles, 'isDiscount', [1 => 'Sonderangebot'], 'Sonderangebot');

		// Kategorien
		$categories = [];

		if (!empty($attCategories)) {
			$items = explode(',', str_replace(' ', '', $attCategories));

			foreach ($this->getVehicleCategories() as $category) {
				if (in_array(strtolower(str_replace('-', '', $category->title)), $items))
					$categories[$category->id] = $category->title;
			}
		}
		else {
			foreach ($this->getVehicleCategories() as $category)
				$categories[$category->id] = $category->title;
		}

		new VehicleFilter($vehicles, 'categoryId', $categories, 'Kategorie:');

		// Hersteller
		$manufacturers = $this->getManufactures($branch);
		asort($manufacturers);
		$mFilter = new VehicleFilter($vehicles, 'manufacturerKey', $manufacturers, 'Hersteller:');

		if (!empty($attManufacturers))
			$mFilter->reduceOptions(explode(',', str_replace(' ', '', $attManufacturers)));

		// Typen
		$types = [];

		if (!empty($attTypes)) {
			$items = explode(',', str_replace(' ', '', $attTypes));

			foreach ($this->getVehicleTypes() as $type) {
				if (in_array(strtolower(str_replace(['-', '/'], '', $type->title)), $items))
					$types[$type->id] = $type->title;
			}
		}
		else {
			foreach ($this->getVehicleTypes() as $type)
				$types[$type->id] = $type->title;
		}

		new VehicleFilter($vehicles, 'typeId', $types, 'Typ:');

		// Body
		$body = HtmlNode::div()->addClass('cycly-vehicles')->data('limit', $attLimit);

		// Vehicles
		$items = HtmlNode::div()->addClass('items')->hide()->appendTo($body);

		foreach ($vehicles->getAll() as $vehicle) {
			$bike = HtmlNode::a()
				->addClass('item')
				->data('id', $vehicle->id)
				->data('categoryid', $vehicle->categoryId)
				->data('typeId', $vehicle->typeId)
				->data('manufacturerkey', $vehicle->manufacturerKey)
				->data('year', $vehicle->year)
				->data('price', (int)$vehicle->price);

			if ($vehicle->discountPrice)
				$bike->data('discountprice', (int)$vehicle->discountPrice);

			$img = HtmlNode::div()->appendTo($bike)->addClass('item-image');

			if (count($vehicle->images))
				try {
					$img->setBackgroundImage($vehicle->images[0]->getResizedImageLink(500, 500));
				} catch (\Exception $ex) {
					$img->setBackgroundImage($this->getPluginUrl().'/tpl/vehicle-empty.png');
				}
			else
				$img->setBackgroundImage($this->getPluginUrl().'/tpl/vehicle-empty.png');

			$bike->append(
				HtmlNode::div()
					->append(HtmlNode::strong($vehicle->manufacturer))
					->append($vehicle->model)
					->addClass('title')
			);
			if ($vehicle->price) {
				$bike->append(HtmlNode::div()
					->append(\HfCore\Price::format($vehicle->discountPrice ?: $vehicle->price))
					->addClass('price')
				);
			}

			if ($vehicle->discountPrice) {
				HtmlNode::div()
					->addClass('price-normal')
					->setText('statt ')
					->append(HtmlNode::span()->setText(\HfCore\Price::format($vehicle->price)))
					->appendTo($bike);

				// Aktionssticker
				$discount = 100 / $vehicle->price * ($vehicle->price - $vehicle->discountPrice);
				HtmlNode::div()
					->addClass('item-discount')
					->append(HtmlNode::span()->setText('-'.round($discount).'%'))
					->appendTo($bike);
			}

			$items->append($bike);
		}

		// Mehr-Button
		HtmlNode::div('Mehr anzeigen')->addClass('moreButton')->appendTo($body);

		// Filter
		$filterBody = HtmlNode::div()->prependTo($body)->addClass('filters');

		foreach ($vehicles->getFilters() as $filter)
			$filterBody->append($filter->getSelect());

		// Sortierfunktion
		$select = HtmlNode::select()->attr('name', 'sort');

		foreach ([2 => 'Preis', 1 => 'Preis absteigend', 4 => 'Jahrgang', 3 => 'Jahrgang absteigend'] as $index => $option) {
			$option = HtmlNode::option($option)
				->attr('value', $index)
				->appendTo($select);

			if ($attSort == $index)
				$option->attr('selected', 'selected');
		}

		$label = HtmlNode::label()
			->append(HtmlNode::span('Sortieren:')->addClass('title'))
			->append($select)
			->addClass('filter')
			->setStyle('display', $attSortable ? null : 'none');

		$filterBody->append(
			$label
		);

		// Dialogbox
		$close = HtmlNode::a(HtmlNode::span(), HtmlNode::span())->addClass('button-close');
		HtmlNode::div()
			->append(HtmlNode::div($close, HtmlNode::div()->addClass('dialog-container'))->addClass('dialog'))
			->addClass('dialog-wrapper')
			->appendTo($body);

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
			->appendTo($infos);

		// Preis
		$price = $vehicle->price;
		$discountPrice = $vehicle->discountPrice;

		$dl = HtmlNode::dl()
			->appendTo($container);

		HtmlNode::dt()
			->setText($discountPrice ? 'Aktionspreis:' : 'Preis:')
			->appendTo($dl);

		HtmlNode::dd()
			->addClass('price')
			->setText($discountPrice ? \HfCore\Price::format($discountPrice) : \HfCore\Price::format($price))
			->appendTo($dl);

		if ($discountPrice) {
			HtmlNode::dt()
				->setText('Vorheriger Preis')
				->appendTo($dl);

			HtmlNode::dd()
				->addClass('old-price')
				->setText(\HfCore\Price::format($price))
				->appendTo($dl);
		}

		// Weitere Details
		HtmlNode::dt()
			->setText('Garantie:')
			->appendTo($dl);

		HtmlNode::dd()
			->setText('Ja')
			//->setText($vehicle->warranty)
			->appendTo($dl);

		HtmlNode::dt()
			->setText('Verfügbar:')
			->appendTo($dl);

		HtmlNode::dd()
			->setText($vehicle->stock ? 'Ja' : ($vehicle->deliveryDate ? 'Liefertermin: '.$vehicle->deliveryDate->format('Y-m-d H:i:s') : 'Nein'))
			->appendTo($dl);

		HtmlNode::dt()
			->setText('Kategorie:')
			->appendTo($dl);

		HtmlNode::dd()
			->setText($vehicle->category)
			->appendTo($dl);

		// Details
		foreach ($vehicle->getParameterCategories() as $category){
			$parameters = $vehicle->getParametersByCategory($category);

			if(!count($parameters))
				continue;

			HtmlNode::h3()
				->setText($category)
				->appendTo($container);

			$dl = HtmlNode::dl()
				->appendTo($container);

			foreach ($parameters as $parameter){
				HtmlNode::dt()
					->setText($parameter->titleShort.':')
					->appendTo($dl);

				HtmlNode::dd()
					->setText($parameter->valueFormated)
					->appendTo($dl);
			}
		}

		echo $element;
	}
}

/**
 * Fahrzeugcollection
 * Class VehicleSet
 */
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
	public $filters = [];

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
	 * @param bool $filtered
	 * @return \Cycly\Vehicle[]
	 */
	public function getAll(bool $filtered = true): array {
		// Ungefiltert
		if (!$filtered)
			return $this->vehicles;

		// Gefiltert
		$items = [];

		foreach ($this->vehicles as $vehicle) {
			$add = true;

			foreach ($this->filters as $filter) {
				if (!$filter->compare($vehicle))
					$add = false;
			}

			if ($add) {
				$items[] = $vehicle;
			}
		}

		return $items;
	}

	public function addFilter(VehicleFilter $filter) {
		$this->filters[] = $filter;
	}

	public function getFilters(): array {
		return $this->filters;
	}
}

/**
 * Fahrzeugfilter
 * Class VehicleFilter
 */
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

		// Options konvertieren falls es sich um Objekte handelt (id und title muss gesetzt sein)
		// TODO Implementierung via Interface
		if (!is_string(current($this->options))) {
			$newOptions = [];

			foreach ($this->options as $option) {
				$newOptions[$option->id] = $option->title;
			}

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

	/**
	 * Option entfernen
	 * @param $option
	 */
	public function removeOption($option) {
		unset($this->options[$option]);
	}

	/**
	 * Prüfe Fahrzeug aufgrund des Filters
	 * @param \Cycly\Vehicle $vehicle
	 * @return bool
	 */
	public function compare(\Cycly\Vehicle $vehicle): bool {
		$value = $vehicle->{$this->propertyName};

		// Boolwerte in Integer umwandeln
		if (is_bool($value))
			$value = $value ? 1 : 0;

		if (array_key_exists($value, $this->options))
			return true;

		return false;
	}

	/**
	 * Prüfe ob das Fahrzeug diese Option enthält
	 * @param \Cycly\Vehicle $vehicle
	 * @param $option
	 * @return bool
	 */
	public function check(\Cycly\Vehicle $vehicle, $option): bool {
		return $vehicle->{$this->propertyName} == $option;
	}

	/**
	 * Anzahl verfügbarer Optionen auf die tatsächlich benötigten reduzieren
	 */
	private function autoReduce() {
		foreach ($this->options as $key => $option) {
			$use = false;

			foreach ($this->vehicleSet->getAll() as $vehicle) {
				if ($this->check($vehicle, $key)) {
					$use = true;
					break;
				}
			}

			if (!$use)
				$this->removeOption($key);
		}
	}

	/*
	 * Selectbox zeichnen
	 * @return HtmlNode|null
	 */
	public function getSelect(): ?HtmlNode {
		// Nicht verwendete Eigenschaften entfernen
		$this->autoReduce();

		// Falls nur eine Option verfügbar, ausblenden
		if (count($this->options) < 2)
			return null;

		$select = HtmlNode::select()->attr('name', strtolower($this->propertyName));
		$first = true;

		if (count($this->options) > 1) {
			$select->append(HtmlNode::option('Alle')->attr('value', 0)->attr('selected', 'selected'));
			$first = false;
		}

		foreach ($this->options as $index => $option) {
			$option = HtmlNode::option($option)
				->attr('value', $index)
				->appendTo($select);

			if ($first) {
				$option->attr('selected', 'selected');
				$first = false;
			}
		}

		$label = HtmlNode::label()
			->append(HtmlNode::span($this->title)->addClass('title'))
			->addClass('filter select-filter')
			->append($select);

		return $label;
	}
}