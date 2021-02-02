<?php
namespace HfCore;

/**
 * BaseObject
 */
abstract class BaseObject {

	/**
	 * Instanz der System Klasse
	 * @var System
	 */
	protected $system;

	public function __construct() {
		$this->system = System::getInstance();
	}

}
