<?php

use \HfCore\HtmlNode;

class CyclyFrontend extends CyclySystem {

	public function __construct() {
		parent::__construct();

		$this->getTemplateController()->addCssFile('tpl/cycly.less')->addJsFile('tpl/cycly.js');

		// Wordpress Tags erfassen
		add_shortcode('show_bikes', [$this, 'drawBikes']);

		// Api Endpunkte
		HfCore\System::getInstance()->getApi()->addEndpoint('bike/(?P<id>\d+)', [$this, 'apiGetBike']);
	}

	public function drawBikes($atts): string {
		$body = HtmlNode::div()->id('cycly-bikes')->data('branch', isset($atts['branch']) ? $atts['branch'] : 1);

		// Filter
		$fitlers = HtmlNode::div()->appendTo($body)->addClass('filters');

		$cagetorieOptions = [0 => 'Alle'];
		foreach ($this->getVehicleCategories() as $category) {
			if ($category->count)
				$cagetorieOptions[$category->id] = $category->title;
		}

		$fitlers->append($this->generateFilter('categorie', 'Kategorie', $cagetorieOptions));

		// Vehicles
		$vehicles = $this->getVehicles(isset($atts['branch']) ? $atts['branch'] : 1);
		$items = HtmlNode::div()->addClass('items')->appendTo($body);

		foreach ($vehicles as $vehicle) {
			$bike = HtmlNode::div()->addClass('item')->data('categoryid', $vehicle->categoryId)->style('display: block;');

			$img = HtmlNode::img();
			$bike->append(HtmlNode::a($img)->addClass('image')->data('id', $vehicle->id));

			if (count($vehicle->images))
				$img->attr('src', $vehicle->images[0]->getResizedImageLink(600, 600))->attr('title', $vehicle->images[0]->title);
			else
				$img->attr('src', $this->getPluginUrl().'/tpl/vehicle-empty.png')->attr('title', 'Kein Bild vorhanden');

			$bike->append(HtmlNode::div(HtmlNode::strong($vehicle->manufacturer))->append($vehicle->model)->addClass('title'));
			$bike->append(HtmlNode::div(\HfCore\Price::format($vehicle->price))->addClass('price'));

			$items->append($bike);
		}

		// Dialog
		$close = HtmlNode::a(HtmlNode::span(), HtmlNode::span())->addClass('button-close');
		HtmlNode::div()
			->append(HtmlNode::div($close, HtmlNode::div()->addClass('dialog-container'))->addClass('dialog'))
			->addClass('dialog-wrapper')
			->appendTo($body);

		return $body;
	}

	function apiGetBike($data): void {
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
		//$imageMain->addClass('noimage');

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

	private function getVehicleById($id): ?\Cycly\Vehicle {
		$item = new \Cycly\Vehicle();
		$item->fromData(\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', $id]));

		return $item;
	}

	/**
	 * Fahrzeuge einer Geschäftsstelle
	 * @param int $branchId
	 * @return Vehicle[]
	 */
	private function getVehicles($branchId = 1): array {
		$vehicles = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', 'branch', $branchId]) as $data) {
			$item = new \Cycly\Vehicle();
			$item->fromData($data);
			$vehicles[$item->id] = $item;
		}

		return $vehicles;
	}

	public function getVehicleCategoryById(int $id): \Cycly\VehicleCategory {
		$categories = $this->getVehicleCategories();

		if (!isset($categories[$id]))
			throw new \Exception('Kategorie nicht gefuden', 404);

		return $categories[$id];
	}

	/**
	 * @return \Cycly\VehicleCategory[]
	 */
	private function getVehicleCategories(): array {
		$this->vehiclescategories = [];

		foreach (\Cycly\CyclyApi::cacheRequest(['extension', 'vehicles', 'categories']) as $data) {
			$item = new \Cycly\VehicleCategory();
			$item->fromData($data);
			$vehiclescategories[$item->id] = $item;
		}

		foreach ($this->getVehicles() as $vehicle)
			$vehiclescategories[$vehicle->categoryId]->count++;

		return $vehiclescategories;
	}

	private function generateFilter($name, $title, $options) {
		$select = HtmlNode::select()->attr('name', $name);

		foreach ($options as $index => $option)
			$select->append(HtmlNode::option($option)->attr('value', $index));

		return HtmlNode::label(
			HtmlNode::span($title)->addClass('title'),
			$select
		);
	}
}
