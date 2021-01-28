<?php
namespace Cycly;

/**
 * Fahrzeugtyp
 * Class VehicleType
 * @package Cycly
 */
class VehicleType extends Item {
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
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->title = $data->title;
		return $this;
	}
}