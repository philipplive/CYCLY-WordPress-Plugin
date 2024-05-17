<?php
namespace Cycly;

/**
 * Fahrzeug Parameter
 * Class VehicleProperty
 * @package Cycly
 */
class VehicleParameter {
	/**
	 * @var int
	 */
	public $type = 0;

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $titleShort = '';

	/**
	 * @var string
	 */
	public $category = '';

	/**
	 * @var bool
	 */
	public $additional = false;

	/**
	 * @var string
	 */
	public $value = '';

	/**
	 * @var string
	 */
	public $valueFormated = '';

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		$this->type = $data->type;
		$this->title = $data->title;
		$this->titleShort = $data->titleShort;
		$this->category = $data->category;
		$this->additional = $data->additional;
		$this->value = $data->value;
		$this->valueFormated = $data->valueFormated;

		return $this;
	}
}