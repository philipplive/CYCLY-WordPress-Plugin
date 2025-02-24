<?php
namespace Cycly;

/**
 * API Item
 * Class Item
 * @package Cycly
 */
abstract class Item {
	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		return $this;
	}

	/**
	 * @param \stdClass $data
	 * @param string $name Name im Datensatz
	 * @param string|null $property Propertyname in der Klasse
	 * @param mixed $defaultValue
	 * @return self
	 */
	protected function fetchInProperty(\stdClass $data, string $name, $property = null, $defaultValue = ''): self {
		if (!$property)
			$property = $name;

		$this->{$property} = $this->fetchIn($data, $name, $defaultValue);

		return $this;
	}

	protected function fetchIn(\stdClass $data, string $name, $defaultValue = '') {
		return property_exists($data, $name) ? $data->{$name} : $defaultValue;
	}
}