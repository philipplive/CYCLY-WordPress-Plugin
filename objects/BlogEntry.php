<?php
namespace Cycly;

/**
 * Blogeintrag
 * Class BlogEntry
 * @package Cycly
 */
class BlogEntry extends Item{
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
	 * Introtext
	 * @var string
	 */
	public $intro = '';

	/**
	 * Content
	 * @var string
	 */
	public $content = '';

	/**
	 * HTML
	 * @var string
	 */
	public $html = '';

	/**
	 * Erfasst am
	 * @var \DateTime
	 */
	public $time;

	/**
	 * Geämdert am
	 * @var \DateTime
	 */
	public $changed;

	/**
	 * Publiziert am
	 * @var \DateTime
	 */
	public $published;

	/**
	 * @param \stdClass $data
	 * @return $this
	 */
	public function fromData(\stdClass $data) {
		parent::fromData($data);
		$this->id = $data->id;
		$this->title = $data->title;
		$this->intro = $data->intro;
		$this->content = $data->content;
		$this->html = $data->html;
		$this->time = new \DateInterval($data->delivery);
		$this->changed = new \DateInterval($data->changed);
		$this->published = new \DateInterval($data->published);

		return $this;
	}
}