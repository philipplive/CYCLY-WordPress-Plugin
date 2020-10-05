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
}