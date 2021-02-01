<?php
namespace Cycly;

/**
 * Fahrzeugkategorie
 * Class VehicleCategory
 * @package Cycly
 */
class VehicleCategory extends Item{
	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * Bezeichnung
	 * @var string
	 */
	public $title = '';

	/**
	 * Motor vorhanden
	 * @var bool
	 */
	public $hasEngine = false;

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->title = $data->title;
		$this->hasEngine = $data->hasEngine;
		return $this;
	}
}