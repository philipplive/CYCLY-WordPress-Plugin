<?php
namespace Cycly;

/**
 * Geschäftsstelle
 * Class Branch
 * @package Cycly
 */
class Branch extends Item {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $company = '';

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->name = $data->name;
		$this->company = $data->company;

		return $this;
	}

}