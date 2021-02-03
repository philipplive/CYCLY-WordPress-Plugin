<?php
namespace Cycly;

/**
 * Mitarbeiter
 * Class Employee
 * @package Cycly
 */
class Employee extends Item {
	/**
	 * Anrede (herr, frau)
	 * @var string
	 */
	public $title = '';

	/**
	 * Vorname
	 * @var string
	 */
	public $firstname = '';

	/**
	 * Nachname
	 * @var string
	 */
	public $lastname = '';

	/**
	 * Bild
	 * @var Image
	 */
	public $image = null;

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->title = $data->title;
		$this->firstname = $data->firstname;
		$this->lastname = $data->lastname;

		if ($data->image) {
			$this->image = new \Cycly\Image();
			$this->image->id = $data->id;
			$this->image->url = $data->image;
			$this->image->md5 = md5($data->firstname.$data->lastname);
			$this->image->title = $data->firstname.' '.$data->lastname;
		}

		return $this;
	}

}